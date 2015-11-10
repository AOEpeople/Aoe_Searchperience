<?php

/**
 * Class Bs_Base_Model_Changelog
 *
 * @author Fabrizio Branca
 * @since 2013-11-26
 */
class Aoe_Searchperience_Model_Changelog extends Enterprise_Index_Model_Changelog
{
    /**
     * @var int
     */
    protected $lastProcessedVersionId;

    /**
     * Load changelog by metadata
     *
     * @param null|int $currentVersion only fetch until current version
     * @return array
     */
    public function loadByMetadata($currentVersion = null)
    {
        $keyColumn = $this->_metadata->getKeyColumn();

        $select = $this->_connection->select()
            ->distinct()
            ->from(['changelog' => $this->_metadata->getChangelogName()], [$keyColumn, 'MAX(version_id) as version_id'])
            ->group($keyColumn)
            ->where('version_id > ?', $this->_metadata->getVersionId())
            ->order('version_id ASC')
            ->limit($this->getLimit());
            // ->columns(array('version_id', $keyColumn))

        if ($currentVersion) {
            $select->where('version_id < ?', $currentVersion);
        }

        $res = $this->_connection->fetchAssoc($select);

        $ids = [];
        $versionId = 0;
        foreach ($res as $row) {
            $value = $row[$keyColumn];
            if (!in_array($value, $ids)) {
                $ids[] = $value;
            }
            $versionId = max($versionId, $row['version_id']);
        }
        $this->lastProcessedVersionId = $versionId;

        $this->reindexUrlKeys($ids);

        return $ids;
    }

    /**
     * @param array| int $productIds reindex these ids
     * @throws Enterprise_Mview_Exception
     * @return void
     */
    public function reindexUrlKeys($productIds)
    {
        /** @var $client Enterprise_Mview_Model_Client */
        $client = Mage::getSingleton('core/factory')->getModel('enterprise_mview/client')->init('enterprise_url_rewrite_product');
        foreach ($productIds as $productId) {
            $client->execute('aoe_searchperience/index_producturl', ['product_id' => $productId]);
        }
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return Mage::getStoreConfig('searchperience/searchperience/changelogIndexBatchSize');
    }

    /**
     * @return int
     */
    public function getLastProcessedVersionId()
    {
        return $this->lastProcessedVersionId;
    }
}
