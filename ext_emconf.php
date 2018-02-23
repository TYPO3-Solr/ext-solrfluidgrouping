<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'Apache Solr for TYPO3 - Grouping for fluid rendering',
    'description' => 'This addon provides the grouping for the fluid templating',
    'version' => '1.0.0-dev',
    'state' => 'stable',
    'category' => 'plugin',
    'author' => 'Timo Hund, Frans Saris',
    'author_email' => 'timo.hund@dkd.de',
    'author_company' => 'dkd Internet Service GmbH',
    'module' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'constraints' => array(
        'depends' => array(
            'scheduler' => '',
            'solr' => '8.0.1-',
            'extbase' => '8.7.0-8.9.99',
            'fluid' => '8.7.0-8.9.99',
            'typo3' => '8.7.0-8.9.99'
        ),
        'conflicts' => array(),
        'suggests' => array(
            'devlog' => '',
        ),
    ),
    'autoload' => array(
        'psr-4' => array(
            'ApacheSolrForTypo3\\Solrfluidgrouping\\' => 'Classes/',
            'ApacheSolrForTypo3\\Solrfluidgrouping\\Tests\\' => 'Tests/'
        )
    )
);
