CREATE TABLE IF NOT EXISTS editorial_users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(80) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 display_name VARCHAR(160) NOT NULL,
 role ENUM('editor','admin') NOT NULL DEFAULT 'editor',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 last_login_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 source_table VARCHAR(64) NOT NULL,
 source_id VARCHAR(64) NOT NULL,
 title VARCHAR(500) NOT NULL,
 source_status VARCHAR(40) NULL,
 source_updated_at DATETIME NULL,
 original_json LONGTEXT NOT NULL,
 original_hash CHAR(64) NOT NULL,
 current_json LONGTEXT NOT NULL,
 current_hash CHAR(64) NOT NULL,
 dossier_json LONGTEXT NULL,
 dossier_hash CHAR(64) NULL,
 structure_version TINYINT UNSIGNED NOT NULL DEFAULT 1,
 workflow_status ENUM('imported','queued','processing','draft_ready','review','approved','rejected','published','error') NOT NULL DEFAULT 'imported',
 quality_score TINYINT UNSIGNED NULL,
 content_category VARCHAR(40) NULL,
 content_type VARCHAR(80) NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_source (source_table,source_id),
 KEY idx_workflow (workflow_status,updated_at),
 KEY idx_title (title(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_revisions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 revision_type ENUM('original','ai_draft','editor_edit','approved') NOT NULL,
 content_json LONGTEXT NOT NULL,
 content_hash CHAR(64) NOT NULL,
 quality_score TINYINT UNSIGNED NULL,
 created_by BIGINT UNSIGNED NULL,
 note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_doc (document_id,created_at),
 CONSTRAINT fk_revision_doc FOREIGN KEY (document_id) REFERENCES editorial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_jobs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 job_type VARCHAR(40) NOT NULL,
 status ENUM('pending','running','done','failed') NOT NULL DEFAULT 'pending',
 attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
 auto_retry TINYINT UNSIGNED NOT NULL DEFAULT 0,
 available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 locked_at DATETIME NULL,
 last_error TEXT NULL,
 feedback TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_queue (status,available_at),
 CONSTRAINT fk_job_doc FOREIGN KEY (document_id) REFERENCES editorial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_sources (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(1000) NOT NULL,
 url VARCHAR(2000) NULL,
 doi VARCHAR(255) NULL,
 publisher VARCHAR(500) NULL,
 source_type VARCHAR(40) NULL,
 verification_status ENUM('unverified','verified','rejected') NOT NULL DEFAULT 'unverified',
 evidence_note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_source_doc (document_id),
 CONSTRAINT fk_source_doc FOREIGN KEY (document_id) REFERENCES editorial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_reviews (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 editor_id BIGINT UNSIGNED NOT NULL,
 decision ENUM('approved','rejected','needs_changes') NOT NULL,
 note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_review_doc (document_id,created_at),
 CONSTRAINT fk_review_doc FOREIGN KEY (document_id) REFERENCES editorial_documents(id) ON DELETE CASCADE,
 CONSTRAINT fk_review_user FOREIGN KEY (editor_id) REFERENCES editorial_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_issues (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 job_id BIGINT UNSIGNED NULL,
 field_name VARCHAR(120) NULL,
 issue_type VARCHAR(40) NOT NULL DEFAULT 'other',
 severity ENUM('info','warning','error') NOT NULL DEFAULT 'warning',
 description TEXT NOT NULL,
 suggestion TEXT NULL,
 is_resolved TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_issue_doc (document_id),
 CONSTRAINT fk_issue_doc FOREIGN KEY (document_id) REFERENCES editorial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS editorial_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 level VARCHAR(20) NOT NULL,
 event VARCHAR(100) NOT NULL,
 context_json LONGTEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_log_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- داسییر ساختاریافته شخص: پروفایل ۲۱ فیلدی (۵ گروه) نظریه‌پردازان، دانشمندان و مؤثرین
-- ساختار رسمی: app/Structure/PersonDossier.php (تک منبع حقیقت)
CREATE TABLE IF NOT EXISTS editorial_person_dossiers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 document_id BIGINT UNSIGNED NOT NULL,
 person_type ENUM('theorist','scientist','influencer') NOT NULL DEFAULT 'theorist',
 structure_version TINYINT UNSIGNED NOT NULL DEFAULT 1,
 dossier_json LONGTEXT NOT NULL,
 dossier_hash CHAR(64) NOT NULL,
 completeness TINYINT UNSIGNED NULL,
 status ENUM('ai_draft','review','approved','published') NOT NULL DEFAULT 'ai_draft',
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 UNIQUE KEY uq_person_doc (document_id),
 KEY idx_person_status (status,updated_at),
 CONSTRAINT fk_person_dossier_doc FOREIGN KEY (document_id) REFERENCES editorial_documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
