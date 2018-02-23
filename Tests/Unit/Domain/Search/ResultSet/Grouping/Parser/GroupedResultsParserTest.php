<?php
namespace ApacheSolrForTypo3\Solrfluidgrouping\Tests\Domain\Search\ResultSet\Grouping\Parser;

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

use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet;
use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequest;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\Tests\Unit\UnitTest;
use ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser\GroupedResultParser;

/**
 * Testcase to test the GroupedResultsParser.
 *
 * @author Timo Hund <timo.hund@dkd.de>
 * @package ApacheSolrForTypo3\Solr\Domain\Search\ResultSet
 */
class GroupedResultsParserTest extends UnitTest
{

    /**
     * @test
     */
    public function canParsedQueryGroupResult()
    {
        $configurationMock = $this->getDumbMock(TypoScriptConfiguration::class);
        $configurationMock->expects($this->any())->method('getSearchGroupingGroupsConfiguration')->willReturn([
            'pidQuery.' => [
                'queries.' => [
                    'lessThenTen' => 'pid:[0 TO 10]',
                    'lessThen30' => 'pid:[11 TO 30]',
                    'rest' => 'pid:[30 TO *]'
                ]
            ]
        ]);
        $configurationMock->expects($this->any())->method('getSearchGroupingResultLimit')->willReturn(5);

        $resultSet = $this->getSearchResultSetMockFromConfigurationAndFixtureFileName($configurationMock, 'fake_solr_response_group_on_queries.json');

        $parser = new GroupedResultParser();
        $searchResultsCollection = $parser->parse($resultSet);

        $this->assertTrue($searchResultsCollection->getHasGroups());
        $this->assertSame(1, $searchResultsCollection->getGroups()->getCount());

        $queryGroup = $searchResultsCollection->getGroups()->getByPosition(0)->getGroupItems();
        $this->assertSame(5, $queryGroup->getByPosition(0)->getSearchResults()->getCount());
        $this->assertSame(3, $queryGroup->getCount(), 'Unexpected amount of groups in parsing result');
    }

    /**
     * @test
     */
    public function canParsedQueryFieldResult()
    {
        $configurationMock = $this->getDumbMock(TypoScriptConfiguration::class);
        $configurationMock->expects($this->any())->method('getSearchGroupingGroupsConfiguration')->willReturn([
            'typeGroup.' => [
                'field' => 'type'
            ]
        ]);
        $configurationMock->expects($this->any())->method('getSearchGroupingResultLimit')->willReturn(5);

        $resultSet = $this->getSearchResultSetMockFromConfigurationAndFixtureFileName($configurationMock, 'fake_solr_response_group_on_type_field.json');

        $parser = new GroupedResultParser();
        $searchResultsCollection = $parser->parse($resultSet);

        $this->assertTrue($searchResultsCollection->getHasGroups());
        $this->assertSame(1, $searchResultsCollection->getGroups()->getCount(), 'There should be 1 Groups of search results');
        $this->assertSame(2, $searchResultsCollection->getGroups()->getByPosition(0)->getGroupItems()->getCount(), 'The group should contain two group items');

        /** @var $firstGroup Group */
        $firstGroup = $searchResultsCollection->getGroups()->getByPosition(0);
        $this->assertSame('typeGroup', $firstGroup->getGroupName(), 'Unexpected groupName for the first group');

        $typeGroup = $searchResultsCollection->getGroups()->getByPosition(0)->getGroupItems();
        $this->assertSame('pages', $typeGroup->getByPosition(0)->getGroupValue(), 'There should be 5 documents in the group pages');
        $this->assertSame(5, $typeGroup->getByPosition(0)->getSearchResults()->getCount(), 'There should be 5 documents in the group pages');

        $this->assertSame('tx_news_domain_model_news', $typeGroup->getByPosition(1)->getGroupValue(), 'There should be 2 documents in the group news');
        $this->assertSame(2, $typeGroup->getByPosition(1)->getSearchResults()->getCount(), 'There should be 2 documents in the group news');

        $this->assertSame(7, $searchResultsCollection->getCount(), 'There should be a 7 search results when they are fetched without groups');
    }

    /**
     * @param TypoScriptConfiguration $configurationMock
     * @param string $fixtureName
     * @return SearchResultSet
     */
    protected function getSearchResultSetMockFromConfigurationAndFixtureFileName(TypoScriptConfiguration $configurationMock, string $fixtureName)
    {
        $searchRequestMock = $this->getDumbMock(SearchRequest::class);
        $searchRequestMock->expects($this->any())->method('getContextTypoScriptConfiguration')->will($this->returnValue($configurationMock));
        $resultSet = $this->getMockBuilder(SearchResultSet::class)->setMethods(['getUsedSearchRequest', 'getResponse'])->getMock();
        $resultSet->expects($this->any())->method('getUsedSearchRequest')->willReturn($searchRequestMock);
        $resultSet->expects($this->any())->method('getResponse')->willReturn($this->getFakeApacheSolrResponse($fixtureName));

        return $resultSet;
    }

    /**
     * @param $fixtureFile
     * @return \Apache_Solr_Response
     */
    protected function getFakeApacheSolrResponse($fixtureFile)
    {
        $fakeResponseJson = $this->getFixtureContentByName($fixtureFile);
        $httpResponseMock = $this->getDumbMock('\Apache_Solr_HttpTransport_Response');
        $httpResponseMock->expects($this->any())->method('getBody')->will($this->returnValue($fakeResponseJson));
        return new \Apache_Solr_Response($httpResponseMock);
    }
}