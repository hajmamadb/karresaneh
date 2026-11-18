#!/usr/bin/env node
/**
 * Resaname KB — سرور MCP برای هسته دانش علمی
 *
 * ابزارها (کلّی برای خواندن/ناوبری، نوشتن با ممیزی و ردپا در kb_audit_log):
 *   kb_status, kb_search, kb_get, kb_neighbors, kb_list, kb_menu, kb_registries,
 *   kb_validate, kb_add_entity, kb_add_relation, kb_add_facet, kb_link_main
 *
 * دیتابیس‌ها مستقیماً از .env پروژه خوانده می‌شوند (EDITORIAL_DB_* / MAIN_DB_*).
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import mysql from 'mysql2/promise';
import { z } from 'zod';
import { McpServer } from '@modelcontextprotocol/sdk/server/mcp.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..', '..');

// ---------------------------------------------------------------- .env
function loadEnv(file) {
  const out = {};
  try {
    for (const line of fs.readFileSync(file, 'utf8').split(/\r?\n/)) {
      const m = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/);
      if (m) out[m[1]] = m[2];
    }
  } catch { /* .env ندارد */ }
  return out;
}
const env = { ...loadEnv(path.join(ROOT, '.env')), ...process.env };

function makeConn(prefix) {
  const name = env[`${prefix}_NAME`], user = env[`${prefix}_USER`];
  if (!name || !user) throw new Error(`Database configuration incomplete: ${prefix}`);
  return mysql.createConnection({
    host: env[`${prefix}_HOST`] || '127.0.0.1',
    port: Number(env[`${prefix}_PORT`] || 3306),
    database: name,
    user,
    password: env[`${prefix}_PASS`] || '',
    charset: 'utf8mb4',
    multipleStatements: false,
  });
}
let edPool = null, mainPool = null;
async function ed() { if (!edPool) edPool = makeConn('EDITORIAL_DB'); return edPool; }
async function main() { if (!mainPool) mainPool = makeConn('MAIN_DB'); return mainPool; }

// ---------------------------------------------------------------- helpers
const ok = (obj) => ({ content: [{ type: 'text', text: typeof obj === 'string' ? obj : JSON.stringify(obj, null, 2) }] });
const err = (msg) => ({ content: [{ type: 'text', text: `خطا: ${msg}` }], isError: true });

