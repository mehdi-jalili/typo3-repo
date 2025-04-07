<?php

return [
    'frontend' => [
        'codingms/view-statistics' => [
            'target' => \CodingMs\ViewStatistics\Middleware\CheckDataSubmissionMiddleware::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ]
        ],
    ],
];
