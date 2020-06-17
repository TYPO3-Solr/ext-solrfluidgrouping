<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'Apache Solr for TYPO3 - Grouping for fluid rendering',
    'description' => 'This addon provides the grouping for the fluid templating',
    'version' => '2.1.0',
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
            'solr' => '9.0.0-',
            'extbase' => '8.7.0-10.4.99',
            'fluid' => '8.7.0-10.4.99',
            'typo3' => '8.7.0-10.4.99'
        ),
        'conflicts' => array(),
    ),
    'autoload' => array(
        'psr-4' => array(
            'ApacheSolrForTypo3\\Solrfluidgrouping\\' => 'Classes/',
            'ApacheSolrForTypo3\\Solrfluidgrouping\\Tests\\' => 'Tests/'
        )
    )
);
