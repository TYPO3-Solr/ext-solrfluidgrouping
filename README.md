Apache Solr for TYPO3 - Grouping for fluid templating
============================================================


[![Build Status](https://travis-ci.com/TYPO3-Solr/ext-solrfluidgrouping.svg?branch=master)](https://travis-ci.com/TYPO3-Solr/ext-solrfluidgrouping)

About
-----

This addon provides grouping for the fluid templating in EXT:solr.

How to run the UnitTests
------------------------

First you need to set some environment variables and boostrap the system with the bootstrap script (you only need to do this once),
the bootstrapper will ask you for some variables (TYPO3 version, EXT:solr version, db host, db user and db passwort) that are needed
for the integration tests:

```bash
chmod u+x ./Build/Test/*.sh
source ./Build/Test/bootstrap.sh --local
```

Now you can run the complete test suite:

```bash
./Build/Test/cibuild.sh
```

