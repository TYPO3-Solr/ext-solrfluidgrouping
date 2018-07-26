<?php

namespace ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Timo Hund <timo.hund@dkd.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\Group;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\GroupCollection;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\GroupItem;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\GroupItemCollection;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\Parser\AbstractResultParser;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\SearchResultCollection;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet;
use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GroupedResultParser
 * @package ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser
 */
class GroupedResultParser extends AbstractResultParser {

    /**
     * The parse method creates a SearchResultCollection from the Apache_Solr_Response
     * and creates the group object structure.
     *
     * @param SearchResultSet $resultSet
     * @param bool $useRawDocuments
     * @return SearchResultSet
     */
    public function parse(SearchResultSet $resultSet, bool $useRawDocuments = true): SearchResultSet
    {
        $searchResultCollection = new SearchResultCollection();

        $configuration = $resultSet->getUsedSearchRequest()->getContextTypoScriptConfiguration();
        $groupsConfiguration = $configuration->getSearchGroupingGroupsConfiguration();

        if (empty($groupsConfiguration)) {
            return $resultSet;
        }

        $searchResultCollection = $this->parseGroups($resultSet, $groupsConfiguration, $searchResultCollection);

        $this->calculateOverallMaximumScore($resultSet, $searchResultCollection);

        $resultSet->setSearchResults($searchResultCollection);
        return $resultSet;
    }

    /**
     * Parser the groups depending on the type (fieldGroup or queryGroup) and adds them to the searchResultCollection.
     *
     * @param SearchResultSet $resultSet
     * @param array $groupsConfigurations
     * @param SearchResultCollection $searchResultCollection
     * @return SearchResultCollection
     */
    protected function parseGroups(SearchResultSet $resultSet, $groupsConfigurations, $searchResultCollection): SearchResultCollection
    {
        $parsedData = $resultSet->getResponse()->getParsedData();
        $allGroups = new GroupCollection();

        foreach ($groupsConfigurations as $name => $groupsConfiguration) {
            $name = rtrim($name, '.');
            $group = $this->parseGroupDependingOnType($resultSet, $groupsConfiguration, $parsedData, $name);
            if ($group === null) {
                continue;
            }

            $allGroups[] = $group;
            $searchResultCollection = $this->addAllSearchResultsOfGroupToGlobalSearchResults($group, $searchResultCollection);
        }

        $searchResultCollection->setGroups($allGroups);
        return $searchResultCollection;
    }

    /**
     * Returns the parsedGroup, depending on the type.
     *
     * @param SearchResultSet $resultSet
     * @param array $options
     * @param \stdClass $parsedData
     * @param string $name
     * @return Group|null
     */
    protected function parseGroupDependingOnType(SearchResultSet $resultSet, array $options, \stdClass $parsedData, string $name)
    {
        if (!empty($options['field'])) {
            return $this->parseFieldGroup($resultSet, $parsedData, $name, $options);
        } elseif (!empty($options['queries.']) || !empty($options['query'])) {
            return $this->parseQueryGroup($resultSet, $parsedData, $name, $options);
        }

        return null;
    }

    /**
     * Parses the fieldGroup and creates the group object structure from it.
     *
     * @param \stdClass $parsedData
     * @param string $groupedResultName
     * @param array $groupedResultConfiguration
     * @return Group
     */
    protected function parseFieldGroup(SearchResultSet $resultSet, $parsedData, $groupedResultName, $groupedResultConfiguration): Group
    {
        /** @var $group Group */
        $resultsPerGroup = $resultSet->getUsedSearchRequest()->getContextTypoScriptConfiguration()->getSearchGroupingResultLimit($groupedResultName);
        $group = GeneralUtility::makeInstance(Group::class, $groupedResultName, $resultsPerGroup);

        if (empty($parsedData->grouped->{$groupedResultConfiguration['field']})) {
            return $group;
        }

        $rawGroupedResult = $parsedData->grouped->{$groupedResultConfiguration['field']};
        $groupItems = new GroupItemCollection();

        foreach ($rawGroupedResult->groups as $rawGroup) {
            $groupValue = $rawGroup->groupValue;
            $groupItem = $this->buildGroupItemAndAddDocuments($resultSet->getUsedSearchRequest(), $group, $groupValue, $rawGroup);

            if ($groupItem->getSearchResults()->count() >= 0) {
                $groupItems[] = $groupItem;
            }
        }

        $group->setGroupItems($groupItems);

        return $group;
    }

    /**
     * Parses the queryGroup and creates the group object structure from it.
     *
     * @param \stdClass $parsedData
     * @param string $groupedResultName
     * @param array $groupedResultConfiguration
     * @return Group
     */
    protected function parseQueryGroup(SearchResultSet $resultSet, $parsedData, $groupedResultName, $groupedResultConfiguration): Group
    {
        /** @var $group Group */
        $resultsPerGroup = $resultSet->getUsedSearchRequest()->getContextTypoScriptConfiguration()->getSearchGroupingResultLimit($groupedResultName);
        $group = GeneralUtility::makeInstance(Group::class, $groupedResultName, $resultsPerGroup);

        $groupItems = new GroupItemCollection();
        $queries = $this->getQueriesFromConfigurationArray($groupedResultConfiguration);
        foreach ($queries as $queryKey => $queryString) {
            $rawGroup = $this->getGroupedResultForQuery($parsedData, $queryString);

            if ($rawGroup === null) {
                continue;
            }

            $groupValue = $queryString;

            /** @var Group $group */
            $groupItem = $this->buildGroupItemAndAddDocuments($resultSet->getUsedSearchRequest(), $group, $groupValue, $rawGroup);

            if ($groupItem->getSearchResults()->count() >= 0) {
                $groupItems[] = $groupItem;
            }
        }

        $group->setGroupItems($groupItems);

        return $group;
    }

