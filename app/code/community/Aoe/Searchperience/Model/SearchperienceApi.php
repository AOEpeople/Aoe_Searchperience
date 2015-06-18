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

}
