<?php
defined('TYPO3_MODE') or die();

(function () {
    if (class_exists(\ApacheSolrForTypo3\Solr\Search\SearchComponentManager::class)) {
        \ApacheSolrForTypo3\Solr\Search\SearchComponentManager::registerSearchComponent('fluid_grouping', \ApacheSolrForTypo3\Solrfluidgrouping\Search\GroupingComponent::class);
    }

    if (class_exists(\ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\Parser\ResultParserRegistry::class)) {
        /* @var \ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\Parser\ResultParserRegistry $parserRegistry */
        $parserRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\Parser\ResultParserRegistry::class);

        if(!$parserRegistry->hasParser(\ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser\GroupedResultParser::class, 200)) {
            $parserRegistry->registerParser(\ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser\GroupedResultParser::class, 200);
        }
    }
})();