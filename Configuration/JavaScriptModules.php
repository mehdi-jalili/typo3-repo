<?php

return [
    'dependencies' => ['core', 'backend'],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@codingms/view-statistics/backend/date-picker.js' => 'EXT:view_statistics/Resources/Public/JavaScript/backend/date-picker.js',
    ],
];