    /**
     * Retrieves all configured queries independent if they have been configured in query or queries.
     *
     * @todo This can be merged into TypoScriptConfiguration when solrfluidgrouping was merged to EXT:solr
     * @param array $configurationArray
     * @return array
     */
    protected function getQueriesFromConfigurationArray(array $configurationArray) {
        $queries = [];

        if(!empty($configurationArray['query'])) {
            $queries[] = $configurationArray['query'];
        }

        if(!empty($configurationArray['queries.']) && is_array($configurationArray['queries.'])) {
            $queries = array_merge($queries, $configurationArray['queries.']);
        }

        return $queries;
    }

    /**
     * Parses the raw documents and create SearchResultObjects from it.
     *
     * @param SearchRequest $searchRequest
     * @param Group $parentGroup
     * @param string $groupValue
     * @param \stdClass $rawGroup
     * @return GroupItem
     */
    protected function buildGroupItemAndAddDocuments(SearchRequest $searchRequest, Group $parentGroup, $groupValue, $rawGroup): GroupItem
    {
        /** @var GroupItem $groupItem */
        $groupItem = GeneralUtility::makeInstance(GroupItem::class, $parentGroup,
            $groupValue,
            $rawGroup->doclist->numFound,
            $rawGroup->doclist->start,
            $rawGroup->doclist->maxScore);

        $currentPage = $searchRequest->getGroupItemPage($parentGroup->getGroupName(), (string)$groupValue);
        $perPage = $parentGroup->getResultsPerPage();
        $offset = ($currentPage - 1) * $perPage;

        // since apache solr does not nativly support to set the offset per group, we get all documents to the current
        // page and slice the part of the results here, that we need
        $relevantResults = array_slice($rawGroup->doclist->docs, $offset, $perPage);

        foreach ($relevantResults as $rawDoc) {
            $solrDocument = new \Apache_Solr_Document();
            foreach(get_object_vars($rawDoc) as $key => $value) {
                $solrDocument->setField($key, $value);
            }

            $document = $this->searchResultBuilder->fromApacheSolrDocument($solrDocument);
            $document->setGroupItem($groupItem);

            $groupItem->addSearchResult($document);
        }
        return $groupItem;
    }

    /**
     * Extracts the grouped results for a queryGroup from a solr raw response.
     *
     * @param $parsedData
     * @param string $queryString
     * @return string|null
     */
    protected function getGroupedResultForQuery($parsedData, $queryString)
    {
        if (!empty($parsedData->grouped->{$queryString})) {
            return $parsedData->grouped->{$queryString};
        } else {
            return null;
        }
    }

    /**
     * Returns true when GroupingIsEnabled.
     *
     * @param \ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet $resultSet
     * @return bool
     */
    public function canParse(SearchResultSet $resultSet): bool
    {
        $configuration = $resultSet->getUsedSearchRequest()->getContextTypoScriptConfiguration();
        $groupsConfiguration = $configuration->getSearchGroupingGroupsConfiguration();
        $groupingEnabled = $configuration->getSearchGrouping();
        return $groupingEnabled && (count($groupsConfiguration) > 0);
    }

    /**
     * Adds all results from all groups the the global search results to have the available in a none grouped
     * view as well.
     *
     * @param Group $group
     * @param SearchResultCollection $searchResultCollection
     * @return SearchResultCollection
     */
    protected function addAllSearchResultsOfGroupToGlobalSearchResults(Group $group, SearchResultCollection $searchResultCollection): SearchResultCollection
    {
        foreach ($group->getGroupItems() as $groupItem) {
            /** @var $groupItem GroupItem */
            foreach ($groupItem->getSearchResults() as $searchResult) {
                $searchResultCollection[] = $searchResult;
            }
        }
        return $searchResultCollection;
    }

    /**
     * Calculates the overall maximum score and passed it to the SearchResultSet.
     *
     * @param SearchResultSet $resultSet
     * @param $searchResultCollection
     */
    private function calculateOverallMaximumScore(SearchResultSet $resultSet, $searchResultCollection)
    {
        $overAllMaximumScore = 0.0;
        foreach ($searchResultCollection->getGroups() as $group) {
            /** @var $group Group */
            foreach ($group->getGroupItems() as $groupItem) {
                /** @var $groupItem GroupItem */
                if ($groupItem->getMaximumScore() > $overAllMaximumScore) {
                    $overAllMaximumScore = $groupItem->getMaximumScore();
                }

            }
        }

        $resultSet->setMaximumScore($overAllMaximumScore);
    }
}