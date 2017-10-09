<?php

namespace ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser;




use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\Group;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\GroupCollection;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\GroupItem;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\GroupItemCollection;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\Parser\AbstractResultParser;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\SearchResultCollection;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class GroupedResultParser extends AbstractResultParser {

    /**
     * @param \ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet $resultSet
     * @param bool $useRawDocuments
     * @return \ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\SearchResultCollection
     */
    public function parse(SearchResultSet $resultSet, bool $useRawDocuments = true)
    {
        $searchResultCollection = new SearchResultCollection();
        $groupsConfiguration = $this->typoScriptConfiguration->getSearchGroupingGroupsConfiguration();

        if (empty($groupsConfiguration)) {
            return $searchResultCollection;
        }

        $parsedData = $resultSet->getResponse()->getParsedData();
        $allGroups = new GroupCollection();

        foreach ($groupsConfiguration as $name => $options) {
            $name = rtrim($name, '.');
            if(!empty($options['field'])) {
                $group = $this->parseFieldGroup($parsedData, $name, $options);
            } elseif(!empty($options['queries.'])) {
                $group = $this->parseQueryGroup($parsedData, $name, $options);
            }

            if($group === null) {
                continue;
            }

            $allGroups[] = $group;
            foreach($group->getGroupItems() as $groupItem) {
                /** @var $groupItem GroupItem */
                foreach($groupItem->getSearchResults() as $searchResult) {
                    $searchResultCollection[] = $searchResult;
                }
            }
        }

        $searchResultCollection->setGroups($allGroups);
        return $searchResultCollection;
    }

    /**
     * @param \stdClass $parsedData
     * @param string $groupedResultName
     * @param array $groupedResultConfiguration
     * @return Group
     */
    protected function parseFieldGroup($parsedData, $groupedResultName, $groupedResultConfiguration): Group
    {
        /** @var $group Group */
        $group = GeneralUtility::makeInstance(Group::class, $groupedResultName);

        if (empty($parsedData->grouped->{$groupedResultConfiguration['field']})) {
            return $group;
        }

        $rawGroupedResult = $parsedData->grouped->{$groupedResultConfiguration['field']};

        $groupItems = new GroupItemCollection();
        foreach ($rawGroupedResult->groups as $rawGroup) {
            $groupValue = $rawGroup->groupValue;
            $groupItem = $this->buildGroupItemAndAddDocuments($group, $groupValue, $rawGroup);

            if ($groupItem->getSearchResults()->count() >= 0) {
                $groupItems[] = $groupItem;
            }
        }

        $group->setGroupItems($groupItems);

        return $group;
    }


    /**
     * @param \stdClass $parsedData
     * @param string $groupedResultName
     * @param array $groupedResultConfiguration
     * @return Group
     */
    protected function parseQueryGroup($parsedData, $groupedResultName, $groupedResultConfiguration): Group
    {
        /** @var $group Group */
        $group = GeneralUtility::makeInstance(Group::class, $groupedResultName);
        $groupItems = new GroupItemCollection();

        foreach ($groupedResultConfiguration['queries.'] as $queryKey => $queryString) {
            $rawGroup = $this->getGroupedResultForQuery($parsedData, $queryString);

            if ($rawGroup === null) {
                continue;
            }

            $groupValue = $queryString;

            /** @var Group $group */
            $groupItem = $this->buildGroupItemAndAddDocuments($group, $groupValue, $rawGroup);

            if ($groupItem->getSearchResults()->count() >= 0) {
                $groupItems[] = $groupItem;
            }
        }

        return $group;
    }

    /**
     * @param Group $parentGroup
     * @param string $groupValue
     * @param \stdClass $rawGroup
     * @return GroupItem
     */
    protected function buildGroupItemAndAddDocuments(Group $parentGroup, $groupValue, $rawGroup): GroupItem
    {
        /** @var GroupItem $groupItem */
        $groupItem = GeneralUtility::makeInstance(GroupItem::class, $parentGroup,
            $groupValue,
            $rawGroup->doclist->numFound,
            $rawGroup->doclist->start,
            $rawGroup->doclist->maxScore);

        foreach ($rawGroup->doclist->docs as $rawDoc) {
            $solrDocument = new \Apache_Solr_Document();
            foreach(get_object_vars($rawDoc) as $key => $value) {
                $solrDocument->addField($key, $value);
            }

            $document = $this->searchResultBuilder->fromApacheSolrDocument($solrDocument);
            $document->setGroupItem($groupItem);

            $groupItem->addSearchResult($document);
        }
        return $groupItem;
    }

    /**
     * @param $parsedData
     * @param string $queryString
     * @return null
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
     * @param \ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet $resultSet
     * @return mixed
     */
    public function canParse(SearchResultSet $resultSet)
    {
        $groupsConfiguration = $this->typoScriptConfiguration->getSearchGroupingGroupsConfiguration();
        $groupingEnabled = $this->typoScriptConfiguration->getSearchGrouping();

        return $groupingEnabled && count($groupsConfiguration > 0);
    }
}