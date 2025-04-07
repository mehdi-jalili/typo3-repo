<?php

/***************************************************************
 *
 * Copyright notice
 *
 
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

$EM_CONF['view_statistics'] = [
    'title' => 'View frontend statistics',
    'description' => 'Logs frontend actions and display them in a backend module. Track page views, News, Downloads and custom objects. Optionally tracks frontend user logins and login durations. Alternative extension for Google-Analytics, Matomo, Piwik - this extension does not use any cookies! By default it does not track any personal data like IP address or even the user agent (though this can be activated optionally).',
    'category' => 'module',
    'version' => '6.0.0',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Mehdi Jalili',
    'author_email' => 'jalilimehdi.1366@gmail.com',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.3.99',
            'typo3' => '12.4.0-13.4.99',
            'modules' => '7.0.0-7.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
