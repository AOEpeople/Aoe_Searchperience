<?php

/**
 * Class Aoe_Searchperience_Test_Config_Main
 * Main config test class
 *
 * @category Test
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * Test class aliases
     *
     * @test
     *
     * @return void
     */
    public function testClassAliases()
    {
        $this->assertModelAlias('aoe_searchperience/observer', 'Aoe_Searchperience_Model_Observer');
        $this->assertModelAlias('aoe_searchperience/client_searchperience',
            'Aoe_Searchperience_Model_Client_Searchperience');
        $this->assertResourceModelAlias('aoe_searchperience/engine', 'Aoe_Searchperience_Model_Resource_Engine');
        $this->assertHelperAlias('aoe_searchperience', 'Aoe_Searchperience_Helper_Data');
        $this->assertBlockAlias('aoe_searchperience/adminhtml_system_config_fieldset_info',
            'Aoe_Searchperience_Block_Adminhtml_System_Config_Fieldset_Info');
    }

    /**
     * Test rewrite aliases
     *
     * @test
     *
     * @return void
     */
    public function testRewritesAliases()
    {
        $this->assertModelAlias('enterprise_search/adminhtml_system_config_source_engine',
            'Aoe_Searchperience_Model_Adminhtml_System_Config_Source_Engine');
        $this->assertResourceModelAlias('catalogsearch/fulltext', 'Aoe_Searchperience_Model_Resource_Fulltext_Threadi');
        $this->assertModelAlias('enterprise_index/resource_lock_resource',
            'Aoe_Searchperience_Model_Resource_Lock_Resource');
    }

    /**
     * Test event observer definitions
     *
     * @test
     *
     * @return void
     */
    public function checkObserver()
    {
        $this->assertEventObserverDefined('global', 'catalogsearch_searchable_attributes_load_after', 'aoe_searchperience/observer', 'storeSearchableAttributes');
    }
}
