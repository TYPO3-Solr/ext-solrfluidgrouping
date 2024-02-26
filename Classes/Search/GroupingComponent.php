<?php

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

namespace ApacheSolrForTypo3\Solrfluidgrouping\Search;

use ApacheSolrForTypo3\Solr\Search\AbstractComponent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * GroupingComponent
 *
 * @author Frans Saris <frans@beech.it>
 */
class GroupingComponent extends AbstractComponent
{
    /**
     * Initializes the search component.
     */
    public function initializeSearchComponent()
    {
        $groupingEnabled = true;
        $solrGetParameters = GeneralUtility::_GET('tx_solr');

        if (!empty($this->searchConfiguration['grouping.']['allowGetParameterSwitch'])
            && isset($solrGetParameters['grouping'])
            && $solrGetParameters['grouping'] === 'off'
        ) {
            $groupingEnabled = false;
        }

        if ($this->searchConfiguration['grouping'] && $groupingEnabled) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['modifySearchQuery']['fluid_grouping'] =
                \ApacheSolrForTypo3\Solrfluidgrouping\Query\Modifier\Grouping::class;
        }
    }
}
