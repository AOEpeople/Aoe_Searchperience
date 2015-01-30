<?php

// @codingStandardsIgnoreStart
require Mage::getBaseDir('lib') . DS . 'searchperience' . DS . 'autoload.php';
// @codingStandardsIgnoreEnd

/**
 * Class Aoe_Searchperience_Model_Client_Searchperience
 *
 * @category Model
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
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
    protected $_documentRepository;

    /**
     * Some statistics about transactions with API
     *
     * @var array
     */
    public static $statistics = array();

    /**
     * @param array $options Constructor options
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
        $this->_documentRepository = \Searchperience\Common\Factory::getDocumentRepository(
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
     * @param array   $rawQueries    Expected to be utf-8 encoded
     * @param boolean $fromPending   Delete from pending
     * @param boolean $fromCommitted Delete from committed
     * @param int     $timeout       Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     */
    public function deleteByQueries($rawQueries, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        return true;
    }

    /**
     * Ping the service
     *
     * @param float $timeout maximum time to wait for ping in seconds, -1 for unlimited (default is 2)
     * @return float Actual time taken to ping the server, FALSE if timeout or HTTP error status occurs
     */
    public function ping($timeout = 2.0)
    {
        return 0.1;
    }

    /**
     * Send a commit command.  Will be synchronous unless both wait parameters are set to false.
     *
     * @param boolean $optimize     Defaults to true
     * @param boolean $waitFlush    Defaults to true
     * @param boolean $waitSearcher Defaults to true
     * @param int     $timeout      Maximum expected duration (in seconds) of the commit operation on the server (otherwise, will throw a communication exception). Defaults to 1 hour
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
     * @param array   $documentList       Document list to add
     * @param boolean $allowDups          Allow duplicates
     * @param boolean $overwritePending   Overwrite pending documents
     * @param boolean $overwriteCommitted Overwrite committed documents
     * @return void|false
     */
    public function addDocuments($documentList, $allowDups = false, $overwritePending = true, $overwriteCommitted = true)
    {
        $helper = $this->_getSearchperienceHelper();
        if (in_array(null, array($this->_customerKey, $this->_username, $this->_password, $this->_documentSource, $this->_baseUrl))) {
            $helper->addError(
                Mage::helper('core')->__('No valid connection settings for searchperience connection found!')
            );
            return false;
        }
        foreach ($documentList as $index => $rawDocument) {
            /* @var Aoe_Searchperience_Model_Api_Document $rawDocument */

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
                $result = $this->_documentRepository->add($document);
                $duration = round((microtime(true) - $start), 3); // in seconds
                if (!isset(self::$statistics[$result])) {
                    self::$statistics[$result] = 0;
                }
                self::$statistics[$result]++;

                if ($helper->isLoggingEnabled()) {
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
                $helper->addError(
                    Mage::helper('core')->__(
                        sprintf('[Aoe_Searchperience] Errors occurred while trying to add document to repository: %s', $message)
                    )
                );
                if ($helper->isLoggingEnabled()) {
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
        return $this->_documentRepository;
    }

    /**
     * Create an XML fragment from a {@link Apache_Solr_Document} instance appropriate for use inside a Solr add call
     *
     * @param Apache_Solr_Document $document Document to convert to xml fragment
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

        // write default element
        $this->_writeXmlCdataElement($writer, 'id', $this->_getValueFromArray('id', $productData));
        $this->_writeXmlCdataElement($writer, 'storeid', $this->_getValueFromArray('storeid', $documentData));
        $this->_writeXmlCdataElement($writer, 'language', $this->_getValueFromArray('language', $documentData));
        $this->_writeXmlCdataElement($writer, 'availability', $this->_getValueFromArray('in_stock', $documentData));

        // write base product information
        $this->_addBaseProductDataToXmlFragment($productData, $documentData, $writer);

        // write category information
        $this->_addCategoryInformationToXmlFragment($documentData, $writer);

        // add image information to xml
        $this->_addImageInformationToXmlFragment($productData, $writer);

        // add dynamic fields
        $this->_addDynamicDataToXmlFragment($documentData, $productData, $writer);

        // add related, upsell and crosssell information
        $this->_addRelatedProductsToXmlFragment($productData, $writer);

        // end product node
        $writer->endElement();
        $writer->endDocument();

        // replace any control characters to avoid Solr XML parser exception
        $result = $this->_stripCtrlChars($writer->outputMemory(true));

        $helper = $this->_getSearchperienceHelper();
        if ($helper->isLoggingEnabled() && $helper->isLogFullDocumentsEnabled()) {
            Mage::log('Generated XML Document: ' . $result, Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }

        return $result;
    }

    /**
     * Add base document data to xml fragment using $writer
     *
     * @param array     $productData  Product data
     * @param array     $documentData Document data
     * @param XMLWriter $writer       XMLWriter used for data creation
     * @return void
     */
    protected function _addBaseProductDataToXmlFragment(array $productData, array $documentData, XMLWriter $writer)
    {
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
    }

    /**
     * Add category information to xml fragment using $writer
     *
     * @param array     $documentData Array of document data
     * @param XMLWriter $writer       XML writer
     * @return void
     */
    protected function _addCategoryInformationToXmlFragment(array $documentData, XMLWriter $writer)
    {
        $categoryInformation = $this->_getValueFromArray('categories', $documentData, array());
        foreach ($categoryInformation as $categoryId => $category) {
            $this->_writeXmlCdataElement($writer, 'category_path', $this->_getValueFromArray('path', $category));
            $this->_writeXmlCdataElement($writer, 'category_id', $categoryId);
        }
    }

    /**
     * Add image information to xml fragment using $writer
     *
     * @param array     $productData Product data
     * @param XMLWriter $writer      XML writer
     * @return void
     */
    protected function _addImageInformationToXmlFragment(array $productData, XMLWriter $writer)
    {
        $images = $this->_getValueFromArray('images', $productData, array());
        $this->_writeXmlCdataElement($writer, 'image_link', $this->_getValueFromArray('small_image', $images));
    }

    /**
     * Add dynamic data fields to xml fragment using $writer
     *
     * @param array     $documentData Document data
     * @param array     $productData  Product data
     * @param XMLWriter $writer       XML writer
     * @return void
     */
    protected function _addDynamicDataToXmlFragment(array $documentData, array $productData, XMLWriter $writer)
    {
        $additionalData = $this->_getValueFromArray('additionalData', $productData, array());
        foreach ($additionalData as $key => $value) {
            $writer->startElement('attribute');
            $writer->writeAttribute('name', $key);
            $writer->writeAttribute('type', $this->_getValueFromArray($key, $documentData['attributeTypes'], 'string'));
            if (isset($documentData['attributesUsedForSorting'][$key])) {
                $writer->writeAttribute('forsorting', $documentData['attributesUsedForSorting'][$key]);
            }
            if (isset($documentData['attributesUsedForFiltering'][$key])) {
                $writer->writeAttribute('forfaceting', $documentData['attributesUsedForFiltering'][$key]);
            }
            if (isset($documentData['attributesUsedForSearching'][$key])) {
                $writer->writeAttribute('forsearching', $documentData['attributesUsedForSearching'][$key]);
            }
//          if (isset($documentData['attributeTypes'][$key]) && ($documentData['attributeTypes'][$key] != 'date')) {
//              $writer->writeAttribute('forsearching', 1);
//          }

            if (!is_array($value)) {
                $value = (array) $value;
            }
            $value = array_unique($value);
            foreach ($value as $attributeValue) {
                if ($attributeValue) {
                    $this->_writeXmlCdataElement($writer, 'value', $attributeValue);
                }
            }

            $writer->endElement();
        }
    }

    /**
     * Add related products to xml fragment using $writer
     *
     * @param array     $productData Product data
     * @param XMLWriter $writer      XML Writer
     * @return void
     */
    protected function _addRelatedProductsToXmlFragment(array $productData, XMLWriter $writer)
    {
        $relatedInformation = array(
            'related' => 'related_product',
            'upsell'  => 'upsell',
            'cross'   => 'crosssell',
        );

        foreach ($relatedInformation as $key => $elementName) {
            $assigned = $this->_getValueFromArray($key, $productData, array());
            foreach ($assigned as $index => $productId) {
                $this->_writeXmlCdataElement($writer, $elementName, $productId);
            }
        }
    }

    /**
     * Used for extracting values from arrays
     *
     * @param string $key     Key of data to extract
     * @param array  $array   Array with data to extract from
     * @param string $default Default return value if key not found in array
     * @return string|array
     */
    private function _getValueFromArray($key, array $array, $default = '')
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }

    /**
     * Use $writer to write $elementName with CDATA encapsulation
     * @param XMLWriter $writer       XML writer
     * @param string    $elementName  XML tag name
     * @param string    $elementValue XML tag string content
     * @return void
     */
    protected function _writeXmlCdataElement(XMLWriter $writer, $elementName, $elementValue = null)
    {
        $writer->startElement($elementName);
        if (!is_null($elementValue)) {
            $writer->writeCdata($elementValue);
        }
        $writer->endElement();
    }

    /**
     * Get the modules helper
     *
     * @return Aoe_Searchperience_Helper_Data
     */
    protected function _getSearchperienceHelper()
    {
        return Mage::helper('aoe_searchperience');
    }
}
