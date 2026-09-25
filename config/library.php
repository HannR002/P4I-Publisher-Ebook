<?php

return [
    'types' => [
        'book', 'journal', 'journal_article', 'article', 'proceeding',
        'report', 'module', 'monograph', 'other',
    ],
    'access_policies' => [
        'public_read_download', 'public_read_only',
        'registered_read_download', 'registered_read_only',
        'manual_purchase', 'external', 'physical_only',
    ],
    'rarely_read_after_days' => 30,
    'rarely_read_threshold' => 3,
];