function slugify(s) {
  return String(s || '').toLowerCase().replace(/[^a-z0-9\-]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
}
async function audit(c, code, action, detail, source) {
  try {
    await c.execute('INSERT INTO kb_audit_log (entity_code, action, detail, source) VALUES (?,?,?,?)',
      [code, action, JSON.stringify(detail ?? {}, { space: 0 }), source]);
  } catch { /* جدول ممیزی موجود نیست */ }
}
const esc = (s) => String(s).replace(/\\/g, '\\\\').replace(/%/g, '\\%').replace(/_/g, '\\_');

// ---------------------------------------------------------------- registries (از جدول‌های seed شده)
async function registries() {
  const c = await ed();
  const [et] = await c.query('SELECT code,label_fa,label_en,domain,description FROM kb_entity_types ORDER BY sort_order');
  const [rt] = await c.query('SELECT code,label_fa,label_en,description,symmetric FROM kb_relation_types ORDER BY sort_order');
  const [ft] = await c.query('SELECT code,label_fa,label_en,description FROM kb_facet_dimensions ORDER BY sort_order');
  return { entity_types: et, relation_types: rt, facet_dimensions: ft };
}
async function typeExists(c, table, code) {
  const [r] = await c.execute(`SELECT 1 FROM \`${table}\` WHERE code=?`, [code]);
  return r.length > 0;
}
async function entityExists(c, code) {
  const [r] = await c.execute('SELECT 1 FROM kb_entities WHERE code=?', [code]);
  return r.length > 0;
}

// ---------------------------------------------------------------- server
const server = new McpServer({ name: 'resaname-kb', version: '1.0.0' });

// ---------------------------------------------------------------- read tools
server.tool('kb_status', 'وضعیت کلی هسته دانش علمی: آمار موجودیت‌ها (به تفکیک نوع و نظام)، روابط، فاکت‌ها، پیوندها و موجودیت‌های بدون چکیده.', {}, async () => {
  try {
    const c = await ed();
    const [entities] = await c.execute("SELECT COUNT(*) n FROM kb_entities WHERE status='active'");
    const [byType] = await c.query(`SELECT e.entity_type, et.label_fa, COUNT(*) c FROM kb_entities e
      JOIN kb_entity_types et ON et.code=e.entity_type WHERE e.status='active'
      GROUP BY e.entity_type, et.label_fa ORDER BY et.sort_order`);
    const [byDomain] = await c.query("SELECT domain, COUNT(*) c FROM kb_entities WHERE status='active' GROUP BY domain");
    const [relations] = await c.execute("SELECT COUNT(*) n FROM kb_relations WHERE status='active'");
    const [relConf] = await c.query("SELECT confidence, COUNT(*) c FROM kb_relations GROUP BY confidence");
    const [facets] = await c.execute('SELECT COUNT(*) n FROM kb_entity_facets');
    const [links] = await c.execute('SELECT COUNT(*) n FROM kb_entity_links');
    const [pending] = await c.execute("SELECT COUNT(*) n FROM kb_entities WHERE status='active' AND (abstract IS NULL OR abstract='')");
    return ok({
      entities: entities[0].n, entities_by_type: byType, entities_by_domain: byDomain,
      relations: relations[0].n, relations_by_confidence: relConf,
      facets: facets[0].n, links: links[0].n, pending_abstract: pending[0].n,
    });
  } catch (e) { return err(e.message); }
});

server.tool('kb_search', 'جست‌وجوی موجودیت‌های هسته دانش (عنوان فارسی/انگلیسی/چکیده) با امتیازبندی.', {
  query: z.string().describe('کلمه‌ی کلیدی فارسی یا انگلیسی'),
  type: z.string().optional().describe('فیلتر بر اساس نوع موجودیت، مثلاً THEORY یا METHOD'),
  limit: z.number().int().min(1).max(200).optional().describe('حداکثر نتایج (پیش‌فرض ۲۰)'),
}, async ({ query, type, limit }) => {
  try {
    const c = await ed();
    const like = `%${esc(query.trim())}%`;
    const sql = `SELECT e.code, e.title_fa, e.title_en, e.entity_type, e.domain, e.parent_code,
        et.label_fa AS type_label,
        (CASE WHEN e.title_fa LIKE ? THEN 3 WHEN e.title_en LIKE ? THEN 2 WHEN e.abstract LIKE ? THEN 1 ELSE 0 END) AS score
      FROM kb_entities e JOIN kb_entity_types et ON et.code=e.entity_type
      WHERE e.status='active' AND (e.title_fa LIKE ? OR e.title_en LIKE ? OR e.slug LIKE ? OR e.abstract LIKE ?)${type ? ' AND e.entity_type=?' : ''}
      ORDER BY score DESC, e.title_fa LIMIT ${Math.max(1, Math.min(200, limit || 20))}`;
    const params = [like, like, like, like, like, like, like, ...(type ? [String(type).toUpperCase()] : [])];
    const [rows] = await c.execute(sql, params);
    return ok(rows.length ? rows : 'نتیجه‌ای پیدا نشد.');
  } catch (e) { return err(e.message); }
});

server.tool('kb_get', 'پرونده‌ی کامل یک موجودیت: خود + والد + فرزندان + روابط + فاکت‌ها + پیوندهای درون‌سایتی.', {
  code: z.string().describe('کد یکتای موجودیت، مثلاً school-frankfurt'),
}, async ({ code }) => {
  try {
    const c = await ed();
    const [e] = await c.execute(`SELECT e.*, et.label_fa AS type_fa, et.label_en AS type_en
      FROM kb_entities e JOIN kb_entity_types et ON et.code=e.entity_type WHERE e.code=?`, [code]);
    if (!e.length) return err(`موجودیت پیدا نشد: ${code}`);
    const out = { ...e[0] };
    out.properties = out.properties ? JSON.parse(out.properties) : null;
    if (out.parent_code) {
      const [p] = await c.execute('SELECT code,title_fa,title_en,entity_type FROM kb_entities WHERE code=?', [out.parent_code]);
      out.parent = p[0] || null;
    } else out.parent = null;
    const [children] = await c.execute('SELECT code,title_fa,title_en,entity_type FROM kb_entities WHERE parent_code=? ORDER BY id', [code]);
    out.children = children;
    const [relations] = await c.execute(`SELECT r.source_code, r.target_code, r.relation_type, rt.label_fa AS rel_fa, r.note, r.weight, r.confidence,
        s.title_fa AS source_title, s.entity_type AS source_type,
        t.title_fa AS target_title, t.entity_type AS target_type
      FROM kb_relations r
      JOIN kb_relation_types rt ON rt.code=r.relation_type
      JOIN kb_entities s ON s.code=r.source_code
      JOIN kb_entities t ON t.code=r.target_code
      WHERE (r.source_code=? OR r.target_code=?) AND r.status='active' ORDER BY r.relation_type, r.id`, [code, code]);
    out.relations = relations;
    const [facets] = await c.execute(`SELECT f.facet_code, fd.label_fa AS facet_fa, f.value_fa, f.value_en
      FROM kb_entity_facets f JOIN kb_facet_dimensions fd ON fd.code=f.facet_code
      WHERE f.entity_code=? ORDER BY fd.sort_order`, [code]);
    out.facets = facets;
    const [links] = await c.execute('SELECT link_type, link_id, link_title, confidence FROM kb_entity_links WHERE entity_code=? ORDER BY link_type', [code]);
    out.links = links;
    return ok(out);
  } catch (e) { return err(e.message); }
});

server.tool('kb_neighbors', 'همسایه‌های یک موجودیت در شبکه‌ی دانش (با فیلتر نوع رابطه و جهت).', {
  code: z.string().describe('کد موجودیت مبدأ'),
  depth: z.number().int().min(1).max(3).optional().describe('عمق گشتار (۱ تا ۳، پیش‌فرض ۱)'),
  relType: z.string().optional().describe('فقط یک نوع رابطه، مثلاً PROPOSED_BY'),
  direction: z.enum(['both', 'out', 'in']).optional().describe('جهت: out (خارجی)، in (ورودی)، both'),
}, async ({ code, depth = 1, relType, direction = 'both' }) => {
  try {
    const c = await ed();
    const d = Math.max(1, Math.min(3, depth));
    const visited = { [code]: 0 };
    const out = [];
    for (let i = 1; i <= d; i++) {
      const codes = Object.keys(visited).filter((k) => visited[k] === i - 1);
      if (!codes.length) break;
      const inList = codes.map((x) => `'${String(x).replace(/[^a-zA-Z0-9\-_]/g, '')}'`).join(',');
      const where = [];
      const params = [];
      if (direction === 'out' || direction === 'both') where.push(`r.source_code IN (${inList})`);
      if (direction === 'in' || direction === 'both') where.push(`r.target_code IN (${inList})`);
      let sql = `SELECT r.source_code, r.target_code, r.relation_type, rt.label_fa,
          (CASE WHEN r.source_code IN (${inList}) THEN r.target_code ELSE r.source_code END) AS other,
          e.title_fa AS other_title, e.entity_type AS other_type, r.confidence
        FROM kb_relations r
        JOIN kb_relation_types rt ON rt.code=r.relation_type
        JOIN kb_entities e ON e.code=(CASE WHEN r.source_code IN (${inList}) THEN r.target_code ELSE r.source_code END)
        WHERE (${where.join(' OR ')}) AND r.status='active'`;
      if (relType) { sql += ' AND r.relation_type=?'; params.push(String(relType).toUpperCase()); }
      const [rows] = await c.execute(sql, params);
      for (const row of rows) {
        if (visited[row.other] === undefined) { visited[row.other] = i; out.push(row); }
      }
    }
    return ok(out.length ? out : 'همسایه‌ای پیدا نشد.');
  } catch (e) { return err(e.message); }
});

server.tool('kb_list', 'فهرست موجودیت‌ها (با فیلتر اختیاری بر اساس نوع).', {
  type: z.string().optional().describe('فیلتر نوع، مثلاً PERSON یا ANALYSIS'),
  limit: z.number().int().min(1).max(2000).optional().describe('حداکثر (پیش‌فرض ۱۰۰)'),
}, async ({ type, limit = 100 }) => {
  try {
    const c = await ed();
    const lim = Math.max(1, Math.min(2000, limit));
    const sql = `SELECT code,title_fa,title_en,entity_type,parent_code FROM kb_entities WHERE status='active'${type ? ' AND entity_type=?' : ''} ORDER BY entity_type,id LIMIT ${lim}`;
    const [rows] = await c.execute(sql, type ? [String(type).toUpperCase()] : []);
    return ok(`${rows.length} رکورد: ` + JSON.stringify(rows, null, 0));
  } catch (e) { return err(e.message); }
});

server.tool('kb_menu', 'منوی ده‌گانه‌ی دانشنامه (ساختار ناوبری بر اساس parent_code) — مناسب برای ساخت منو در فرانت.', {
  section: z.string().optional().describe('فقط یک بخش، مثلاً «تحلیل داده» (در غیر این صورت کل منو)'),
}, async ({ section }) => {
  try {
    const c = await ed();
    const [all] = await c.query("SELECT code,title_fa,title_en,entity_type,parent_code FROM kb_entities WHERE status='active' ORDER BY id");
    const byType = {};
    for (const e of all) (byType[e.entity_type] ||= []).push(e);
    const MENU = [
      ['مکاتب و سنت‌های فکری', ['SCHOOL', 'TRADITION']],
      ['نظریه‌ها و نظام‌های تبیینی', ['THEORY', 'MODEL', 'FRAMEWORK', 'HYPOTHESIS', 'LAW', 'CONCEPT']],
      ['پارادایم‌ها و رویکردهای پژوهش', ['PARADIGM', 'APPROACH']],
      ['روش‌شناسی و طرح‌های پژوهش', ['METHODOLOGY', 'METHOD', 'DESIGN', 'STRATEGY']],
      ['گردآوری داده و نمونه‌گیری', ['TECHNIQUE', 'SAMPLING', 'INSTRUMENT']],
      ['تحلیل داده', ['ANALYSIS']],
      ['کیفیت و اعتبار پژوهش', ['QUALITY']],
      ['اخلاق و استانداردهای پژوهش', ['ETHICS', 'PROTOCOL']],
      ['ابزارها و نرم‌افزارهای پژوهش', ['SOFTWARE', 'DATASET']],
      ['اندیشمندان و منابع علمی', ['PERSON', 'WORK']],
    ];
    const lines = [];
    for (const [label, tlist] of MENU) {
      if (section && !label.includes(section)) continue;
      lines.push(`■ ${label}`);
      const top = [];
      for (const t of tlist) for (const e of byType[t] || []) if (!e.parent_code) top.push(e);
      top.sort((a, b) => String(a.title_fa).localeCompare(String(b.title_fa), 'fa'));
      if (!top.length) { lines.push('   (خالی)'); continue; }
      for (const e of top) {
        const n = all.filter((x) => x.parent_code === e.code).length;
        lines.push(`   • ${e.title_fa}${e.title_en ? ' — ' + e.title_en : ''}${n ? `  (${n} زیرمجموعه)` : ''}`);
      }
    }
    return ok(lines.join('\n'));
  } catch (e) { return err(e.message); }
});

server.tool('kb_registries', 'ثبت‌نام‌سازهای رسمی: انواع موجودیت، انواع رابطه و ابعاد فاکت (تک منبع حقیقت).', {
  kind: z.enum(['all', 'entity', 'relation', 'facet']).optional().describe('پیش‌فرض all'),
}, async ({ kind = 'all' }) => {
  try {
    const r = await registries();
    if (kind === 'entity') return ok(r.entity_types);
    if (kind === 'relation') return ok(r.relation_types);
    if (kind === 'facet') return ok(r.facet_dimensions);
    return ok(r);
  } catch (e) { return err(e.message); }
});

server.tool('kb_validate', 'گزارش یکپارچگی شبکه‌ی دانش (دو سر ناموجود، نوع نامعتبر، والد ناموجود، عنوان تکراری).', {}, async () => {
  try {
    const c = await ed();
    const issues = [];
    const q = async (sql) => ((await c.execute(sql))[0][0]?.n) || 0;
    let n = await q('SELECT COUNT(*) n FROM kb_relations r WHERE NOT EXISTS (SELECT 1 FROM kb_entities e WHERE e.code=r.source_code)');
    if (n) issues.push(`رابطه‌ای با source ناموجود: ${n}`);
    n = await q('SELECT COUNT(*) n FROM kb_relations r WHERE NOT EXISTS (SELECT 1 FROM kb_entities e WHERE e.code=r.target_code)');
    if (n) issues.push(`رابطه‌ای با target ناموجود: ${n}`);
    n = await q('SELECT COUNT(*) n FROM kb_entities e WHERE e.entity_type NOT IN (SELECT code FROM kb_entity_types)');
    if (n) issues.push(`موجودیت با نوع نامعتبر: ${n}`);
    n = await q('SELECT COUNT(*) n FROM kb_entities e WHERE e.parent_code IS NOT NULL AND NOT EXISTS (SELECT 1 FROM kb_entities p WHERE p.code=e.parent_code)');
    if (n) issues.push(`والدِ ناموجود: ${n}`);
    n = await q('SELECT COUNT(*) n FROM kb_entity_facets f WHERE f.facet_code NOT IN (SELECT code FROM kb_facet_dimensions)');
    if (n) issues.push(`فاکت با بعد نامعتبر: ${n}`);
    n = await q('SELECT COUNT(*) n FROM kb_entity_links l WHERE NOT EXISTS (SELECT 1 FROM kb_entities e WHERE e.code=l.entity_code)');
    if (n) issues.push(`پیوند با موجودیت ناموجود: ${n}`);
    const [dups] = await c.query("SELECT title_fa, COUNT(*) c FROM kb_entities WHERE status='active' GROUP BY title_fa HAVING c>1");
    for (const d of dups) issues.push(`عنوان تکراری «${d.title_fa}»: ${d.c} رکورد (باید یک موجودیت + چند رابطه باشد)`);
    return ok(issues.length ? issues : 'یکپارچگی هسته دانش سالم است.');
  } catch (e) { return err(e.message); }
});

// ---------------------------------------------------------------- write tools (با ممیزی + ردپا)
server.tool('kb_add_entity', 'ساخت یا به‌روزرسانی یک موجودیت در هسته دانش (code یکتا، type معتبر، parent موجود).', {
  code: z.string().describe('کد یکتا با حروف کوچک و خط‌تیره، مثلا‌ً theory-agenda-setting'),
  title_fa: z.string().describe('عنوان فارسی (الزامی)'),
  title_en: z.string().optional().describe('عنوان انگلیسی'),
  entity_type: z.string().describe('نوع موجودیت از kb_registries، مثلا‌ً THEORY'),
  parent_code: z.string().optional().describe('کد موجودیت والد (باید وجود داشته باشد)'),
  abstract: z.string().optional().describe('چکیده/تعریف کوتاه'),
  properties: z.record(z.any()).optional().describe('مشخصات ساختاریافته (JSON)'),
}, async (a) => {
  try {
    const c = await ed();
    const code = slugify(a.code);
    if (!code || code.length > 80) return err('code نامعتبر است (1-80 کاراکتر، حروف کوچک لاتین و خط تیره)');
    const fa = String(a.title_fa || '').trim();
    if (!fa) return err('title_fa الزامی است');
    const type = String(a.entity_type || '').trim().toUpperCase();
    if (!(await typeExists(c, 'kb_entity_types', type))) return err(`entity_type نامعتبر: ${a.entity_type}`);
    let parent = null;
    if (a.parent_code) {
      if (!(await entityExists(c, String(a.parent_code).trim()))) return err(`parent_code وجود ندارد: ${a.parent_code}`);
      parent = String(a.parent_code).trim();
    }
    const props = a.properties ? JSON.stringify(a.properties) : null;
    const [ex] = await c.execute('SELECT code FROM kb_entities WHERE code=?', [code]);
    const exists = ex.length > 0;
    await c.execute(`INSERT INTO kb_entities (code,slug,title_fa,title_en,entity_type,domain,parent_code,abstract,properties,sort_order,status,created_at,updated_at)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
      ON DUPLICATE KEY UPDATE title_fa=VALUES(title_fa), title_en=VALUES(title_en), entity_type=VALUES(entity_type),
        domain=VALUES(domain), parent_code=VALUES(parent_code),
        abstract=IF(VALUES(abstract) IS NULL,abstract,VALUES(abstract)),
        properties=IF(VALUES(properties) IS NULL,properties,VALUES(properties)), updated_at=NOW()`,
      [code, code, fa, String(a.title_en || '').trim() || null, type,
       (await (async () => { const [t] = await c.execute('SELECT domain FROM kb_entity_types WHERE code=?', [type]); return t[0]?.domain || 'shared'; })()),
       parent, String(a.abstract || '').trim() || null, props, 0, 'active']);
    await audit(c, code, exists ? 'update' : 'create', { type, title: fa }, 'mcp');
    return ok(`ذخیره شد: ${code}`);
  } catch (e) { return err(e.message); }
});

server.tool('kb_add_relation', 'افزودن رابطه بین دو موجودیت (هر دو باید وجود داشته باشند؛ نوع از kb_registries).', {
  source: z.string().describe('کد موجودیت مبدأ'),
  target: z.string().describe('کد موجودیت مقصد'),
  relation_type: z.string().describe('نوع رابطه، مثلا‌ً PROPOSED_BY'),
  note: z.string().optional().describe('توضیح کوتاه'),
  weight: z.number().int().min(1).max(100).optional().describe('وزن ۱ تا ۱۰۰ (پیش‌فرض ۵۰)'),
  confidence: z.enum(['verified', 'suggested', 'ai_draft']).optional().describe('پیش‌فرض suggested'),
}, async (a) => {
  try {
    const c = await ed();
    const s = String(a.source || '').trim();
    const t = String(a.target || '').trim();
    const type = String(a.relation_type || '').trim().toUpperCase();
    if (!s || !t) return err('source و target الزامی است');
    if (!(await typeExists(c, 'kb_relation_types', type))) return err(`relation_type نامعتبر: ${a.relation_type}`);
    if (!(await entityExists(c, s))) return err(`source وجود ندارد: ${s}`);
    if (!(await entityExists(c, t))) return err(`target وجود ندارد: ${t}`);
    const conf = a.confidence || 'suggested';
    await c.execute(`INSERT INTO kb_relations (source_code,target_code,relation_type,note,weight,confidence,status,created_by,created_at,updated_at)
      VALUES (?,?,?,?,?,?, 'active', 'mcp', NOW(), NOW())
      ON DUPLICATE KEY UPDATE note=IF(VALUES(note) IS NULL,note,VALUES(note)), weight=VALUES(weight), confidence=VALUES(confidence), status='active', updated_at=NOW()`,
      [s, t, type, String(a.note || '').trim() || null, Math.max(1, Math.min(100, a.weight || 50)), conf]);
    await audit(c, s, 'relate', { target: t, type }, 'mcp');
    return ok(`رابطه ثبت شد: ${s} → [${type}] → ${t}`);
  } catch (e) { return err(e.message); }
});

server.tool('kb_add_facet', 'افزودن فاکت (ویژگی چندبُعدی) به یک موجودیت.', {
  code: z.string().describe('کد موجودیت'),
  facet: z.string().describe('بعد فاکت از kb_registries (facet)، مثلا‌ً approach'),
  value: z.string().describe('مقدار فارسی'),
  value_en: z.string().optional().describe('مقدار انگلیسی'),
}, async (a) => {
  try {
    const c = await ed();
    if (!(await entityExists(c, String(a.code).trim()))) return err(`entity وجود ندارد: ${a.code}`);
    if (!(await typeExists(c, 'kb_facet_dimensions', String(a.facet).trim()))) return err(`facet نامعتبر: ${a.facet}`);
    await c.execute(`INSERT INTO kb_entity_facets (entity_code,facet_code,value_fa,value_en,created_at)
      VALUES (?,?,?,?,NOW())
      ON DUPLICATE KEY UPDATE value_en=IF(VALUES(value_en) IS NULL,value_en,VALUES(value_en))`,
      [String(a.code).trim(), String(a.facet).trim(), String(a.value || '').trim(), a.value_en || null]);
    await audit(c, String(a.code).trim(), 'facet', { facet: a.facet, value: a.value }, 'mcp');
    return ok('فاکت ثبت شد.');
  } catch (e) { return err(e.message); }
});

server.tool('kb_link_main', 'پیوند یک موجودیت به محتوای دیتابیس اصلی (کارت/نظریه‌پرداز/منبع کتابخانه/دسته‌بندی).', {
  code: z.string().describe('کد موجودیت هسته دانش'),
  link_type: z.enum(['card', 'theorist', 'library_resource', 'category']).describe('نوع محتوای اصلی'),
  link_id: z.string().describe('شناسه‌ی رکورد اصلی، مثلا‌ً card-101'),
  title: z.string().optional().describe('عنوان نمایشی'),
  confidence: z.enum(['verified', 'suggested', 'ai_draft']).optional().describe('پیش‌فرض suggested'),
}, async (a) => {
  try {
    const c = await ed();
    if (!(await entityExists(c, String(a.code).trim()))) return err(`entity وجود ندارد: ${a.code}`);
    await c.execute(`INSERT INTO kb_entity_links (entity_code,link_type,link_id,link_title,confidence,created_by,created_at)
      VALUES (?,?,?,?,?,?,NOW())
      ON DUPLICATE KEY UPDATE link_title=IF(VALUES(link_title) IS NULL,link_title,VALUES(link_title)), confidence=VALUES(confidence), created_by=VALUES(created_by)`,
      [String(a.code).trim(), a.link_type, String(a.link_id).trim(), a.title || null, a.confidence || 'suggested', 'mcp']);
    await audit(c, String(a.code).trim(), 'link', { type: a.link_type, id: a.link_id }, 'mcp');
    return ok('پیوند ثبت شد.');
  } catch (e) { return err(e.message); }
});

server.tool('kb_match_main', 'تطبیق خودکار موجودیت‌ها با محتوای دیتابیس اصلی (فقط‌خواندنی). با apply=true پیوندها ذخیره می‌شوند.', {
  apply: z.boolean().optional().describe('اگر true، پیوندها در kb_entity_links ذخیره شوند (پیش‌فرض false)'),
  limit: z.number().int().min(1).max(100).optional().describe('حداکثر تعداد نمایش (پیش‌فرض ۳۰)'),
}, async ({ apply = false, limit = 30 }) => {
  try {
    const m = await main();
    const c = await ed();
    const pool = [
      ['card', 'cards', 'title_fa', 'title_en', " WHERE status='published'"],
      ['theorist', 'theorists', 'name', 'name_en', ''],
      ['library_resource', 'library_resources', 'title', 'title_en', ''],
    ];
    const mainRows = [];
    for (const [type, table, tcol, encol, where] of pool) {
      try {
        const [rows] = await m.query(`SELECT id, \`${tcol}\` AS title, \`${encol}\` AS title_en FROM \`${table}\`${where} LIMIT 5000`);
        for (const r of rows) mainRows.push({ type, id: String(r.id), title: String(r.title || ''), en: String(r.title_en || '') });
      } catch (e) { console.error(`kb_match_main: ${table}: ${e.message}`); }
    }
    const norm = (t) => {
      t = String(t || '').trim().replace(/[ـ‌]/g, '');
      t = t.replace(/^(نظریه‌ی |نظریه |مدل |مکتب |سنت |روش |فن |چارچوب |پارادایم )/g, '');
      t = t.toLowerCase();
      t = t.replace(/(theory |model of |the )/gi, '');
      t = t.replace(/[\s\-_/،:؛|()]+/g, ' ');
      return t.trim().toLowerCase();
    };
    const ngrams = (s, n) => { const o = {}; for (let i = 0; i + n <= [...s].length; i++) o[[...s].slice(i, i + n).join('')] = 1; return o; };
    const sim = (a, b) => { const A = ngrams(a, 3), B = ngrams(b, 3); const ca = Object.keys(A).length, cb = Object.keys(B).length; if (!ca || !cb) return 0; let common = 0; for (const k in A) if (B[k]) common++; return (2 * common) / (ca + cb); };
    const [kb] = await c.query("SELECT code, title_fa, title_en FROM kb_entities WHERE status='active'");
    const matches = [];
    for (const e of kb) {
      const et = norm(e.title_fa);
      const en = String(e.title_en || '').trim().toLowerCase();
      if (!et) continue;
      for (const mm of mainRows) {
        const mt = norm(mm.title);
        const men = mm.en.trim().toLowerCase();
        let hit = null;
        if (et && mt === et) hit = 'verified';
        else if (en && men && en === men) hit = 'verified';
        else if (et && mt) {
          const lmin = Math.min([...et].length, [...mt].length);
          const lmax = Math.max([...et].length, [...mt].length);
          if (lmin >= 8 && lmin / Math.max(1, lmax) >= 0.5 && (mt.includes(et) || et.includes(mt))) hit = 'suggested';
          else if (lmin >= 6 && sim(et, mt) >= 0.82) hit = 'suggested';
        }
        if (hit) {
          matches.push({ entity_code: e.code, entity_title: e.title_fa, link_type: mm.type, link_id: mm.id, link_title: mm.title, confidence: hit });
          if (hit === 'verified') break;
        }
      }
    }
    if (apply) {
      for (const mm of matches) {
        await c.execute(`INSERT INTO kb_entity_links (entity_code,link_type,link_id,link_title,confidence,created_by,created_at)
          VALUES (?,?,?,?,?,?,NOW())
          ON DUPLICATE KEY UPDATE link_title=IF(VALUES(link_title) IS NULL,link_title,VALUES(link_title)), confidence=VALUES(confidence), created_by=VALUES(created_by)`,
          [mm.entity_code, mm.link_type, mm.link_id, mm.link_title, mm.confidence, 'auto_match']);
      }
      await audit(c, null, 'link_main_apply', { count: matches.length }, 'mcp');
    }
    const verified = matches.filter((x) => x.confidence === 'verified').length;
    return ok({ total: matches.length, verified, suggested: matches.length - verified, applied: apply, matches: matches.slice(0, limit) });
  } catch (e) { return err(e.message); }
});

// ---------------------------------------------------------------- transport
const transport = new StdioServerTransport();
await server.connect(transport);
console.error('resaname-kb MCP server running');

