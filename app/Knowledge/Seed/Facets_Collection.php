<?php
namespace Resaname\Knowledge\Seed;
return [
    // ---------------- ۷.۱ پرسش‌محور ----------------
    ['collection-survey', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['عددی', 'متنی', 'صوتی']]],
    ['technique-questionnaire', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['عددی', 'متنی'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-structured-interview', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['متنی', 'عددی'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-semi-structured-interview', ['approach' => 'کیفی', 'data_type' => ['متنی', 'صوتی'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-in-depth-interview', ['approach' => 'کیفی', 'data_type' => ['متنی', 'صوتی'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-group-interview', ['approach' => ['کیفی', 'کمّی'], 'data_type' => ['متنی', 'صوتی']]],
    ['technique-focus-group', ['approach' => 'کیفی', 'data_type' => ['متنی', 'صوتی'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-delphi', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['عددی', 'متنی']]],
    ['technique-online-survey', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['عددی', 'متنی'], 'analysis_scale' => 'خرد/بین‌فردی']],

    // ---------------- ۷.۲ مشاهده‌محور ----------------
    ['collection-observation', ['approach' => ['کیفی', 'کمّی'], 'data_type' => 'رفتاری']],
    ['technique-direct-observation', ['approach' => 'کیفی', 'data_type' => 'رفتاری', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-participant-observation', ['approach' => 'کیفی', 'data_type' => 'رفتاری']],
    ['technique-nonparticipant-observation', ['approach' => ['کیفی', 'کمّی'], 'data_type' => 'رفتاری']],
    ['technique-structured-observation', ['approach' => 'کمّی', 'data_type' => 'رفتاری', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-naturalistic-observation', ['approach' => 'کیفی', 'data_type' => 'رفتاری']],
    ['technique-field-observation', ['approach' => 'کیفی', 'data_type' => 'رفتاری']],
    ['technique-digital-observation', ['approach' => 'کیفی', 'data_type' => ['رفتاری', 'دیجیتال']]],
    ['technique-digital-ethnography', ['approach' => 'کیفی', 'data_type' => ['متنی', 'دیجیتال'], 'analysis_scale' => 'میانه/سازمانی']],

    // ---------------- ۷.۳ اسنادی و محتوایی ----------------
    ['collection-documentary', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['اسنادی', 'متنی', 'تصویری', 'صوتی']]],
    ['technique-document-review', ['approach' => 'کیفی', 'data_type' => 'اسنادی']],
    ['technique-official-document-analysis', ['approach' => 'کیفی', 'data_type' => 'اسنادی', 'application_domain' => 'سیاست']],
    ['technique-archive-analysis', ['approach' => 'کیفی', 'data_type' => 'اسنادی', 'application_domain' => 'رسانه']],
    ['technique-media-content-analysis', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['متنی', 'تصویری'], 'application_domain' => 'رسانه']],
    ['technique-text-review', ['approach' => 'کیفی', 'data_type' => 'متنی', 'application_domain' => 'رسانه']],
    ['technique-image-analysis', ['approach' => ['کمّی', 'کیفی'], 'data_type' => 'تصویری', 'application_domain' => 'رسانه']],
    ['technique-video-analysis', ['approach' => ['کمّی', 'کیفی'], 'data_type' => ['تصویری', 'صوتی'], 'application_domain' => 'رسانه']],
    ['technique-user-posts-analysis', ['approach' => ['کیفی', 'محاسباتی'], 'data_type' => ['متنی', 'دیجیتال']]],

    // ---------------- ۷.۴ داده‌برداری دیجیتال ----------------
    ['collection-digital', ['approach' => 'محاسباتی', 'data_type' => ['دیجیتال', 'شبکه‌ای', 'رفتاری']]],
    ['technique-web-scraping', ['approach' => 'محاسباتی', 'data_type' => 'دیجیتال']],
    ['technique-api-extraction', ['approach' => 'محاسباتی', 'data_type' => 'دیجیتال']],
    ['technique-social-data-collection', ['approach' => 'محاسباتی', 'data_type' => ['دیجیتال', 'شبکه‌ای'], 'application_domain' => 'رسانه']],
    ['technique-user-behavior-tracking', ['approach' => 'محاسباتی', 'data_type' => ['رفتاری', 'دیجیتال'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-log-analysis', ['approach' => 'محاسباتی', 'data_type' => ['دیجیتال', 'رفتاری']]],
    ['technique-sensor-data', ['approach' => 'محاسباتی', 'data_type' => ['عددی', 'رفتاری']]],
    ['technique-digital-trace-analysis', ['approach' => 'محاسباتی', 'data_type' => 'دیجیتال']],

    // ---------------- ۷.۵ سنجش و اندازه‌گیری ----------------
    ['collection-measurement', ['approach' => 'کمّی', 'data_type' => 'عددی']],
    ['technique-psychometric-test', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-attitude-scale', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-satisfaction-measurement', ['approach' => 'کمّی', 'data_type' => 'عددی', 'application_domain' => 'رسانه']],
    ['technique-trust-measurement', ['approach' => 'کمّی', 'data_type' => 'عددی', 'application_domain' => 'رسانه']],
    ['technique-recognition-measurement', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-attention-measurement', ['approach' => 'کمّی', 'data_type' => ['عددی', 'رفتاری'], 'analysis_scale' => 'خرد/بین‌فردی']],
    ['technique-physiological-measurement', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'خرد/بین‌فردی']],

    // ---------------- ۸. نمونه‌گیری ----------------
    ['sampling-probability', ['approach' => 'کمّی']],
    ['sampling-simple-random', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'کلان/جامعه‌شناختی']],
    ['sampling-systematic', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'کلان/جامعه‌شناختی']],
    ['sampling-stratified', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'کلان/جامعه‌شناختی']],
    ['sampling-cluster', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'کلان/جامعه‌شناختی']],
    ['sampling-multistage', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'کلان/جامعه‌شناختی']],
    ['sampling-proportional', ['approach' => 'کمّی', 'data_type' => 'عددی']],
    ['sampling-multistage-stratified', ['approach' => 'کمّی', 'data_type' => 'عددی', 'analysis_scale' => 'کلان/جامعه‌شناختی']],

    ['sampling-nonprobability', ['approach' => 'کیفی', 'data_type' => ['عددی', 'متنی']]],
    ['sampling-convenience', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['sampling-purposive', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['sampling-snowball', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['sampling-quota', ['approach' => 'کمّی', 'data_type' => 'عددی', 'application_domain' => 'بازاریابی']],
    ['sampling-theoretical', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['sampling-case-volunteer', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['sampling-maximum-diversity', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],
    ['sampling-critical-cases', ['approach' => 'کیفی', 'data_type' => 'متنی']],
    ['sampling-tip-of-iceberg', ['approach' => 'کیفی', 'data_type' => 'متنی']],
    ['sampling-sequential', ['approach' => 'کیفی', 'data_type' => 'متنی', 'analysis_scale' => 'خرد/بین‌فردی']],

    // مفاهیم کلیدی نمونه‌گیری
    ['concept-population', ['approach' => 'کمّی', 'qualifies_for' => 'کمّی']],
    ['concept-sampling-frame', ['approach' => 'کمّی', 'qualifies_for' => 'کمّی']],
    ['concept-sampling-unit', ['approach' => ['کمّی', 'کیفی'], 'qualifies_for' => 'همه']],
    ['concept-unit-of-analysis', ['approach' => ['کمّی', 'کیفی'], 'qualifies_for' => 'همه']],
    ['concept-sample-size', ['approach' => 'کمّی', 'qualifies_for' => 'کمّی']],
    ['concept-theoretical-saturation', ['approach' => 'کیفی', 'qualifies_for' => 'کیفی']],
    ['concept-data-saturation', ['approach' => 'کیفی', 'qualifies_for' => 'کیفی']],
    ['concept-statistical-power', ['approach' => 'کمّی', 'qualifies_for' => 'کمّی']],
    ['concept-sampling-error', ['approach' => 'کمّی', 'qualifies_for' => 'کمّی']],
    ['concept-selection-bias', ['approach' => ['کمّی', 'کیفی'], 'qualifies_for' => 'همه']],
];
