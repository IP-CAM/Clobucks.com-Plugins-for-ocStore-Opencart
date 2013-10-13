<?php

require DIR_SYSTEM . 'library/IXR_Library.php';

class ModelClobucksClobucksShop extends Model
{
    /**
     * @var IXR_Client
     */
    private $client;

    /**
     * All API methods should be authenticated ;)
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if (strpos($method, 'api') === 0) {
            $this->ensureAuth();

            return call_user_func_array([$this, str_replace('api', '', $method)], $arguments);
        }

        return;
    }

    /**
     * @param string $login
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function auth($login, $password, $hash)
    {
        $this->client = new IXR_Client('http://api.clobucks.com/api1.0/');
        $this->client->query(API::method('auth'), $login, $password, $hash);

        return !$this->client->isError();
    }

    /**
     * @return null|array
     */
    public function getSuppliers()
    {
        if (!($raw_suppliers = $this->apiCall([API::method('suppliersList')]))) {
            return null;
        }

        $obSuppliers = simplexml_load_string($raw_suppliers)->suppliers;

        $suppliers = [];
        foreach ($obSuppliers->supplier as $raw_supplier) {

            $supplier = $raw_supplier;
            $suppliers[] = [
                'id' => (string) $supplier->attributes()->id,
                'description' => (string) $supplier->description,
                'products_amount' => (string) $supplier->products_amount,
            ];
        }

        return $suppliers;
    }

    private function replace_unicode_escape_sequence($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }

    /**
     * @param string $supplier Supplier id
     * @return null|array
     */
    public function getSupplierCategories($supplier)
    {
        if (!($raw_categories = $this->apiCall([API::method('supplierCategories'), $supplier]))) {
            return null;
        }
        $settings = $this->model_setting_setting->getSetting('ClobucksShopModel');
        if(empty($settings['category'])) $arChecked = array();
        else $arChecked = unserialize($settings['category']);
         
        $categories = [];
        // Why categories in $xml->categories->category ? (facepalm)
        foreach (simplexml_load_string($raw_categories)->categories->category as $category) {
            $categories[] = [
                'id' => (string) $category->attributes()->id,
                'title' => (string) $category->title,
                'parent' => (string) $category->parent,
                'checked' => (bool) in_array($category->attributes()->id,$arChecked) ? true : false,
            ];                       
        }

        return $categories;
    }

    public function getProductsByCategory($supplier, $arCategory)
    {
    	//For testing
    	//$category = 7;
    	//$supplier = '505998ea5caf25a003000000';
        
        if(!is_array($arCategory) || !count($arCategory)) return null;
         
        //$this->load->model('clobucks/Import');              
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('ClobucksShopModel', ['category'=>serialize($arCategory)]);

        $products = [];
        foreach($arCategory as $category) {
        
            if($category == 32) $category = 7;
            if (!($raw_products = $this->apiCall([API::method('productsByCategory'), intval($category), $supplier]))) { 
                continue;   
            }           
                 
            $arProducts = simplexml_load_string($raw_products)->products;     
            if(is_object($arProducts) && count($arProducts)){                
	            foreach ($arProducts as $raw_product) {
	                $product = $raw_product->product;

	                $options = [];
	                if(count($product->options)) {
		                foreach ($product->options as $option) {
		                    $options[] = [
		                        'title' => (string) $option->option->title,
		                        'amount' => (int) $option->option->amount,
		                    ];
		                }
					}

	                $images = [];
	                if(count($product->images)) {
		                foreach ($product->images as $image) {
		                    $images[] = (string) $image->image;
		                }
					}

					
					
						
					if(count($product)) {
		                $products[] = [
		                    'id' => (string) $product->attributes()->id,
		                    'title' => (string) $product->title,
		                    'sku' => (int) $product->sku,
		                    'category' => (int) $product->category,
		                    'description' => (string) $product->description,
		                    'price' => (int) $product->price,
		                    'options' => $options,
		                    'images' => $images,
		                ];
					}
	            }
			}            
        }
        
        return count($products) ? $products : null;
    }
    
    public function getCategoryInfo($supplier,$categoryId) {
        if(!($categoryInfo = $this->apiCall([API::method('categoryInfo'), $categoryId, $supplier]))) {
            return false;        
        }   	
        
        return $categoryInfo;
    }
    
    public function getOrderStatuses() {
    	if(!($statuses = $this->apiCall([API::method('orderStatuses')]))) {
			return false;
    	}
                              
    	
    	return simplexml_load_string($statuses,'SimpleXMLElement', LIBXML_NOCDATA);    
    }
    
    public function getDelivery($supplier) {
        if(!($delivery = $this->apiCall([API::method('delivery'), (string)$supplier]))) {
            return false;        
        }       

        return simplexml_load_string($delivery,'SimpleXMLElement', LIBXML_NOCDATA);    
    } 
    
    public function sync($orderId,$ocOrderId) {

        if(!($xmlStatus = $this->apiCall([API::method('orderStatus'), $orderId]))) {
            return false;        
        }
	
        $status = simplexml_load_string($xmlStatus,'SimpleXMLElement', LIBXML_NOCDATA);
        $state = (int)$status->state;  

        $this->db->query("UPDATE ". DB_PREFIX ."order SET order_status_id = '" . $state . "' WHERE order_id = '" . $ocOrderId . "'");	
    }
    
    /**
     * @return self
     * @throws Exception
     */
    private function ensureAuth()
    {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('ClobucksShop');

        if (!$this->auth($settings['login'], $settings['password'], $settings['hash'])) {
            throw new Exception('You must be authenticated.');
        }

        return $this;
    }

    /**
     * @param array $args
     * @return null|string Response as XML string or nothing
     */
    private function apiCall(array $args) {
        call_user_func_array([$this->client, "query"], $args);       
        return $this->client->error ? null : $this->client->getResponse();
    }
}

/**
 * Wrapper for Clobucks API.
 */
class API
{
    /**
     * @var array Matching Clobucks API names and aliases
     */
    static private $methods = [
        'auth' 					=> 'cb.auth',
        'suppliersList' 		=> 'cb.getSuppliers',
        'supplierCategories' 	=> 'cb.getCategories',
        'categoryInfo' 			=> 'cb.getCategory',
        'productsByCategory' 	=> 'cb.getProductsByCategory',
        'orderStatuses'			=> 'cb.getOrderStatuses',
        'delivery'              => 'cb.getDeliveries',
        'orderStatus'			=> 'cb.getOrderStatus',
    ];

    /**
     * @param string $alias Clobucks API method alias
     * @return string Clobucks API method
     * @throws Exception
     */
    static public function method($alias)
    { 
        if (!array_key_exists($alias, self::$methods)) {
            throw new Exception(sprintf('Route %s is not found.', $alias));
        }

        return self::$methods[$alias];
    }
}
