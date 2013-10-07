<?
require DIR_SYSTEM . 'IXR_Library.php';

class ModelClobucksOrder extends Model
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
    
    public function apiSetOrder($obCustomer,$products, $ocOrderId) {
    	
        $customer = $obCustomer->session->data['guest'];
        $data['customer_info'] = array(
            'username' => $customer['firstname'],
            'email' => $customer['email'],
            'address' => $customer['firstname'],
            'telephone' => $customer['telephone'],
        );  
        
       	$data['comment'] = $obCustomer->session->data['comment'];
        
		$arShipCode = explode('.',$obCustomer->session->data['shipping_method']['code']);
		$shipId = $this->getRealDeliveryId(current($arShipCode));
		if(is_null($shipId)) $shipId = 13; //Хардкод для курьерки
		
        $data['delivery_id'] = $shipId;
        foreach($products as $product) {
        	$productId = $this->getRealProductId($product['product_id']);
        	if(is_null($productId)) continue;
        	
        	$optionId = '';
            $arProduct[] = array('product_id' => $productId,  'option_id' => $optionId, 'qty' => $product['quantity']);
        } 
        
        $settings = $this->model_setting_setting->getSetting('ClobucksShop');
        
        $data['products'] = $arProduct;
        $data['hash'] = $settings['hash'] ? $settings['hash'] : 0;
        
        $this->ensureAuth();
    	if(!($xmlResponse = $this->apiCall([API::method('order'),$data]))) {
			return false;
    	}     
    	$response = simplexml_load_string($xmlResponse);
    	
    	$orderId 	= (string)$response->order->orderid;
    	$price		= (float)$response->order->price;
    	
    	$this->relateOrder($ocOrderId,$orderId,$price);
    	
    }
    
    private function getRealDeliveryId($deliveryId) {
		$result = $this->db->query("SELECT `delivery_id` FROM ". DB_PREFIX ."clobucks_related_delivery
		WHERE `oc_delivery_code` = '" . $deliveryId . "'");

		return (count($result->row)) ? $result->row['delivery_id'] : null;
    }    
    
    private function getRealProductId($productId) {
		$result = $this->db->query("SELECT `product_id` FROM ". DB_PREFIX ."clobucks_related_id
		WHERE `oc_id` = '" . $productId . "'");

		return (count($result->row)) ? $result->row['product_id'] : null;
    }
    
    private function relateOrder($ocOrderId,$orderId,$price) {
		$this->checkTableRelatedOrder();

		$this->db->query("
			INSERT INTO ". DB_PREFIX ."clobucks_related_order
			SET
				order_id = '". $orderId ."',
				oc_order_id = '". $ocOrderId ."',
				price = '". $price ."' ");			
    }
 
    
    private function checkTableRelatedOrder() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS ". DB_PREFIX ."clobucks_related_order (
			  order_id varchar(255) NOT NULL,
			  oc_order_id int(11) NOT NULL,
			  price decimal(10, 2) DEFAULT NULL,
			  UNIQUE INDEX UK_clobucks_related_order_oc_id (oc_order_id),
			  UNIQUE INDEX UK_clobucks_related_order_product_id (order_id)
			)
			ENGINE = MYISAM;");
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
        'auth'				=> 'cb.auth',
        'order'				=> 'cb.setOrder',
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
