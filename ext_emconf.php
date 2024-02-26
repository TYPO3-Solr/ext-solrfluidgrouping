<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Apache Solr for TYPO3 - Grouping for fluid rendering',
    'description' => 'This addon provides the grouping for the fluid templating',
    'version' => '11.0.1',
    'state' => 'stable',
    'category' => 'plugin',
    'author' => 'Timo Hund, Frans Saris',
    'author_email' => 'solr-eb-support@dkd.de',
    'author_company' => 'dkd Internet Service GmbH',
    'clearCacheOnLoad' => 0,
    'constraints' => [
        'depends' => [
            'solr' => '11.5.0-11.9.99',
            'typo3' => '11.5.4-11.9.99',
        ],
        'conflicts' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'ApacheSolrForTypo3\\Solrfluidgrouping\\' => 'Classes/',
            'ApacheSolrForTypo3\\Solrfluidgrouping\\Tests\\' => 'Tests/',
        ],
    ],
];
