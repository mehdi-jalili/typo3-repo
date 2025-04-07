# View-Statistics Change-Log

## 2025-04-01 Release of version 6.0.0

*	[TASK] Migrate to TYPO3 13 and remove support for TYPO3 11



## 2025-04-01 Release of version 5.0.2

*	[TASK] Remove jQuery dependency



## 2025-03-31 Release of version 5.0.1

*	[BUGFIX] Fix date-picker in backend module for TYPO3 12
*	[BUGFIX] Fix sorting in backend module



## 2024-08-08 Release of version 5.0.0

*	[TASK] Migrate to TYPO3 12 and remove support for TYPO3 10
*	[TASK] Migrate backend module with EXT:modules usage, in order to reduce maintenance effort



## 2023-06-27 Release of version 4.0.2

*	[BUGFIX] Fix undefined array index in news backend module
*	[BUGFIX] Backward compatibility for TYPO3 10 in PageService::getPageRepository



## 2023-01-17 Release of version 4.0.1

*	[BUGFIX] Clean up TYPO3 PHP constant usage and fix undefined array usage in PHP 8+



## 2022-11-21 Release of version 4.0.0

*	[TASK] Migration for TYPO3 11 and PHP 8, drop support for TYPO3 9



## 2022-11-21 Release of version 3.0.11

*	[BUGFIX] Fix file search
*	[BUGFIX] Fix object tracking with route enhancer
*	[BUGFIX] Fix documentation configuration
*	[TASK] Add documentations configuration



## 2022-05-16 Release of version 3.0.10

*	[BUGFIX] Fix object tracking with route enhancer
*	[BUGFIX] Fix documentation configuration
*	[TASK] Add documentations configuration



## 2021-07-19 Release of version 3.0.9

*	[TASK] Raise PHP requirement to 7.4.0



## 2021-06-07 Release of version 3.0.8

*	[BUGFIX] Fix check if non-admin user has access to page



## 2021-05-17 Release of version 3.0.7

*	[TASK] Add tag for TYPO3 10.4



## 2021-05-13 Release of version 3.0.6

*	[BUGFIX] Fix namespace issue in Middleware



## 2021-05-01 Release of version 3.0.5

*	[BUGFIX] Fix type casting issue for frontend user uid



## 2021-05-01 Release of version 3.0.4

*	[BUGFIX] Storing logged in user in tracking record



## 2021-02-01 Release of version 3.0.3

*	[BUGFIX] Using RootlineUtility to get rootline in PageService



## 2021-01-04 Release of version 3.0.2

*	[TASK] Add documentation translation file



## 2020-11-27 Release of version 3.0.1

*	[TASK] Pages tca modifications



## 2020-11-23 Release of version 3.0.0

*	[TASK] Dokumentation: visitor counter
*	[TASK] Lower-camel-case naming for defaultType
*	[FEATURE] Visitor counter
*	[TASK] Migration for TYPO3 10



## 2020-11-17 Release of version 2.0.1

Security fix - please update ASAP

*	[BUGFIX] Remove request params logging



## 2020-10-08 Release of version 2.0.0

*	[TASK] Move default object type to settings
*	[TASK] Migrate the extension for TYPO3 9.5
*	[!!!][TASK] Increase length of IP field in the database to accept IPv6 addresses. This changes the database structure.
*	[FEATURE] Make tracking user agents and login duration configurable in the extension settings
*	[BUGFIX] Fix "class 'CodingMs\ViewStatistics\ViewHelpers\Format\LoginDurationViewHelper' does not have a method 'render'" for TYPO3 8
*	[BUGFIX] Fix path to JavaScript file
*	[BUGFIX] Add missing label for user agent
*	[TASK] Add translation files for documentation
*	[TASK] Set default values if no extension settings exist
*	[TASK] Database and ORM migration
*	[TASK] ViewHelper migration
*	[TASK] Source code clean up
*	[TASK] Remove inject annotations
*	[TASK] Add configuration to track immobilien/properties from openimmo extension (realty, estate)
*	[TASK] Replace $_EXTKEY variable by static extension key string
*	[TASK] Add documentation files
*	[TASK] Clean up ChangeLog file



## 2020-05-27 Release of Version 1.0.4

*	[TASK] Cleanup Change-Log



## 2019-10-13 Release of version 1.0.3

*	[TASK] Add Gitlab-CI configuration.
*	[TASK] Providing a documentation about configuring own tracking objects.
*	[FEATURE] Track user agent of requests.



## 2017-11-23 Release of version 1.0.2

*	[BUGFIX] Fixing of tracking IP addresses
*	[BUGFIX] Adding group by in object SQL statement



## 2017-11-20 Release of version 1.0.1

*	[BUGFIX] Fixing sort ViewHelper



## 2017-11-19 Release of version 1.0.0

*	[FEATURE] Tracking IP-Address optionally
*	[FEATURE] Tracking for Referrer, Request-URI and language
*	[FEATURE] Restrictions for non admins
*	[TASK] Translations
*	[TASK] CSV-Export for Tracks and Frontend-User
*	[TASK] Backend-List for custom objects
