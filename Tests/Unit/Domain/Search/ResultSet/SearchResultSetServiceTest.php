<?php
namespace ApacheSolrForTypo3\Solrfluidgrouping\Tests\Domain\Search\ResultSet;

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

use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Grouping\Group;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\Parser\ResultParserRegistry;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\ResultSetReconstitutionProcessor;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSetService;
use ApacheSolrForTypo3\Solr\Domain\Search\SearchRequest;
use ApacheSolrForTypo3\Solr\Search;
use ApacheSolrForTypo3\Solr\System\Configuration\TypoScriptConfiguration;
use ApacheSolrForTypo3\Solr\Tests\Unit\Helper\FakeObjectManager;
use ApacheSolrForTypo3\Solr\Tests\Unit\UnitTest;
use ApacheSolrForTypo3\Solr\Util;
use ApacheSolrForTypo3\Solrfluidgrouping\Domain\Search\ResultSet\Grouping\Parser\GroupedResultParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This processor is used to transform the solr response into a
 * domain object hierarchy that can be used in the application (controller and view).
 *
 * @author Frans Saris <frans@beech.it>
 * @author Timo Hund <timo.hund@dkd.de>
 * @package ApacheSolrForTypo3\Solr\Domain\Search\ResultSet
 */
class SearchResultSetServiceTest extends UnitTest
{

    /**
     * @test
     */
    public function canCreateGroups()
    {

        // source: http://8.7.local.typo3.org:8083/solr/core_en/select?fl=*%2Cscore&fq=siteHash%3A%229e9d76a598c63d4ff578fea5c5254c27d9554fc6%22&fq={!typo3access}-1%2C0&expand.rows=10
        //  &debugQuery=true&echoParams=all&spellcheck=true&spellcheck.collate=true&spellcheck.maxCollationTries=1&enableElevation=false&
        //  qf=content^40.0+title^5.0+keywords^2.0+tagsH1^5.0+tagsH2H3^3.0+tagsH4H5H6^2.0+tagsInline+description^4.0+abstract+subtitle+navtitle+author&hl=true&hl.fragsize=200&
        //  hl.fl=content&hl.useFastVectorHighlighter=true&hl.tag.pre=%3Cspan+class%3D%22results-highlight%22%3E&hl.tag.post=%3C%2Fspan%3E&
        //  hl.simple.pre=%3Cspan+class%3D%22results-highlight%22%3E&hl.simple.post=%3C%2Fspan%3E&facet=true&facet.mincount=1&facet.limit=100&facet.field=type&
        //  facet.field=keywords_stringM&facet.field=author_stringM&f.author_stringM.facet.sort=count&facet.sort=index&group=true&group.format=grouped&group.ngroups=true&
        //  group.limit=5&group.field=type&wt=json&json.nl=map&q=*&start=0&rows=5
        $fakeResponse = $this->getFakeApacheSolrResponse('fake_solr_response_group_on_type_field.json');

        $searchMock = $this->getDumbMock(Search::class);

        /** @var $typoScriptConfigurationMock TypoScriptConfiguration */
        $typoScriptConfigurationMock = $this->getDumbMock(TypoScriptConfiguration::class);
        $typoScriptConfigurationMock->expects($this->any())->method('getSearchQueryReturnFieldsAsArray')->will($this->returnValue(['*']));
        $typoScriptConfigurationMock->expects($this->any())->method('getSearchGrouping')->will($this->returnValue(true));

        $configurationArray = [
            'plugin.' => [
                'tx_solr.' => [
                    'search.' => [
                        'grouping' => 1,
                        'grouping.' => [
                            'numberOfResultsPerGroup' => 5,
                            'numberOfGroups' => 2,
                            'groups.' => [
                                'typeGroup.' => [
                                    'field' => 'type'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $typoScriptConfiguration = new TypoScriptConfiguration($configurationArray);

            /** @var $parserRegistry ResultParserRegistry */
        $parserRegistry = GeneralUtility::makeInstance(ResultParserRegistry::class, $typoScriptConfiguration);
        $parserRegistry->registerParser(GroupedResultParser::class, 300);


            /** @var $searchResultSetService SearchResultSetService */
        $searchResultSetService = $this->getMockBuilder(SearchResultSetService::class)->setMethods(['doASearch'])->setConstructorArgs([$typoScriptConfiguration, $searchMock])->getMock();
        $searchResultSetService->expects($this->once())->method('doASearch')->will($this->returnValue($fakeResponse));

        $fakeRequest = $this->getDumbMock(SearchRequest::class);
        $fakeRequest->expects($this->any())->method('getResultsPerPage')->willReturn(10);
        $searchResultSet = $searchResultSetService->search($fakeRequest);

        $this->assertSame(1, $searchResultSet->getSearchResults()->getGroups()->getCount(), 'There should be 1 Groups of search results');
        $this->assertSame(2, $searchResultSet->getSearchResults()->getGroups()->getByPosition(0)->getGroupItems()->getCount(), 'The group should contain two group items');

            /** @var $firstGroup Group */
        $firstGroup = $searchResultSet->getSearchResults()->getGroups()->getByPosition(0);
        $this->assertSame('typeGroup', $firstGroup->getGroupName(), 'Unexpected groupName for the first group');

        $typeGroup = $searchResultSet->getSearchResults()->getGroups()->getByPosition(0)->getGroupItems();
        $this->assertSame('pages', $typeGroup->getByPosition(0)->getGroupValue(), 'There should be 5 documents in the group pages');
        $this->assertSame(5, $typeGroup->getByPosition(0)->getSearchResults()->getCount(), 'There should be 5 documents in the group pages');

        $this->assertSame('tx_news_domain_model_news', $typeGroup->getByPosition(1)->getGroupValue(), 'There should be 2 documents in the group news');
        $this->assertSame(2, $typeGroup->getByPosition(1)->getSearchResults()->getCount(), 'There should be 2 documents in the group news');

        $this->assertSame(7, $searchResultSet->getSearchResults()->getCount(), 'There should be a 7 search results when they are fetched without groups');
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
