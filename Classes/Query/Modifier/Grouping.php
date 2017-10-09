<?php
namespace ApacheSolrForTypo3\Solrfluidgrouping\Query\Modifier;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use ApacheSolrForTypo3\Solr\Query;
use ApacheSolrForTypo3\Solr\Query\Modifier\Modifier;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\Util;

/**
 * Modifies a query to add grouping parameters
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @author Frans Saris <frans@beech.it>
 */
class Grouping implements Modifier
{

    /**
     * Solr configuration
     *
     * @var TypoScriptConfiguration
     */
    protected $configuration;

    /**
     * Grouping related configuration
     *
     * plugin.tx.solr.search.grouping
     *
     * @var array
     */
    protected $groupingConfiguration;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->configuration = Util::getSolrConfiguration();
        $this->groupingConfiguration = $this->configuration->getObjectByPathOrDefault('plugin.tx_solr.search.grouping.', ['groups.' => []]);
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
        $grouping = $query->getGrouping();

        $grouping->setIsEnabled(true);
        $grouping->setResultsPerGroup($this->findHighestGroupResultsLimit());
        if (!empty($this->groupingConfiguration['numberOfGroups'])) {
            $grouping->setNumberOfGroups($this->groupingConfiguration['numberOfGroups']);
        }

        $configuredGroups = $this->groupingConfiguration['groups.'];
        foreach ($configuredGroups as $groupName => $groupConfiguration) {
            if (!empty($groupConfiguration['field'])) {
                $grouping->addField($groupConfiguration['field']);
            } elseif (!empty($groupConfiguration['queries.'])) {
                foreach ((array)$groupConfiguration['queries.'] as $_query) {
                    $grouping->addQuery($_query);
                }
            }

            if (isset($groupConfiguration['sortBy'])) {
                $grouping->addSorting($groupConfiguration['sortBy']);
            }
        }

        return $query;
    }

    /**
     * Finds the highest number of results per group.
     *
     * Checks the global setting, as well as each group configuration's
     * individual results limit.
     *
     * The lowest limit returned will be 1, as this is the default for Solr's
     * group.limit parameter. See http://wiki.apache.org/solr/FieldCollapsing
     *
     * @return int Highest number of results per group configured.
     */
    protected function findHighestGroupResultsLimit()
    {
        $highestLimit = 1;

        if (!empty($this->groupingConfiguration['numberOfResultsPerGroup'])) {
            $highestLimit = $this->groupingConfiguration['numberOfResultsPerGroup'];
        }

        $configuredGroups = $this->groupingConfiguration['groups.'];
        foreach ($configuredGroups as $groupName => $groupConfiguration) {
            if (!empty($groupConfiguration['numberOfResultsPerGroup'])
                && $groupConfiguration['numberOfResultsPerGroup'] > $highestLimit
            ) {
                $highestLimit = $groupConfiguration['numberOfResultsPerGroup'];
            }
        }

        return $highestLimit;
    }
}

