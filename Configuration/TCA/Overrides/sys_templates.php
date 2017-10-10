<?php
defined('TYPO3_MODE') or die();
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'solrfluidgrouping',
    'Configuration/TypoScript/',
    'Search - (Example) Fluid result grouping on type field'
);