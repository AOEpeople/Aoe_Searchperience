<?php

require Mage::getBaseDir('lib') . DS . 'searchperience' . DS . 'autoload.php';

class Aoe_Searchperience_Model_Client_Searchperience extends Apache_Solr_Service
{
    /**
     * Searchperience API customer key
     *
     * @var string
     */
    protected $_customerKey;

    /**
     * Searchperience API username
     *
     * @var string
     */
    protected $_username;

    /**
     * Searchperience API password
     *
     * @var string
     */
    protected $_password;

    /**
     * Searchperience API base URL
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * Searchperience API document source
     *
     * @var string
     */
    protected $_documentSource;

    /**
     * @var Searchperience\Api\Client\Domain\DocumentRepository
     */
    protected $documentRepository;

    /**
     * Some statistics about transactions with API
     *
     * @var array
     */
    public static $statistics = array();

    /**
     * @param array $options
     */
    public  function __construct($options)
    {
        // fetching settings from magento backend
        $this->_customerKey    = Mage::getStoreConfig('searchperience/searchperience/customer_key');
        $this->_username       = Mage::getStoreConfig('searchperience/searchperience/username');
        $this->_password       = Mage::getStoreConfig('searchperience/searchperience/password');
        $this->_baseUrl        = Mage::getStoreConfig('searchperience/searchperience/api');
        $this->_documentSource = Mage::getStoreConfig('searchperience/searchperience/source');

        //\Searchperience\Common\Factory::$HTTP_DEBUG = true;
        $this->documentRepository = \Searchperience\Common\Factory::getDocumentRepository(
            $this->_baseUrl,
            $this->_customerKey,
            $this->_username,
            $this->_password
        );

        return $this;
    }

    /**
     * Create a delete document based on a multiple queries and submit it
     *
     * @param array $rawQueries Expected to be utf-8 encoded
     * @param boolean $fromPending
     * @param boolean $fromCommitted
     * @param int $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     */
    public function deleteByQueries($rawQueries, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        return true;
    }

    /*
    * @param float $timeout maximum time to wait for ping in seconds, -1 for unlimited (default is 2)
    * @return float Actual time taken to ping the server, FALSE if timeout or HTTP error status occurs
    */
    public function ping($timeout = 2)
    {
        return 0.1;
    }

