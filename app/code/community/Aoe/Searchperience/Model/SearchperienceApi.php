<?php

require Mage::getBaseDir('lib') . DS . 'searchperience' . DS . 'autoload.php';

/**
 * Searchperience API
 *
 * @author Fabrizio Branca
 * @since 2015-06-17
 */
class Aoe_Searchperience_Model_SearchperienceApi {

    /**
     * @var Searchperience\Api\Client\Domain\DocumentRepository
     */
    protected $documentRepository;

    /**
     * @var array Some statistics about transactions with API
     */
    public static $statistics = array();

    /**
     * \Searchperience\Api\Client\Domain\DocumentRepository
     */
    public function getDocumentRepository()
    {
        if (is_null($this->documentRepository)) {
            $customerKey = Mage::getStoreConfig('searchperience/searchperience/customer_key');
            $username = Mage::getStoreConfig('searchperience/searchperience/username');
            $password = Mage::getStoreConfig('searchperience/searchperience/password');
            $baseUrl = Mage::getStoreConfig('searchperience/searchperience/api');

            if (in_array(null, array($customerKey, $username, $password, $baseUrl))) {
                Mage::helper('aoe_searchperience')->addError(Mage::helper('core')->__('No valid connection settings for searchperience connection found!'));
                Mage::throwException('Invalid Aoe_Searchperience configuration');
            }

            //\Searchperience\Common\Factory::$HTTP_DEBUG = true;
            $this->documentRepository = \Searchperience\Common\Factory::getDocumentRepository(
                $baseUrl,
                $customerKey,
                $username,
                $password
            );
        }

        return $this->documentRepository;
    }

    /**
     * Add raw document
     *
     * @param $content
     * @param $id
     * @param $url
     * @param string $sourceIdentifier
     * @param string $mimeType
     * @return int HTTP status code
     */
    public function addDocument($content, $id, $url, $sourceIdentifier='magento', $mimeType='text/xml') {
        try {

            if (empty($content)) { throw new InvalidArgumentException('No content found'); }
            if (empty($id)) { throw new InvalidArgumentException('No id found'); }
            if (empty($url)) { throw new InvalidArgumentException('No url found'); }
            if (empty($sourceIdentifier)) { throw new InvalidArgumentException('No source identified found'); }

            if (Mage::helper('aoe_searchperience')->isLoggingEnabled() && Mage::helper('aoe_searchperience')->isLogFullDocumentsEnabled()) {
                Mage::log(
                    "Generated XML Document (id: $id, source: $sourceIdentifier, url: $url): \n" . $content,
                    Zend_Log::DEBUG,
                    Aoe_Searchperience_Helper_Data::LOGFILE
                );
            }

            // create searchperience document
            $document = new \Searchperience\Api\Client\Domain\Document();

            $document->setContent($content);
            $document->setForeignId($id);
            $document->setSource($sourceIdentifier);
            $document->setMimeType($mimeType);
            $document->setUrl($url);

            // transfer document

            $start = microtime(true);

            // this is where the magic happens
            $result = $this->getDocumentRepository()->add($document);

            $duration = round((microtime(true) - $start), 3); // in seconds

            if (!isset(self::$statistics[$result])) { self::$statistics[$result] = 0; }
            self::$statistics[$result]++;

            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                if ($result == 201) {
                    $status = '[NEW]';
                } elseif ($result == 200) {
                    $status = '[UPDATED]';
                } else {
                    $status = '[STATUS: '.$result.']';
                }
                Mage::log($status .' ' . $document->getUrl() . ' ('.$duration.' sec)', Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $message = $e->getMessage();
            if (strlen($message) > 200) {
                $message = substr($message, 0, 200) . ' ... (find full message in var/log/exception.log)';
            }
            Mage::helper('aoe_searchperience')->addError(Mage::helper('core')->__(sprintf('[Aoe_Searchperience] Errors occurred while trying to add document to repository: %s', $message)));
            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log('[ERROR] ' . $document->getUrl(), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
                Mage::log($message, Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
            }
        }

        return $result;
    }

    /**
     * Delete all products
     *
     * @return int
     */
    public function deleteAllProducts() {
        $source = Mage::getStoreConfig('searchperience/searchperience/source');
        return $this->deleteBySource($source);
    }

    /**
     * Delete all documents by source
     *
     * @param $source
     * @return int
     */
    public function deleteBySource($source) {
        $statusCode = null;
        try {
            $statusCode = $this->getDocumentRepository()->deleteBySource($source);
            if ($statusCode == 200) {
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Successfully deleted all documents from repository for source "%s"', $source), Zend_Log::INFO, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            } else {
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Error while deleting all documents from repository for source "%s" (Status Code: "%s")', $source, $statusCode), Zend_Log::ERR, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log(sprintf('Error while deleting all documents from repository for source "%s" (Message: "%s")', $source, $e->getMessage()), Zend_Log::ERR, Aoe_Searchperience_Helper_Data::LOGFILE);
            }
        }
        return $statusCode;
    }

    /**
     * Delete by document id
     *
     * @param array|int $docIDs
     */
    public function deleteById($docIDs) {
        if (!is_array($docIDs)) {
            $docIDs = array($docIDs);
        }
        foreach ($docIDs as $docId) {
            try {
                $this->getDocumentRepository()->deleteByForeignId($docId);
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Successfully deleted document with foreign id %s from repository', $docId), Zend_Log::INFO, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            } catch (Searchperience\Common\Http\Exception\DocumentNotFoundException $e) {
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Document with foreign id %s not found', $docId), Zend_Log::INFO, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Error while deleting document with foreign id %s from repository', $docId), Zend_Log::ERR, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            }
        }
    }

    /**
     * Get document info
     *
     * @param $productId
     * @param $storeId
     * @return array
     */
    public function getDocumentInfo($productId, $storeId) {
        $document = $this->getDocumentRepository()->getByForeignId(Mage::helper('aoe_searchperience')->getProductUniqueId($productId, $storeId));
        return array(
            'LastProcessing'        => $document->getLastProcessing(),
            'BoostFactor'           => $document->getBoostFactor(),
            'Id'                    => $document->getId(),
            'IsMarkedForProcessing' => $document->getIsMarkedForProcessing(),
            'IsMarkedForDeletion'   => $document->getIsMarkedForDeletion(),
            'IsProminent'           => $document->getIsProminent(),
            'NoIndex'               => $document->getNoIndex(),
            'Content'               => $document->getContent(),
            'ForeignId'             => $document->getForeignId(),
            'GeneralPriority'       => $document->getGeneralPriority(),
            'MimeType'              => $document->getMimeType(),
            'Source'                => $document->getSource(),
            'TemporaryPriority'     => $document->getTemporaryPriority(),
            'Url'                   => $document->getUrl(),
        );
    }
}
