.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _releases-11-0:

============================================================
Apache Solr for TYPO3 - Grouping for Fluid templating 11.0.0
============================================================

Release 11.0.1
--------------

11.0.1 is a maintenance release for TYPO3 11.5 LTS.

..  attention::
    solrfluidgrouping is part of solr 12+, so there will be no release for TYPO3 12.

## What's Changed
* [BUGFIX] cast maxScore to float by @achimfritz in https://github.com/TYPO3-Solr/ext-solrfluidgrouping/pull/33
* [TASK] Increase RAMFS size by @dkd-friedrich
* [BUGFIX] Fix environment variable in GitHub workflow by @dkd-friedrich
* [BUGFIX] Adjust unit tests for EXT:solr 11.5.1+ by @dkd-friedrich
* [BUGFIX] allow empty groupValue and fallback to empty string as a group by @lukasniestroj in https://github.com/TYPO3-Solr/ext-solrfluidgrouping/pull/31

Please read the release notes:
https://github.com/TYPO3-Solr/ext-solrfluidgrouping/releases/tag/11.0.1


Release 11.0.0
--------------

We are happy to release EXT:solrfluidgrouping 11.0.0
The focus of this release has been on the EXT:solr 11.5 and TYPO3 11 LTS compatibility

As we're planning to simplify the maintenance, we want to merge EXT:solrfluidgrouping into EXT:solr.
So this will happen most probably in EXT:solr 12.0.0.

New in this release
-------------------

TYPO3 11 LTS and EXT:solr 11.5 compatibility
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

EXT:solrfluidgrouping is now compatible with TYPO3 11 LTS and EXT:solr 11.5

Small improvements and bugfixes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Beside the major changes we did several small improvements and bugfixes:

* [BUGFIX] Fix PSR-4 Namesppaces and Paths
* [TASK] Fix GitHub-Actions and TYPO3 coding standards
* [BUGFIX] Exception on argument mismatch
