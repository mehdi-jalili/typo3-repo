<?php

declare(strict_types=1);

return [
    \CodingMs\Modules\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \CodingMs\ViewStatistics\Domain\Model\Page::class => [
        'tableName' => 'pages'
    ],
    \CodingMs\ViewStatistics\Domain\Model\Track::class => [
        'tableName' => 'tx_viewstatistics_domain_model_track',
        'properties' => [
            'creationDate' => [
                'fieldName' => 'crdate'
            ]
        ]
    ],
    \CodingMs\ViewStatistics\Domain\Model\PageViewSummary::class => [
        'tableName' => 'tx_viewstatistics_domain_model_track',
        'properties' => [
            'creationDate' => [
                'fieldName' => 'crdate'
            ],
        ]
    ],
    \CodingMs\ViewStatistics\Domain\Model\TrackObject::class => [
        'tableName' => 'tx_viewstatistics_domain_model_track',
        'properties' => [
            'creationDate' => [
                'fieldName' => 'crdate'
            ],
            'type' => [
                'fieldName' => 'object_type'
            ]
        ]
    ],
];
