<?php
namespace ApacheSolrForTypo3\Solrfluidgrouping\Query\Modifier;

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

use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequest;
use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequestAware;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\Query;
use ApacheSolrForTypo3\Solr\Query\Modifier\Modifier;


/**
 * Modifies a query to add grouping parameters
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @author Frans Saris <frans@beech.it>
 */
class Grouping implements Modifier, SearchRequestAware
{
    /**
     * @var SearchRequest
     */
    protected $searchRequest;

    /**
     * @param SearchRequest $searchRequest
     */
    public function setSearchRequest(SearchRequest $searchRequest)
    {
        $this->searchRequest = $searchRequest;
    }

    /**
     * Modifies the given query and adds the parameters necessary
     * for result grouping.
     *
     * @param Query $query The query to modify
     * @return Query The modified query with grouping parameters
     */
    public function modifyQuery(Query $query)
    {
        $isGroupingEnabled = $this->searchRequest->getContextTypoScriptConfiguration()->getSearchGrouping();
        if(!$isGroupingEnabled) {
            return $query;
        }

        $grouping = $query->getGrouping();
        $grouping->setIsEnabled(true);

        $groupingConfiguration = $this->searchRequest->getContextTypoScriptConfiguration()->getObjectByPathOrDefault('plugin.tx_solr.search.grouping.', []);

        // since apache solr does not support to set the offset per group we calculate the results perGroup value here to
        // cover the last document
        $highestGroupPage = $this->searchRequest->getHighestGroupPage();
        $highestLimit = $this->searchRequest->getContextTypoScriptConfiguration()->getSearchGroupingHighestGroupResultsLimit();
        $resultsPerGroup = $highestGroupPage * $highestLimit;

        $grouping->setResultsPerGroup($resultsPerGroup);

        if (!empty($groupingConfiguration['numberOfGroups'])) {
            $grouping->setNumberOfGroups($groupingConfiguration['numberOfGroups']);
        }

        $configuredGroups = $groupingConfiguration['groups.'];
        foreach ($configuredGroups as $groupName => $groupConfiguration) {
            if (!empty($groupConfiguration['field'])) {
                $grouping->addField($groupConfiguration['field']);
            } else {
                // query group
                if (!empty($groupConfiguration['queries.'])) {
                    foreach ((array)$groupConfiguration['queries.'] as $_query) {
                        $grouping->addQuery($_query);
                    }
                }
                if (!empty($groupConfiguration['query'])) {
                    $grouping->addQuery($groupConfiguration['query']);
                }
            }

            if (isset($groupConfiguration['sortBy'])) {
                $grouping->addSorting($groupConfiguration['sortBy']);
            }
        }

        return $query;
    }
}

