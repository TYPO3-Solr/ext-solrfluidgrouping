<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'solrfluidgrouping',
    'Configuration/TypoScript/Templates/',
    'Search - Use templates from solrfluidgrouping'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'solrfluidgrouping',
    'Configuration/TypoScript/Examples/TypeFieldGroup/',
    'Search - (Example) Fieldgroup on type field'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'solrfluidgrouping',
    'Configuration/TypoScript/Examples/PidQueryGroup/',
    'Search - (Example) Querygroup on pid field'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'solrfluidgrouping',
    'Configuration/TypoScript/Examples/GroupedSuggest/',
    'Search - (Example) Suggest with grouped results'
);