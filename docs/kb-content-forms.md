# فرم‌های محتوایی ۲۸ نوع موجودیت — هسته دانش رسانامه

> مرجع طراحی ساختار محتوایی هر نوع برای سایت مخاطب‌نهادی.
> هر نوع سه لایه داده دارد:
> - **`properties`** — فیلدهای ساختاریافته مخصوص آن نوع (JSON)
> - **`facets`** — از ۹ بُعد مشترک
> - **`relations`** — پیوندهای نوع‌به‌نوع
>
> تک منبع حقیقت: `app/Knowledge/EntityTypes.php`

---

## نظام فکری، نظری و پارادایمی (theoretical)

### ۱. `SCHOOL` — مکتب فکری
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `founded_year` | سال شکل‌گیری |
| properties | `founded_place` | محل شکل‌گیری (شهر/کشور) |
| properties | `center` | مرکز علمی/دانشکده |
| properties | `key_works` | آثار بنیان‌گذاران |
| properties | `methodology_core` | روش‌شناسی محوری |
| properties | `epistemological_basis` | مبنا معرفت‌شناختی |
| properties | `key_debates` | مناقشات محوری |
| facets | `discipline`, `time_frame` | |
| relations | `PART_OF` → TRADITION | |
| relations | `DEVELOPED_BY` → PERSON | بنیان‌گذاران |
| relations | `INFLUENCED_BY` → PARADIGM/TRADITION | |
| relations | `CRITICIZES` → SCHOOL/TRADITION | |

### ۲. `TRADITION` — سنت فکری
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `origin_period` | دوره‌ی ظهور |
| properties | `origin_region` | منطقه‌ی ظهور |
| properties | `core_assumptions` | مفروضات بنیادین |
| properties | `methodological_implication` | پیامد روش‌شناختی |
| properties | `key_figures` | چهره‌های کلیدی |
| properties | `key_texts` | متون بنیادین |
| facets | `discipline`, `time_frame` | |
| relations | `PART_OF` → DISCIPLINE | |
| relations | `INFLUENCED` → SCHOOL/PARADIGM | |
| relations | `INFLUENCED_BY` → TRADITION | |
| relations | `CONTRADICTS` → TRADITION | |

### ۳. `PARADIGM` — پارادایم
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `ontology` | هستی‌شناسی (واقعیت چیست) |
| properties | `epistemology` | معرفت‌شناسی (دانش چگونه به دست می‌آید) |
| properties | `axiology` | ارزش‌شناسی |
| properties | `methodological_commitments` | تعهدات روش‌شناختی |
| properties | `key_assumptions` | مفروضات کلیدی |
| properties | `representative_scholars` | نمایندگان |
| facets | `discipline`, `time_frame` | |
| relations | `PROPOSED_BY` → PERSON | |
| relations | `INFLUENCED` → APPROACH/METHOD | |
| relations | `INFLUENCED_BY` → TRADITION/SCHOOL | |
| relations | `CONTRADICTS` → PARADIGM | |

### ۴. `THEORY` — نظریه
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `year_proposed` | سال پیشنهاد |
| properties | `proposing_context` | بستر/دلیل شکل‌گیری |
| properties | `core_question` | پرسش محوری |
| properties | `core_mechanism` | مکانیزم تبیین |
| properties | `key_variables` | متغیرهای کلیدی |
| properties | `domain_of_application` | حوزه‌ی کاربرد |
| properties | `empirical_findings` | یافته‌های تجربی کلیدی |
| properties | `limitations` | محدودیت‌ها |
| properties | `criticisms` | نقدهای اصلی |
| properties | `examples` | مثال‌های کاربردی |
| facets | `discipline`, `time_frame`, `theoretical_tradition` | |
| relations | `PROPOSED_BY` → PERSON | |
| relations | `DEVELOPED_BY` → SCHOOL | |
| relations | `INFLUENCED_BY` → PARADIGM/TRADITION | |
| relations | `EXTENDS` → THEORY | |
| relations | `CONTRADICTS` → THEORY | |
| relations | `APPLIES_TO` → SUBFIELD | |
| relations | `TESTED_BY` → DESIGN/METHOD | |

### ۵. `MODEL` — مدل
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `year_proposed` | سال پیشنهاد |
| properties | `diagram_description` | توصیف شماتیک |
| properties | `components` | اجزای مدل |
| properties | `process_flow` | جریان فرآیند |
| properties | `scope` | قلمرو کاربرد |
| properties | `limitations` | محدودیت‌ها |
| facets | `discipline`, `time_frame` | |
| relations | `PROPOSED_BY` → PERSON | |
| relations | `DERIVED_FROM` → THEORY | |
| relations | `OPERATIONALIZES` → THEORY | |

