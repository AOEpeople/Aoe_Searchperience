Aoe_Searchperience
==================

Module integrating Searchperience (Solr SaaS solution from AOE Media) with Magento

Core modifications:
=======================
see attached patch file

Indexing of Attributes:
=======================

Attributes can be used for searching, filtering or sorting.

The Magento attributes settings are used as follows for the export to Searchperience:

TODO:

   is_global: 1
                   is_visible: 1
                is_searchable: 1
                search_weight: 1
                is_filterable: 0
                is_comparable: 0
          is_visible_on_front: 0
     is_html_allowed_on_front: 1
      is_used_for_price_rules: 0
      is_filterable_in_search: 1
      used_in_product_listing: 1
             used_for_sort_by: 1
              is_configurable: 1
                     apply_to: NULL
is_visible_in_advanced_search: 1
                     position: 0
           is_wysiwyg_enabled: 0
      is_used_for_promo_rules: 0