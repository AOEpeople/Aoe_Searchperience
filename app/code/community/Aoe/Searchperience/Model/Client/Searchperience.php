<?php

$path = dirname( __FILE__ ).'/../../../../../../../vendor/autoload.php';
require $path;


class Aoe_Searchperience_Model_Client_Searchperience extends Apache_Solr_Service
{
    /**
     * Product data
     *
     * @var array
     */
    private $_productData = array();

    /**
     * Searchperience API customer key
     *
     * @var string
     */
    private $_customerKey;

    /**
     * Searchperience API username
     *
     * @var string
     */
    private $_username;

    /**
     * Searchperience API password
     *
     * @var string
     */
    private $_password;

    /**
     * Searchperience API base URL
     *
     * @var string
     */
    private $_baseUrl;

    /**
     * Searchperience API document source
     *
     * @var string
     */
    private $_documentSource;

    /**
     * @var Searchperience\Api\Client\Domain\DocumentRepository
     */
    private $_documentRepository;

    public static $statistics = array();

	/**
	 * @param array $options
	 */
	public  function __construct($options)
    {
        // fetching settings from magento backend
        $this->_customerKey    = Mage::getStoreConfig('searchperience/searchperience/customer_key', 'default');
        $this->_username       = Mage::getStoreConfig('searchperience/searchperience/username',     'default');
        $this->_password       = Mage::getStoreConfig('searchperience/searchperience/password',     'default');
        $this->_baseUrl        = Mage::getStoreConfig('searchperience/searchperience/api',          'default');
        $this->_documentSource = Mage::getStoreConfig('searchperience/searchperience/source',       'default');

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
     * @param array $rawQueries Expected to be utf-8 encoded
     * @param boolean $fromPending
     * @param boolean $fromCommitted
     * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
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
	 * @param float $timeout Maximum expected duration (in seconds) of the commit operation on the server (otherwise, will throw a communication exception). Defaults to 1 hour
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
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
	 * @return
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function addDocuments($documentList, $allowDups = false, $overwritePending = true, $overwriteCommitted = true)
	{
        if (in_array(null, array($this->_customerKey, $this->_username, $this->_password, $this->_documentSource, $this->_baseUrl))) {
            Mage::getSingleton('core/session')->addError(
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
            //$document->setMimeType('application/searchperienceproduct+xml');
            $document->setMimeType('text/xml');
            $document->setUrl($this->_getValueFromArray('url', $productData));

            try {

                $result = $this->_documentRepository->add($document);
                if (!isset(self::$statistics[$result])) {
                    self::$statistics[$result] = 0;
                }
                self::$statistics[$result]++;
                Mage::log('Searchperience API log result: ' . $result);
            } catch (Exception $e) {
                Mage::log(sprintf('Errors occured while trying to add document to repository: %s', $e->getMessage()));
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('core')->__(
                        sprintf('Errors occured while trying to add document to repository: %s', $e->getMessage())
                    )
                );
            }
            unset($document, $rawDocument, $documentList[$index]);
        }
        unset($documentList);
	}

    /**
     * Create an XML fragment from a {@link Apache_Solr_Document} instance appropriate for use inside a Solr add call
     *
     * @return string
     */
    protected function _documentToXmlFragment(Apache_Solr_Document $document)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startElement('product');
        $documentFields = array(
            'sku'               => 'sku',
            'title'             => 'name',
            'description'       => 'description',
            'short_description' => 'short_description',
            'price'             => 'price',
            'special_price'     => 'special_price',
            'group_price'       => 'group_price'
        );
        $documentData = $document->getData();
        $productData  = ((isset($documentData['productData']) ? $documentData['productData'] : array()));
        $productId    = $this->_getValueFromArray('id', $productData);

        // fetch some default data
        $writer->writeElement('id', $productId);
        $writer->writeElement('storeid', $this->_getValueFromArray('storeid', $documentData));
        $writer->writeElement('language', $this->_getValueFromArray('language', $documentData));
        $writer->writeElement('availability', $this->_getValueFromArray('in_stock', $documentData));

        // add product data to xml
        foreach ($documentFields as $elementName => $productDataName) {
            $writer->writeElement($elementName, $this->_getValueFromArray($productDataName, $productData));
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
            $writer->writeAttribute('type', '');
            $writer->writeAttribute('forsorting', 0);
            $writer->writeAttribute('forfiltering', 0);
            $writer->writeAttribute('forsearching', 0);
            $writer->text($value);
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

        // replace any control characters to avoid Solr XML parser exception
        $return = $this->_stripCtrlChars($writer->outputMemory(true));

        //Mage::log($return);

        return $return;
    }

    /**
     * Used for extracting values from arrays
     *
     * @param $key      Key of data to extract
     * @param $array    Array with data to extract from
     * @param $default  Default return value if key not found in array
     */
    private function _getValueFromArray($key, array $array, $default = '')
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }
}