### ۶. `FRAMEWORK` — چارچوب نظری
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `year_proposed` | سال پیشنهاد |
| properties | `constituent_theories` | نظریه‌های تشکیل‌دهنده |
| properties | `integrating_logic` | منطق ترکیب |
| properties | `analytical_dimensions` | بُعد‌های تحلیلی |
| properties | `domain_of_application` | حوزه‌ی کاربرد |
| facets | `discipline`, `time_frame` | |
| relations | `DERIVED_FROM` → THEORY/MODEL | |
| relations | `PROPOSED_BY` → PERSON | |
| relations | `APPLIES_TO` → SUBFIELD | |

### ۷. `HYPOTHESIS` — فرضیه
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `variables` | متغیرها |
| properties | `direction` | جهت رابطه |
| properties | `status` | وضعیت (آزموده/غیرآزموده) |
| properties | `empirical_support` | حمایت تجربی |
| facets | `discipline` | |
| relations | `DERIVED_FROM` → THEORY | |
| relations | `TESTS` → THEORY | |
| relations | `PROPOSED_BY` → PERSON | |

### ۸. `LAW` — قانون علمی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `formulation` | صورت‌بندی |
| properties | `domain` | قلمرو |
| properties | `empirical_basis` | مبنای تجربی |
| properties | `limitations` | محدودیت‌ها |
| facets | `discipline` | |
| relations | `PROPOSED_BY` → PERSON | |
| relations | `DERIVED_FROM` → THEORY | |

### ۹. `CONCEPT` — مفهوم
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `origin_term` | ریشه‌ی واژه |
| properties | `dimensions` | بُعد‌ها/انواع |
| properties | `synonyms` | مترادف‌ها |
| properties | `antonyms` | متضادها |
| properties | `examples` | مثال‌ها |
| properties | `non_examples` | مثال‌های منفی |
| facets | `discipline`, `time_frame` | |
| relations | `DERIVED_FROM` → THEORY | |
| relations | `MEASURED_BY` → INSTRUMENT | |
| relations | `APPLIES_TO` → SUBFIELD | |
| relations | `RELATED_TO` → CONCEPT | |

### ۱۰. `DISCIPLINE` — رشته علمی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `foundational_question` | پرسش بنیادین |
| properties | `methodological_core` | هسته‌ی روش‌شناختی |
| properties | `key_institutions` | نهادهای کلیدی |
| properties | `key_journals` | مجلات کلیدی |
| properties | `subfields` | حوزه‌های تخصصی |
| facets | `time_frame` | |
| relations | `PART_OF` → TRADITION | |
| relations | `INFLUENCED_BY` → TRADITION/DISCIPLINE | |

### ۱۱. `SUBFIELD` — حوزه تخصصی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `scope` | قلمرو |
| properties | `key_topics` | موضوعات کلیدی |
| properties | `key_scholars` | چهره‌های کلیدی |
| properties | `key_journals` | مجلات کلیدی |
| facets | `discipline`, `time_frame` | |
| relations | `PART_OF` → DISCIPLINE | |
| relations | `PART_OF` → SCHOOL | |

---

## نظام روش‌شناسی و پژوهش (methodological)

### ۱۲. `METHODOLOGY` — روش‌شناسی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `philosophical_basis` | مبنا فلسفی |
| properties | `principles` | اصول |
| properties | `criteria` | معیارها |
| properties | `scope` | قلمرو |
| facets | `discipline` | |
| relations | `INFLUENCED_BY` → PARADIGM | |
| relations | `PROPOSED_BY` → PERSON | |

### ۱۳. `METHOD` — روش تحقیق
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `research_question_fit` | تناسب با پرسش پژوهش |
| properties | `data_type` | نوع داده |
| properties | `strengths` | نقاط قوت |
| properties | `limitations` | محدودیت‌ها |
| properties | `examples` | مثال‌های کاربردی |
| facets | `approach`, `data_type`, `discipline` | |
| relations | `DERIVED_FROM` → APPROACH | |
| relations | `USES` → TECHNIQUE/DESIGN | |
| relations | `TESTS` → THEORY | |

