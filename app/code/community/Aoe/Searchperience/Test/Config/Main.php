<?php

class Aoe_Searchperience_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{

    public function testClassAliases()
    {
        $this->assertModelAlias('aoe_searchperience/observer', 'Aoe_Searchperience_Model_Observer');
        $this->assertModelAlias('aoe_searchperience/client_searchperience', 'Aoe_Searchperience_Model_Client_Searchperience');
        $this->assertResourceModelAlias('aoe_searchperience/engine', 'Aoe_Searchperience_Model_Resource_Engine');
        $this->assertHelperAlias('aoe_searchperience', 'Aoe_Searchperience_Helper_Data');
        $this->assertBlockAlias('aoe_searchperience/adminhtml_system_config_fieldset_info', 'Aoe_Searchperience_Block_Adminhtml_System_Config_Fieldset_Info');
    }

    public function testRewritesAliases()
    {
        $this->assertModelAlias('enterprise_search/adminhtml_system_config_source_engine', 'Aoe_Searchperience_Model_Adminhtml_System_Config_Source_Engine');
        $this->assertResourceModelAlias('catalogsearch/fulltext', 'Aoe_Searchperience_Model_Resource_Fulltext');
    }
}
