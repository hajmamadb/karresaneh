/**
 * پلاگین resaname-kb — هسته دانش علمی دانشنامه‌ی رسانامه
 *
 * دو قابلیت (فقط از APIهای رسمی V2: command.transform + session.hook):
 *  1) دستور /kb — عبور هوشمند آرگومان‌ها به ابزارهای MCP resaname-kb
 *     /kb                وضعیت + منوی ده‌گانه
 *     /kb <متن>          جست‌وجو
 *     /kb show <code>    پرونده‌ی کامل موجودیت
 *     /kb neighbors <code> [relType]
 *     /kb list [TYPE]    فهرست
 *     /kb menu [بخش]     منوی ده‌گانه
 *     /kb validate       یکپارچگی
 *     /kb registries     ثبت‌نام‌سازها
 *  2) هوک prompt — اگر درخواست کاربر به هسته دانش مربوط باشد،
 *     یک خط یادآوری به ابتدای پیام اضافه می‌کند (تا ابزارهای MCP استفاده شوند).
 */
import { Plugin } from "@opencode/plugin"

// مارکر داخلی برای جلوگیری از تکرارِ یادآوری روی پیام‌های خودِ پلاگین
const MARK = "[KB]"

// کلمات کلیدی‌ای که نشان می‌دهد درخواست به هسته دانش علمی مربوط است
const KB_RE = /هسته[‌\s-]?دانش|دانشنامه|موجودیت|رابطه‌ی? دانش|پارادایم|روش‌?شناسی|نمونه‌?گیری|طبقه‌?بندی|kb|knowledge base/i

function kbInstruction(args: string): string {
  const a = args.trim()
  const base = "درخواست کاربر مربوط به «هسته دانش علمی» دانشنامه‌ی رسانامه است. از ابزارهای MCP سرور resaname-kb (در Code Mode: tools[\"resaname-kb\"].kb_*) استفاده کن؛ گزارش نهایی را فارسی و در جدول بده."

  if (!a) {
    return `${base}\n\nکارها:\n1. kb_status → آمار کلی (موجودیت به تفکیک نوع و نظام، اطمینان روابط، بدون‌چکیده‌ها)\n2. kb_menu → منوی ده‌گانه‌ی دانشنامه\nهر دو را اجرا کن و نتیجه را خلاصه و خوانا ارائه بده.`
  }

  const [verb, ...rest] = a.split(/\s+/)
  const arg = rest.join(" ").trim()

  switch (verb) {
    case "search":
    case "جستجو":
      return `${base}\n\nکار: kb_search(query: "${arg}") — نتایج امتیازبندی‌شده را با کد، عنوان و نمره فهرست کن. برای بهترین نتایج kb_get(code) بزن و پرونده را خلاصه کن.`
    case "show":
    case "پرونده":
      return `${base}\n\nکار: kb_get(code: "${arg}") — پرونده کامل (خود، والد، فرزندان، روابط، فاکت‌ها، پیوندها) را فارسی و ساختارمند ارائه بده.`
    case "neighbors":
    case "graph": {
      const [code, rel] = arg.split(/\s+/)
      return `${base}\n\nکار: kb_neighbors(code: "${code}"${rel ? `, relType: "${rel}"` : ""}) — همسایه‌های شبکه را به‌صورت فهرست «نام ← نوع رابطه» نشان بده.`
    }
    case "list":
    case "فهرست":
      return `${base}\n\nکار: kb_list${arg ? `(type: "${arg}")` : ""} — موجودیت‌ها را به تفکیک نوع و با کدهایشان فهرست کن.`
    case "menu":
    case "منو":
      return `${base}\n\nکار: kb_menu${arg ? `(section: "${arg}")` : ""} — منوی ده‌گانه‌ی ناوبری را دقیقاً با ساختار بخش‌ها ارائه بده.`
    case "validate":
    case "یکپارچگی":
      return `${base}\n\nکار: kb_validate — گزارش یکپارچگی (دو سر ناموجود، نوع نامعتبر، والد ناموجود، عنوان تکراری). اگر خطا بود، فهرست کن و راه‌حل بده؛ اگر پاک بود صریح بگو «بدون خطا».`
    case "registries":
    case "ثبت‌نام‌سازها":
      return `${base}\n\nکار: kb_registries — سه ثبت‌نام‌ساز رسمی (۲۸ نوع موجودیت، ۲۰ نوع رابطه، ۹ بُعد فاکت) را به جدول کامل درآور.`
    case "status":
    case "وضعیت":
      return `${base}\n\nکار: kb_status — آمار کامل هسته دانش را در جدول ارائه بده.`
    default:
      // هر متن دیگری → جست‌وجو
      return `${base}\n\nکار: kb_search(query: "${a}") — بهترین نتایج را با kb_get تکمیل کن و پاسخ را مستند و فارسی بنویس.`
  }
}

export default Plugin.define({
  id: "resaname-kb",
  async setup(ctx) {
    // 1) دستور /kb
    await ctx.command.transform((editor) => {
      editor.add({
        name: "kb",
        description:
          "هسته دانش علمی — وضعیت، جست‌وجو، پرونده موجودیت، شبکه، منو، یکپارچگی (مثال: /kb search برجسته‌سازی، /kb show theory-agenda-setting، /kb validate)",
        execute: async ({ sessionID, prompt, delivery }) => {
          const text = kbInstruction(prompt.text ?? "")
          await ctx.session.prompt({ ...prompt, sessionID, text, delivery })
        },
      })
    })

    // 2) هوک prompt: یادآوری خودکار برای درخواست‌های مرتبط با هسته دانش
    await ctx.session.hook("prompt", (event) => {
      const text = event.prompt.text ?? ""
      if (!text.startsWith(MARK) && KB_RE.test(text)) {
        event.prompt.text = `${MARK} این درخواست احتمالاً به «هسته دانش علمی» دانشنامه مربوط است؛ از ابزارهای MCP سرور resaname-kb (kb_status, kb_search, kb_get, kb_neighbors, kb_validate) استفاده کن و پاسخ را فارسی و مستند بنویس.\n\n${text}`
      }
    })
  },
})
