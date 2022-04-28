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

use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\Grouping as GroupingParameter;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\QueryBuilder;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\Query;
use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequest;
use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequestAware;
use ApacheSolrForTypo3\Solr\Query\Modifier\Modifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;


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
     * QueryBuilder
     *
     * @var QueryBuilder|object
     */
    protected $queryBuilder;

    /**
     * AccessComponent constructor.
     * @param QueryBuilder|null
     */
    public function __construct(QueryBuilder $queryBuilder = null)
    {
        $this->queryBuilder = $queryBuilder ?? GeneralUtility::makeInstance(QueryBuilder::class);
    }

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
    public function modifyQuery(Query $query): Query
    {
        $arguments = $this->searchRequest->getArguments();
        $allowGetParameterSwitch = (bool) $this->searchRequest->getContextTypoScriptConfiguration()
            ->getValueByPathOrDefaultValue('plugin.tx_solr.search.grouping.allowGetParameterSwitch', false);
        if ($allowGetParameterSwitch && isset($arguments['grouping']) && $arguments['grouping'] === "off") {
            $this->queryBuilder->startFrom($query);
            $this->queryBuilder->useGrouping(GroupingParameter::getEmpty());
            // reset rows since it was previously set by solr/Classes/Domain/Search/Query/ParameterBuilder/Grouping.php:build
            $this->queryBuilder->useResultsPerPage($this->searchRequest->getResultsPerPage());
        }
        return $query;
    }
}