### ۱۴. `APPROACH` — رویکرد پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `philosophical_basis` | مبنا فلسفی |
| properties | `data_orientation` | جهت‌گیری داده |
| properties | `analysis_orientation` | جهت‌گیری تحلیل |
| properties | `strengths` | نقاط قوت |
| properties | `limitations` | محدودیت‌ها |
| facets | `approach`, `epistemology`, `discipline` | |
| relations | `DERIVED_FROM` → PARADIGM | |
| relations | `INFLUENCED` → METHOD | |
| relations | `INFLUENCED_BY` → PARADIGM | |

### ۱۵. `DESIGN` — طرح پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `structure` | ساختار (توصیفی/تجربی/آمیخته) |
| properties | `data_collection_strategy` | راهبرد گردآوری |
| properties | `data_analysis_strategy` | راهبرد تحلیل |
| properties | `validity_strategies` | راهبردهای اعتبار |
| properties | `limitations` | محدودیت‌ها |
| properties | `examples` | مثال‌ها |
| facets | `approach`, `data_type`, `discipline` | |
| relations | `DERIVED_FROM` → METHOD | |
| relations | `USES` → TECHNIQUE/INSTRUMENT/SAMPLING | |
| relations | `TESTS` → THEORY | |

### ۱۶. `STRATEGY` — استراتژی پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `positioning` | جایگاه بین روش و فن |
| properties | `components` | اجزا |
| properties | `examples` | مثال‌ها |
| facets | `discipline` | |
| relations | `PART_OF` → METHOD | |
| relations | `USES` → TECHNIQUE | |

### ۱۷. `TECHNIQUE` — فن پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `procedure` | مراحل اجرا |
| properties | `data_type_produced` | نوع داده‌ی تولیدشده |
| properties | `strengths` | نقاط قوت |
| properties | `limitations` | محدودیت‌ها |
| properties | `examples` | مثال‌ها |
| facets | `data_type`, `discipline` | |
| relations | `USES` → INSTRUMENT | |
| relations | `PART_OF` → DESIGN/STRATEGY | |
| relations | `PROPOSED_BY` → PERSON | |

### ۱۸. `INSTRUMENT` — ابزار پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `type` | نوع (مقیاس/فرم/پرسشنامه) |
| properties | `items_count` | تعداد آیتم |
| properties | `validity` | روایی |
| properties | `reliability` | پایایی (ضریب آلفا و ...) |
| properties | `population_validity` | اعتبار برای جامعه |
| properties | `original_source` | منبع اصلی |
| properties | `localized_versions` | نسخه‌های بومی‌سازی‌شده |
| facets | `data_type`, `discipline` | |
| relations | `MEASURES` → CONCEPT | |
| relations | `USES` → TECHNIQUE | |
| relations | `DEVELOPED_BY` → PERSON | |

### ۱۹. `SAMPLING` — روش نمونه‌گیری
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `sampling_frame` | چارچوب نمونه‌گیری |
| properties | `sample_size_guidelines` | راهنمای حجم نمونه |
| properties | `strengths` | نقاط قوت |
| properties | `limitations` | محدودیت‌ها |
| properties | `bias_risks` | ریسک‌های سوگیری |
| facets | `approach`, `discipline` | |
| relations | `USES` → TECHNIQUE | |
| relations | `PART_OF` → DESIGN | |

### ۲۰. `ANALYSIS` — روش تحلیل داده
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `data_type_required` | نوع داده‌ی مورد نیاز |
| properties | `procedure` | مراحل اجرا |
| properties | `output` | خروجی (یافته/مدل/گزارش) |
| properties | `assumptions` | مفروضات |
| properties | `software_commonly_used` | نرم‌افزارهای رایج |
| properties | `strengths` | نقاط قوت |
| properties | `limitations` | محدودیت‌ها |
| properties | `examples` | مثال‌ها |
| facets | `data_type`, `approach`, `discipline` | |
| relations | `USES` → SOFTWARE | |
| relations | `MEASURES` → CONCEPT | |
| relations | `PART_OF` → DESIGN | |

### ۲۱. `QUALITY` — معیار کیفیت پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `what_it_ensures` | چه چیزی را تضمین می‌کند |
| properties | `how_achieved` | چگونگی دستیابی |
| properties | `approach_specific` | آیا روش‌بسته است؟ |
| properties | `quantitative_indicators` | شاخص‌های کمّی (اگر دارد) |
| properties | `qualitative_indicators` | شاخص‌های کیفی |
| properties | `limitations` | محدودیت‌ها |
| facets | `approach` | |
| relations | `APPLIES_TO` → METHOD/DESIGN/ANALYSIS | |
| relations | `INFLUENCED_BY` → PARADIGM | |

