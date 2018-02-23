.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. raw:: latex

    \newpage

.. raw:: pdf

   PageBreak


.. _conf-tx-solr-general:

tx_solr.search.grouping
=======================

This section defines all available settings for grouping.

.. contents::
    :local:


grouping.numberOfGroups
-----------------------

:Type: Integer
:TS Path: plugin.tx_solr.search.grouping.numberOfGroups
:Default: 5
:Since: 1.0

grouping.numberOfResultsPerGroup
--------------------------------

:Type: Integer
:TS Path: plugin.tx_solr.search.grouping.numberOfResultsPerGroup
:Default: 5
:Since: 1.0


grouping.allowGetParameterSwitch
--------------------------------

:Type: Boolean
:TS Path: plugin.tx_solr.search.grouping.allowGetParameterSwitch
:Default: 0
:Since: 1.0


grouping.groups.[groupName].field
---------------------------------

:Type: String
:TS Path: plugin.tx_solr.search.grouping.[groupName].field
:Default: empty
:Since: 1.0

Defines the solr field where a group should be build on.

Note: Use either field or queries no mix. Groups with field are field groups, groups with queries are query groups.

grouping.groups.[groupName].queries
-----------------------------------

:Type: Array
:TS Path: plugin.tx_solr.search.grouping.[groupName].queries
:Default: empty
:Since: 1.0

Defines an array of queries to group the results in.

Note: Use either field or queries no mix. Groups with field are field groups, groups with queries are query groups.
