Introduction
============

What does it do?
----------------

Solrfluidgrouping can be used to group documents based on a solr field or a set of solr queries.

The following example shows show to group documents based on the "type" field:

.. code-block:: typoscript

    plugin.tx_solr {
        search {
            grouping = 1
            grouping {
                numberOfGroups = 5
                numberOfResultsPerGroup = 5
                allowGetParameterSwitch = 0
                groups {
                    typeGroup {
                        field = type
                    }
                }
            }
        }
    }


The next example shows how to group documents based on queries:

.. code-block:: typoscript

    plugin.tx_solr {
        search {
            grouping = 1
            grouping {
                numberOfGroups = 5
                numberOfResultsPerGroup = 5
                allowGetParameterSwitch = 0
                groups {
                    pidQuery {
                        queries {
                            lessThenTen = pid:[0 TO 10]
                            lessThen30 = pid:[11 TO 30]
                            rest = pid:[30 TO *]
                        }
                    }
                }
            }
        }
    }