### ۲۲. `ETHICS` — اصل اخلاقی پژوهش
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `scope` | قلمرو کاربرد |
| properties | `implementation` | چگونگی اجرا |
| properties | `challenges` | چالش‌های عملی |
| properties | `guidelines` | دستورالعمل‌های مرتبط |
| facets | `discipline` | |
| relations | `APPLIES_TO` → DESIGN/METHOD | |
| relations | `INFLUENCED_BY` → PARADIGM | |

### ۲۳. `SOFTWARE` — نرم‌افزار پژوهشی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `version` | نسخه |
| properties | `vendor` | تولیدکننده |
| properties | `license_type` | نوع لایسنس |
| properties | `platforms` | پلتفرم‌ها |
| properties | `key_features` | قابلیت‌های کلیدی |
| properties | `suitable_for` | مناسب برای چه تحلیل‌ها |
| properties | `learning_curve` | منحنی یادگیری |
| properties | `alternatives` | جایگزین‌ها |
| facets | `discipline`, `data_type` | |
| relations | `USES` → ANALYSIS | |
| relations | `MEASURES` → CONCEPT | |

### ۲۴. `PROTOCOL` — پروتکل پژوهشی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `definition` | تعریف |
| properties | `standard_body` | نهاد استاندارددهنده |
| properties | `scope` | قلمرو |
| properties | `steps` | مراحل |
| properties | `quality_criteria` | معیارهای کیفیت |
| properties | `common_variants` | واریانت‌های رایج |
| facets | `discipline` | |
| relations | `PART_OF` → ANALYSIS/TECHNIQUE | |
| relations | `USES` → TECHNIQUE | |

### ۲۵. `DATASET` — داده و منبع
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `description` | توضیح |
| properties | `source` | منبع گردآوری |
| properties | `time_period` | دوره زمانی پوشش |
| properties | `geographic_scope` | قلمرو جغرافیایی |
| properties | `sample_size` | حجم |
| properties | `variables_available` | متغیرهای در دسترس |
| properties | `access` | دسترسی (آزاد/محدود/تجاری) |
| properties | `citation` | ارجاع‌دهی |
| properties | `limitations` | محدودیت‌ها |
| facets | `data_type`, `discipline`, `time_frame` | |
| relations | `COLLECTS` → TECHNIQUE | |
| relations | `APPLIES_TO` → SUBFIELD | |

---

## نظام مشترک (shared)

### ۲۶. `PERSON` — اندیشمند و نظریه‌پرداز
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `birth_year` | سال تولد |
| properties | `death_year` | سال درگذشت (اگر دارد) |
| properties | `birth_place` | محل تولد |
| properties | `education` | تحصیلات |
| properties | `academic_positions` | مناصب آکادمیک |
| properties | `intellectual_context` | بستر فکری/دوره |
| properties | `main_contributions` | مشارکت‌های اصلی |
| properties | `influences_on` | تأثیر بر چه حوزه‌هایی |
| properties | `influenced_by` | متأثر از چه سنت‌ها/مکتب‌ها |
| properties | `key_critics` | ناقدین اصلی |
| properties | `criticisms_received` | نقدهای دریافتی |
| properties | `legacy` | میراث فکری |
| facets | `discipline`, `time_frame` | |
| relations | `PROPOSED_BY` → THEORY/MODEL/WORK | آثارش |
| relations | `DEVELOPED_BY` → SCHOOL | |
| relations | `INFLUENCED_BY` → TRADITION/SCHOOL/PARADIGM | |
| relations | `INFLUENCED` → THEORY/SCHOOL | |
| relations | `CRITICIZES` → THEORY/SCHOOL | |
| relations | `COMPARED_WITH` → PERSON | |
| links | → `theorists` (سایت اصلی) | |

### ۲۷. `WORK` — اثر علمی بنیادین
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `work_type` | نوع (کتاب/مقاله/فصل) |
| properties | `year` | سال انتشار |
| properties | `publisher` | ناشر |
| properties | `language_original` | زبان اصلی |
| properties | `language_translation` | زبان ترجمه |
| properties | `page_count` | تعداد صفحات (اگر کتاب) |
| properties | `abstract` | چکیده |
| properties | `main_arguments` | استدلال‌های اصلی |
| properties | `impact` | تأثیرگذاری |
| properties | `citations_count` | تعداد استناد (اگر دارد) |
| properties | `available_versions` | نسخه‌های در دسترس |
| facets | `discipline`, `time_frame` | |
| relations | `PROPOSED_BY` → PERSON | نویسندگان |
| relations | `INFLUENCED` → THEORY/SCHOOL | |
| relations | `INFLUENCED_BY` → THEORY/TRADITION | |
| relations | `DERIVED_FROM` → THEORY | |
| relations | `COMPARED_WITH` → WORK | |

