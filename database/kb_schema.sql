
-- ============================================================================
-- هسته دانش علمی رسانامه (Knowledge Core)
-- شبکه دانش دانشنامه: موجودیت‌ها + روابط + فاکت‌ها + پیوندهای درون‌سایتی
-- طراحی: docs/knowledge-core.md — تک منبع حقیقت: app/Knowledge/*
-- دو نظام مستقل: theoretical (مکاتب/نظریه‌ها) و methodological (روش‌شناسی)
-- ============================================================================

-- ۱) ثبت‌نام‌ساز: انواع موجودیت‌های دانش (SCHOOL, THEORY, METHOD, ...)
CREATE TABLE IF NOT EXISTS kb_entity_types (
  code VARCHAR(40) NOT NULL PRIMARY KEY,
  label_fa VARCHAR(200) NOT NULL,
  label_en VARCHAR(200) NOT NULL,
  domain ENUM('theoretical','methodological','shared') NOT NULL,
  description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۲) ثبت‌نام‌ساز: انواع روابط (IS_A, INFLUENCED_BY, ...)
CREATE TABLE IF NOT EXISTS kb_relation_types (
  code VARCHAR(40) NOT NULL PRIMARY KEY,
  label_fa VARCHAR(200) NOT NULL,
  label_en VARCHAR(200) NOT NULL,
  description TEXT NULL,
  symmetric TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۳) ثبت‌نام‌ساز: ابعاد طبقه‌بندی چندبُعدی (approach, data_type, ...)
CREATE TABLE IF NOT EXISTS kb_facet_dimensions (
  code VARCHAR(60) NOT NULL PRIMARY KEY,
  label_fa VARCHAR(200) NOT NULL,
  label_en VARCHAR(200) NOT NULL,
  description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۴) هسته: موجودیت‌های دانش (هر مفهوم یک رکورد، هرگز تکرار نمی‌شود)
CREATE TABLE IF NOT EXISTS kb_entities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL,
  slug VARCHAR(200) NOT NULL,
  title_fa VARCHAR(500) NOT NULL,
  title_en VARCHAR(500) NULL,
  entity_type VARCHAR(40) NOT NULL,
  domain ENUM('theoretical','methodological','shared') NOT NULL,
  parent_code VARCHAR(80) NULL,
  abstract TEXT NULL,
  description LONGTEXT NULL,
  properties JSON NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('draft','active','archived') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_code (code),
  UNIQUE KEY uq_slug (slug),
  KEY idx_type (entity_type),
  KEY idx_parent (parent_code),
  KEY idx_status (status),
  KEY idx_domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۵) شبکه دانش: روابط بین‌موجودیتی (هر رابطه یک رکورد مستقل و قابل توسعه)
CREATE TABLE IF NOT EXISTS kb_relations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_code VARCHAR(80) NOT NULL,
  target_code VARCHAR(80) NOT NULL,
  relation_type VARCHAR(40) NOT NULL,
  note VARCHAR(500) NULL,
  weight TINYINT UNSIGNED NOT NULL DEFAULT 50,
  confidence ENUM('verified','suggested','ai_draft') NOT NULL DEFAULT 'suggested',
  status ENUM('draft','active','rejected') NOT NULL DEFAULT 'active',
  created_by VARCHAR(80) NOT NULL DEFAULT 'seed',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_relation (source_code,target_code,relation_type),
  KEY idx_src (source_code),
  KEY idx_tgt (target_code),
  KEY idx_rtype (relation_type),
  KEY idx_conf (confidence),
  CONSTRAINT fk_rel_src FOREIGN KEY (source_code) REFERENCES kb_entities(code) ON DELETE CASCADE,
  CONSTRAINT fk_rel_tgt FOREIGN KEY (target_code) REFERENCES kb_entities(code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۶) فاکت‌ها: ویژگی‌های مستقل چندبُعدی (غیر از درخت سلسله‌مراتب)
CREATE TABLE IF NOT EXISTS kb_entity_facets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_code VARCHAR(80) NOT NULL,
  facet_code VARCHAR(60) NOT NULL,
  value_fa VARCHAR(300) NOT NULL,
  value_en VARCHAR(300) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_facet (entity_code,facet_code,value_fa),
  KEY idx_facet (facet_code,value_fa(64)),
  CONSTRAINT fk_facet_entity FOREIGN KEY (entity_code) REFERENCES kb_entities(code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۷) پیوند به محتوای دیتابیس اصلی (cards / theorists / library_resources / categories)
CREATE TABLE IF NOT EXISTS kb_entity_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_code VARCHAR(80) NOT NULL,
  link_type ENUM('card','theorist','library_resource','category') NOT NULL,
  link_id VARCHAR(64) NOT NULL,
  link_title VARCHAR(500) NULL,
  note VARCHAR(500) NULL,
  confidence ENUM('verified','suggested') NOT NULL DEFAULT 'suggested',
  created_by VARCHAR(80) NOT NULL DEFAULT 'seed',
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_link (entity_code,link_type,link_id),
  KEY idx_link (link_type,link_id),
  CONSTRAINT fk_link_entity FOREIGN KEY (entity_code) REFERENCES kb_entities(code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۸) ممیزی: ردپای کامل تغییرات هسته دانش
CREATE TABLE IF NOT EXISTS kb_audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_code VARCHAR(80) NULL,
  action VARCHAR(60) NOT NULL,
  detail JSON NULL,
  source VARCHAR(60) NOT NULL DEFAULT 'manual',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_entity (entity_code),
  KEY idx_audit_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
