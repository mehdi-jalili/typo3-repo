<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgSpriteIconProvider;

return [
    'content-plugin-viewstatistics-visitors' => [
        'provider' => SvgSpriteIconProvider::class,
        'source' => 'EXT:view_statistics/Resources/Public/Icons/iconmonstr-chart-18.svg',
        'sprite' => 'EXT:view_statistics/Resources/Public/Icons/backend-sprites.svg#content-plugin-viewstatistics-visitors',
    ],
    'mimetypes-x-content-viewstatistics-track' => [
        'provider' => SvgSpriteIconProvider::class,
        'source' => 'EXT:view_statistics/Resources/Public/Icons/iconmonstr-chart-18.svg',
        'sprite' => 'EXT:view_statistics/Resources/Public/Icons/backend-sprites.svg#mimetypes-x-content-viewstatistics-track',
    ],
];
