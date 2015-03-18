<?php

/**
 * Class Bs_Base_Model_Changelog
 *
 * @author Fabrizio Branca
 * @since 2013-11-26
 */
class Aoe_Searchperience_Model_Changelog extends Enterprise_Index_Model_Changelog {

    /**
     * @var int
     */
    protected $lastProcessedVersionId;

    /**
     * Load changelog by metadata
     *
     * @param null|int $currentVersion
     * @return array
     */
    public function loadByMetadata($currentVersion = null)
    {
        $keyColumn = $this->_metadata->getKeyColumn();

        $select = $this->_connection->select()
            ->from(array('changelog' => $this->_metadata->getChangelogName()), array())
            ->where('version_id > ?', $this->_metadata->getVersionId())
            ->order('version_id ASC')
            ->limit($this->getLimit())
            ->columns(array('version_id', $keyColumn));

        if ($currentVersion) {
            $select->where('version_id <= ?', $currentVersion);
        }

        $res = $this->_connection->fetchAssoc($select);

        $ids = array();
        $versionId = 0;
        foreach ($res as $row) {
            $value = $row[$keyColumn];
            if (!in_array($value, $ids)) {
                $ids[] = $value;
            }
            $versionId = max($versionId, $row['version_id']);
        }
        $this->lastProcessedVersionId = $versionId;

        return $ids;
    }

    public function getLimit() {
        return Mage::getStoreConfig('searchperience/searchperience/changelogIndexBatchSize');
    }

    public function getLastProcessedVersionId() {
        return $this->lastProcessedVersionId;
    }

}
