<?php

class Aoe_Searchperience_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{

    public function testClassAliases()
    {
        $this->assertModelAlias('aoe_searchperience/Observer', 'Aoe_Searchperience_Model_Observer');
        $this->assertModelAlias('aoe_searchperience/client_searchperience','Aoe_Searchperience_Model_Client_Searchperience');
        $this->assertResourceModelAlias('aoe_searchperience/engine','Aoe_Searchperience_Model_Resource_Engine');
        $this->assertHelperAlias('aoe_searchperience', 'Aoe_Searchperience_Helper_Data');
    }
}