### ۲۸. `DISPUTE` — مناقشه علمی
| لایه | فیلد | توضیح |
| --- | --- | --- |
| properties | `topic` | موضوع مناقشه |
| properties | `parties` | طرفین (چند PERSON/THEORY/SCHOOL) |
| properties | `core_disagreement` | اختلاف اصلی |
| properties | `arguments_side_a` | استدلال‌های طرف الف |
| properties | `arguments_side_b` | استدلال‌های طرف ب |
| properties | `resolution_status` | وضعیت (حل‌شده/در حال/باز) |
| properties | `key_texts` | متون کلیدی |
| properties | `historical_context` | بستر تاریخی |
| facets | `discipline`, `time_frame` | |
| relations | `CONTRADICTS` → THEORY/SCHOOL/PARADIGM | |
| relations | `COMPARED_WITH` → DISPUTE | |
| relations | `PROPOSED_BY` → PERSON | طرفین |

---

## خلاصه‌ی روابط بین‌نوع‌ها

| از نوع | به نوع | رابطه |
| --- | --- | --- |
| SCHOOL | TRADITION | `PART_OF` |
| SCHOOL | PERSON | `DEVELOPED_BY` |
| SCHOOL | PARADIGM/TRADITION | `INFLUENCED_BY` |
| SCHOOL | SCHOOL/TRADITION | `CRITICIZES` |
| TRADITION | DISCIPLINE | `PART_OF` |
| TRADITION | SCHOOL/PARADIGM | `INFLUENCED` |
| TRADITION | TRADITION | `CONTRADICTS`, `INFLUENCED_BY` |
| PARADIGM | PERSON | `PROPOSED_BY` |
| PARADIGM | APPROACH/METHOD | `INFLUENCED` |
| PARADIGM | TRADITION/SCHOOL | `INFLUENCED_BY` |
| PARADIGM | PARADIGM | `CONTRADICTS` |
| THEORY | PERSON | `PROPOSED_BY` |
| THEORY | SCHOOL | `DEVELOPED_BY` |
| THEORY | PARADIGM/TRADITION | `INFLUENCED_BY` |
| THEORY | THEORY | `EXTENDS`, `CONTRADICTS` |
| THEORY | SUBFIELD | `APPLIES_TO` |
| THEORY | DESIGN/METHOD | `TESTED_BY` |
| MODEL | PERSON | `PROPOSED_BY` |
| MODEL | THEORY | `DERIVED_FROM`, `OPERATIONALIZES` |
| FRAMEWORK | THEORY/MODEL | `DERIVED_FROM` |
| FRAMEWORK | PERSON | `PROPOSED_BY` |
| FRAMEWORK | SUBFIELD | `APPLIES_TO` |
| HYPOTHESIS | THEORY | `DERIVED_FROM`, `TESTS` |
| HYPOTHESIS | PERSON | `PROPOSED_BY` |
| LAW | PERSON | `PROPOSED_BY` |
| LAW | THEORY | `DERIVED_FROM` |
| CONCEPT | THEORY | `DERIVED_FROM` |
| CONCEPT | INSTRUMENT | `MEASURED_BY` |
| CONCEPT | SUBFIELD | `APPLIES_TO` |
| CONCEPT | CONCEPT | `RELATED_TO` |
| DISCIPLINE | TRADITION | `PART_OF` |
| DISCIPLINE | TRADITION/DISCIPLINE | `INFLUENCED_BY` |
| SUBFIELD | DISCIPLINE | `PART_OF` |
| SUBFIELD | SCHOOL | `PART_OF` |
| METHODOLOGY | PARADIGM | `INFLUENCED_BY` |
| METHODOLOGY | PERSON | `PROPOSED_BY` |
| METHOD | APPROACH | `DERIVED_FROM` |
| METHOD | TECHNIQUE/DESIGN | `USES` |
| METHOD | THEORY | `TESTS` |
| APPROACH | PARADIGM | `DERIVED_FROM`, `INFLUENCED_BY` |
| APPROACH | METHOD | `INFLUENCED` |
| DESIGN | METHOD | `DERIVED_FROM` |
| DESIGN | TECHNIQUE/INSTRUMENT/SAMPLING | `USES` |
| DESIGN | THEORY | `TESTS` |
| STRATEGY | METHOD | `PART_OF` |
| STRATEGY | TECHNIQUE | `USES` |
| TECHNIQUE | INSTRUMENT | `USES` |
| TECHNIQUE | DESIGN/STRATEGY | `PART_OF` |
| TECHNIQUE | PERSON | `PROPOSED_BY` |
| INSTRUMENT | CONCEPT | `MEASURES` |
| INSTRUMENT | TECHNIQUE | `USES` |
| INSTRUMENT | PERSON | `DEVELOPED_BY` |
| SAMPLING | TECHNIQUE | `USES` |
| SAMPLING | DESIGN | `PART_OF` |
| ANALYSIS | SOFTWARE | `USES` |
| ANALYSIS | CONCEPT | `MEASURES` |
| ANALYSIS | DESIGN | `PART_OF` |
| QUALITY | METHOD/DESIGN/ANALYSIS | `APPLIES_TO` |
| QUALITY | PARADIGM | `INFLUENCED_BY` |
| ETHICS | DESIGN/METHOD | `APPLIES_TO` |
| ETHICS | PARADIGM | `INFLUENCED_BY` |
| SOFTWARE | ANALYSIS | `USES` |
| SOFTWARE | CONCEPT | `MEASURES` |
| PROTOCOL | ANALYSIS/TECHNIQUE | `PART_OF` |
| PROTOCOL | TECHNIQUE | `USES` |
| DATASET | TECHNIQUE | `COLLECTS` |
| DATASET | SUBFIELD | `APPLIES_TO` |
| PERSON | THEORY/MODEL/WORK | `PROPOSED_BY` |
| PERSON | SCHOOL | `DEVELOPED_BY` |
| PERSON | TRADITION/SCHOOL/PARADIGM | `INFLUENCED_BY` |
| PERSON | THEORY/SCHOOL | `INFLUENCED` |
| PERSON | THEORY/SCHOOL | `CRITICIZES` |
| PERSON | PERSON | `COMPARED_WITH` |
| WORK | PERSON | `PROPOSED_BY` |
| WORK | THEORY/SCHOOL | `INFLUENCED` |
| WORK | THEORY/TRADITION | `INFLUENCED_BY` |
| WORK | THEORY | `DERIVED_FROM` |
| WORK | WORK | `COMPARED_WITH` |
| DISPUTE | THEORY/SCHOOL/PARADIGM | `CONTRADICTS` |
| DISPUTE | DISPUTE | `COMPARED_WITH` |
| DISPUTE | PERSON | `PROPOSED_BY` |