    /**
     * Send a commit command.  Will be synchronous unless both wait parameters are set to false.
     *
     * @param boolean $optimize Defaults to true
     * @param boolean $waitFlush Defaults to true
     * @param boolean $waitSearcher Defaults to true
     * @param int $timeout Maximum expected duration (in seconds) of the commit operation on the server (otherwise, will throw a communication exception). Defaults to 1 hour
     * @return Apache_Solr_Response
     */
    public function commit($optimize = true, $waitFlush = true, $waitSearcher = true, $timeout = 3600)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        return true;
    }

    /**
     * Add an array of Solr Documents to the index all at once
     *
     * @param array $documentList
     * @param boolean $allowDups
     * @param boolean $overwritePending
     * @param boolean $overwriteCommitted
     * @return void|false
     */
    public function addDocuments($documentList, $allowDups = false, $overwritePending = true, $overwriteCommitted = true)
    {
        if (in_array(null, array($this->_customerKey, $this->_username, $this->_password, $this->_documentSource, $this->_baseUrl))) {
            Mage::helper('aoe_searchperience')->addError(
                Mage::helper('core')->__('No valid connection settings for searchperience connection found!')
            );
            return false;
        }
        foreach ($documentList as $index => $rawDocument) {
            $documentData = $rawDocument->getData();
            $productData  = ((isset($documentData['productData']) ? $documentData['productData'] : array()));
            $document     = new \Searchperience\Api\Client\Domain\Document();

            $document->setContent($this->_documentToXmlFragment($rawDocument));
            $document->setForeignId($this->_getValueFromArray('unique', $productData));
            $document->setSource($this->_documentSource);
            $document->setMimeType('text/xml');
            $document->setUrl($this->_getValueFromArray('url', $productData));

            try {
                $start = microtime(true);
                $result = $this->documentRepository->add($document);
                $duration = round((microtime(true) - $start), 3); // in seconds
                if (!isset(self::$statistics[$result])) {
                    self::$statistics[$result] = 0;
                }
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
                Mage::helper('aoe_searchperience')->addError(
                    Mage::helper('core')->__(
                        sprintf('[Aoe_Searchperience] Errors occurred while trying to add document to repository: %s', $message)
                    )
                );
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log('[ERROR] ' . $document->getUrl(), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
                    Mage::log($message, Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            }
            unset($document, $rawDocument, $documentList[$index]);
        }
        unset($documentList);
    }

    /**
     * Returns API document repository
     *
     * @return Searchperience\Api\Client\Domain\DocumentRepository
     */
    public function getDocumentRepository()
    {
        return $this->documentRepository;
    }

    /**
     * Create an XML fragment from a {@link Apache_Solr_Document} instance appropriate for use inside a Solr add call
     *
     * @param Apache_Solr_Document $document
     * @return string
     */
    protected function _documentToXmlFragment(Apache_Solr_Document $document)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString("\t");
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('product');

        $documentData = $document->getData();
        $productData  = ((isset($documentData['productData']) ? $documentData['productData'] : array()));
        $productId    = $this->_getValueFromArray('id', $productData);

        // fetch some default data
        $writer->writeElement('id', $productId);
        $writer->writeElement('storeid', $this->_getValueFromArray('storeid', $documentData));
        $writer->writeElement('language', $this->_getValueFromArray('language', $documentData));
        $writer->writeElement('availability', $this->_getValueFromArray('in_stock', $documentData));

        $documentFields = array(
            'sku'               => 'sku',
            'title'             => 'name',
            'description'       => 'description',
            'short_description' => 'short_description',
            'price'             => 'price',
            'special_price'     => 'special_price',
            'group_price'       => 'group_price',
            'rating'            => 'rating',
            'content'           => 'content',
        );

        /**
         * Add additional fields to the document.
         * Example (e.g. in the aoe_searchperience_prepareIndexProductData_after event)
         *
         * $documentData['additionalDocumentFields'] = array('priority' => 'priority'); // add field to the list
         * $documentData['productData']['priority'] = 'high'; // add value for the field to the product data
         */
        if (isset($documentData['additionalDocumentFields']) && is_array($documentData['additionalDocumentFields'])) {
            $documentFields = array_merge($documentFields, $documentData['additionalDocumentFields']);
        }

        // add product data to xml
        foreach ($documentFields as $elementName => $productDataName) {
            $writer->startElement($elementName);
            $writer->writeCData($this->_getValueFromArray($productDataName, $productData));
            if (isset($documentData['attributesUsedForSearching'][$elementName])) {
                $writer->writeAttribute('forsearching', $documentData['attributesUsedForSearching'][$elementName]);
            }
            $writer->endElement();
        }

        // add category information to xml
        $categoryInformation = $this->_getValueFromArray('categories', $documentData, array());
        foreach ($categoryInformation as $categoryId => $category) {
            $writer->writeElement('category_path', $this->_getValueFromArray('path', $category));
            $writer->writeElement('category_id', $categoryId);
        }

        // add image information to xml
        $images = $this->_getValueFromArray('images', $productData, array());
        $writer->writeElement('image_link', $this->_getValueFromArray('small_image', $images));

        // dynamic fields
        $additionalData = $this->_getValueFromArray('additionalData', $productData, array());
        foreach ($additionalData as $key => $value) {
            $writer->startElement('attribute');
            $writer->writeAttribute('name', $key);
            $writer->writeAttribute('type', $this->_getValueFromArray($key, $documentData['attributeTypes'], 'string') );
            if (isset($documentData['attributesUsedForSorting'][$key])) {
                $writer->writeAttribute('forsorting', $documentData['attributesUsedForSorting'][$key]);
            }
            if (isset($documentData['attributesUsedForFiltering'][$key])) {
                $writer->writeAttribute('forfaceting', $documentData['attributesUsedForFiltering'][$key]);
            }
            if (isset($documentData['attributesUsedForSearching'][$key])) {
                $writer->writeAttribute('forsearching', $documentData['attributesUsedForSearching'][$key]);
            }
//            if (isset($documentData['attributeTypes'][$key]) && ($documentData['attributeTypes'][$key] != 'date')) {
//                $writer->writeAttribute('forsearching', 1);
//            }

            if (!is_array($value)) {
                $value = (array)$value;
            }
            $value = array_unique($value);
            foreach ($value as $key => $attributeValue) {
                if ($attributeValue) {
                    $writer->writeElement('value', $attributeValue);
                }
            }

            $writer->endElement();
        }

        // add related, upsell and crosssell information
        $relatedInformation = array(
            'related' => 'related_product',
            'upsell'  => 'upsell',
            'cross'   => 'crosssell',
        );

        foreach ($relatedInformation as $key => $elementName) {
            $assigned = $this->_getValueFromArray($key, $productData, array());
            foreach ($assigned as $index => $productId) {
                $writer->writeElement($elementName, $productId);
            }
        }

        // end product node
        $writer->endElement();
        $writer->endDocument();

        // replace any control characters to avoid Solr XML parser exception
        $return = $this->_stripCtrlChars($writer->outputMemory(true));

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled() && Mage::helper('aoe_searchperience')->isLogFullDocumentsEnabled()) {
            Mage::log('Generated XML Document: ' . $return, Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }

        return $return;
    }

    /**
     * Used for extracting values from arrays
     *
     * @param string $key      Key of data to extract
     * @param array $array    Array with data to extract from
     * @param string $default  Default return value if key not found in array
     * @return string|array
     */
    private function _getValueFromArray($key, array $array, $default = '')
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }
}