---

## فاکت‌های (facets) رایج به تفکیک نوع

| نوع | فاکت‌های توصیه‌شده |
| --- | --- |
| SCHOOL | `discipline`, `time_frame` |
| TRADITION | `discipline`, `time_frame` |
| PARADIGM | `discipline`, `time_frame`, `theoretical_tradition` |
| THEORY | `discipline`, `time_frame`, `theoretical_tradition` |
| MODEL | `discipline`, `time_frame` |
| FRAMEWORK | `discipline`, `time_frame` |
| HYPOTHESIS | `discipline` |
| LAW | `discipline` |
| CONCEPT | `discipline`, `time_frame` |
| DISCIPLINE | `time_frame` |
| SUBFIELD | `discipline`, `time_frame` |
| METHODOLOGY | `discipline` |
| METHOD | `approach`, `data_type`, `discipline` |
| APPROACH | `approach`, `epistemology`, `discipline` |
| DESIGN | `approach`, `data_type`, `discipline` |
| STRATEGY | `discipline` |
| TECHNIQUE | `data_type`, `discipline` |
| INSTRUMENT | `data_type`, `discipline` |
| SAMPLING | `approach`, `discipline` |
| ANALYSIS | `data_type`, `approach`, `discipline` |
| QUALITY | `approach` |
| ETHICS | `discipline` |
| SOFTWARE | `discipline`, `data_type` |
| PROTOCOL | `discipline` |
| DATASET | `data_type`, `discipline`, `time_frame` |
| PERSON | `discipline`, `time_frame` |
| WORK | `discipline`, `time_frame` |
| DISPUTE | `discipline`, `time_frame` |
