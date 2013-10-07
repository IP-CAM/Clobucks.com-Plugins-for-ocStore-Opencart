<?php

class ControllerModuleClobucksShop extends Controller
{
    /**
     * @var array
     */
    private $error = [];

    public function index()
    {
        $this->load->language('module/ClobucksShop');

        $this->load->model('clobucks/ClobucksShop');
        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        // TODO POST data validation.
        if ($this->isPostRequest()) {
            $this->model_setting_setting->editSetting('ClobucksShop', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('extension/module', 'token=' . $this->getToken(), 'SSL'));
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_content_top'] = $this->language->get('text_content_top');
        $this->data['text_content_bottom'] = $this->language->get('text_content_bottom');
        $this->data['text_column_left'] = $this->language->get('text_column_left');
        $this->data['text_column_right'] = $this->language->get('text_column_right');

        $this->data['entry_banner'] = $this->language->get('entry_banner');
        $this->data['entry_dimension'] = $this->language->get('entry_dimension');
        $this->data['entry_layout'] = $this->language->get('entry_layout');
        $this->data['entry_position'] = $this->language->get('entry_position');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['button_add_module'] = $this->language->get('button_add_module');
        $this->data['button_remove'] = $this->language->get('button_remove');

        $this->data['trans']['login'] = $this->language->get('login');
        $this->data['trans']['password'] = $this->language->get('password');
        $this->data['trans']['hash'] = $this->language->get('hash');
        
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['dimension'])) {
            $this->data['error_dimension'] = $this->error['dimension'];
        } else {
            $this->data['error_dimension'] = [];
        }

        $this->data['breadcrumbs'] = [];

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->getToken(), 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = [
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('extension/module', 'token=' . $this->getToken(), 'SSL'),
            'separator' => ' :: '
        ];

        $this->data['breadcrumbs'][] = [
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/ClobucksShop', 'token=' . $this->getToken(), 'SSL'),
            'separator' => ' :: '
        ];

        $this->data['action'] = $this->url->link('module/ClobucksShop', 'token=' . $this->getToken(), 'SSL');
        $this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->getToken(), 'SSL');

        $this->data['urls'] = [
            'checkAuth' => $this->url->link('module/ClobucksShop/checkAuthentication', 'token=' . $this->getToken(), 'SSL'),
            'getSuppliersList' => $this->url->link('module/ClobucksShop/getSuppliers', 'token=' . $this->getToken(), 'SSL'),
            'getSupplierCategoriesList' => $this->url->link('module/ClobucksShop/getSupplierCategories', 'token=' . $this->getToken(), 'SSL'),
            'loadProductsByCategory' => $this->url->link('module/ClobucksShop/loadProductsByCategory', 'token=' . $this->getToken(), 'SSL'),
            'sync' => $this->url->link('module/ClobucksShop/sync', 'token=' . $this->getToken(), 'SSL'),
            'changeRelateDelivery' => $this->url->link('module/ClobucksShop/changeRelateDelivery', 'token=' . $this->getToken(), 'SSL'),
        ];

        $this->data['modules'] = [];

        $clobucksSettings = $this->model_setting_setting->getSetting('ClobucksShop');

        if (isset($this->request->post['login'])) {
            $this->data['login'] = $this->request->post['login'];
        } else if(isset($clobucksSettings['login'])) {
            $this->data['login'] = $clobucksSettings['login'];
        } else {
            $this->data['login'] = '';
        }
        if (isset($this->request->post['password'])) {
            $this->data['password'] = $this->request->post['password'];
        } else if(isset($clobucksSettings['password'])) {
            $this->data['password'] = $clobucksSettings['password'];
        } else {
             $this->data['password'] = '';
        }
        if (isset($this->request->post['hash'])) {
            $this->data['hash'] = $this->request->post['hash'];
        } else if(isset($clobucksSettings['hash'])){
            $this->data['hash'] = $clobucksSettings['hash'];
        } else {
            $this->data['hash'] = '';
        }

        if (isset($this->request->post['clobucks_module'])) {
            $this->data['modules'] = $this->request->post['clobucks_module'];
        } elseif ($this->config->get('clobucks_module')) {
            $this->data['modules'] = $this->config->get('clobucks_module');
        }

        $this->load->model('design/layout');
        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $this->template = 'module/ClobucksShop.tpl';
        $this->children = [
            'common/header',
            'common/footer'
        ];

        
        $arSupplier = $this->model_setting_setting->getSetting('ClobucksShopSupplier');
        $this->data['supplier'] = isset($arSupplier['supplier']) ? $arSupplier['supplier'] : null;
              
              
  
		$this->load->model('setting/extension');
		$arShipping = $this->model_setting_extension->getInstalled('shipping');            
         foreach($arShipping as $shipping) {
		 	$this->language->load('shipping/' . $shipping);  
		 	
		 	$this->data['shipping'][] = array(
		 		'name'	=> $this->language->get('heading_title'),
		 		'value'	=> $shipping
		 	);	 
         }
         
         $this->load->model('clobucks/Supplier');
         $this->data['c_shipping'] = $this->model_clobucks_Supplier->getDelivery();     
        //echo "<pre>"; var_dump($this->data['c_shipping']); die();
		 $this->response->setOutput($this->render());
    }

    public function checkAuthentication()
    {
        if (!$this->isPostRequest() || !$this->isAuthenticationFormValid()) {
            return;
        }

        $this->load->model('clobucks/ClobucksShop');

        $login = $this->getPostParameter('login');
        $password = $this->getPostParameter('password');
        $hash = $this->getPostParameter('hash');

        $output = ['status' => (bool) $this->model_clobucks_ClobucksShop->auth($login, $password, $hash)];

        $this->response->addheader('Content-Type: application/json');
        $this->response->setOutput(json_encode($output));
    }

    public function getSuppliers()
    {
        if (!$this->isGetRequest()) {
            return;
        }

        $this->load->model('clobucks/ClobucksShop');

        $output = [
            'status' => 0,
            'data' => []
        ];
        
        
        if ($suppliers = $this->model_clobucks_ClobucksShop->apiGetSuppliers()) {
            $output = [
                'status' => 1,
                'data' => $suppliers,
            ];
        }

        $this->response->addheader('Content-Type: application/json');
        $this->response->setOutput(json_encode($output));
    }

    public function changeRelateDelivery() 
    {
        if (
        	!$this->isPostRequest()
        	|| !($deliveryId = $this->getPostParameter('delivery_id'))
        	|| !($ocDeliveryCode = $this->getPostParameter('oc_delivery_code'))
        
        ) {
            return;
        }
        
        
		$this->load->model('clobucks/Supplier');
        $this->model_clobucks_Supplier->relateId($deliveryId,$ocDeliveryCode);    		
    }
    
    
    
    public function getSupplierCategories()
    {
        if (!$this->isPostRequest() || !($supplier = $this->getPostParameter('supplier'))) {
            return;
        }

        $this->load->model('clobucks/ClobucksShop');

        $output = [
            'status' => 0,
            'data' => []
        ];
        if ($categories = $this->model_clobucks_ClobucksShop->apiGetSupplierCategories($supplier)) {
            $categoriesTree = new Tree();
            foreach ($categories as $category) {
                $categoriesTree->addNode(
                    $category['id'],
                    $category['parent'],
                    ['title' => $category['title']],
                    $category['checked']         
                );
            }

            $output = [
                'status' => 1,
                'data' => $categoriesTree->build(),
            ];
        }

        $this->response->addheader('Content-Type: application/json');
        $this->response->setOutput(json_encode($output, JSON_UNESCAPED_UNICODE));
    }
	
	/**
	* Клиент хочет поменять поставщика
	* 
	*/
	private function setNewSupplier($supplier = null) {
		if(is_null($supplier)) {         
			$arSupplier = $this->model_setting_setting->getSetting('ClobucksShopSupplier');
			$supplier = $arSupplier['supplier'];
		} else {
			$this->model_setting_setting->editSetting('ClobucksShopSupplier', ['supplier'=>$supplier]);
		}
		
		
		
        
        $arStatus = $this->model_clobucks_ClobucksShop->apiGetOrderStatuses();//Обновим статусы заказов
        $this->model_clobucks_Order->import($arStatus); 
        		
		$delivery = $this->model_clobucks_ClobucksShop->apiGetDelivery($supplier);	
		$this->model_clobucks_Supplier->importDelivery($delivery);	
		
		
		
			
	}
	
    public function loadProductsByCategory()
    {
        if (!$this->isPostRequest() || !($supplier = $this->getPostParameter('supplier'))
            || !($category = $this->getPostParameter('category'))
        ) {
            return;
        }

   
        $this->load->model('clobucks/ClobucksShop');
		$this->load->model('clobucks/ImportCategory');
		$this->load->model('clobucks/ImportProduct');
		$this->load->model('clobucks/Supplier');
		$this->load->model('clobucks/Order');
        $this->load->model('setting/setting');
        
        $this->setNewSupplier($supplier);
 
        $output = [
            'status' => 0,
            'data' => []
        ];
        
        
        if($this->syncProducts($category)) {
			$output = [
				'status' => 1,
			];  			
        }
        
        $this->response->addheader('Content-Type: application/json');
        $this->response->setOutput(json_encode($output, JSON_UNESCAPED_UNICODE));
    }
    
    public function sync() {
    	$this->load->model('setting/setting');
        $this->load->model('clobucks/ClobucksShop');
		$this->load->model('clobucks/ImportCategory');
		$this->load->model('clobucks/ImportProduct');
		$this->load->model('clobucks/Supplier');	
		$this->load->model('clobucks/Order');
        $this->load->model('setting/setting');	
		
		//Получить supplier из settings
		$this->setNewSupplier();
		
		$this->syncProducts();
		
		
		$this->redirect($this->url->link('module/ClobucksShop', 'token=' . $this->session->data['token'] . '', 'SSL'));
    }
    
    private function syncProducts($arrayCategory = null) {
    	
        $arSupplier = $this->model_setting_setting->getSetting('ClobucksShopSupplier');
        $supplier = $arSupplier['supplier'];
        
        if(is_null($arrayCategory)) {
        	$settingsCategory = $this->model_setting_setting->getSetting('ClobucksShopModel');
        	$arrayCategory = unserialize($settingsCategory['category']);
		}
		
		if(empty($arrayCategory) || empty($supplier)) return false;
		
        if(!($_arCategory = $this->model_clobucks_ClobucksShop->apiGetSupplierCategories($supplier))) {
			return false;
        }
        
        $arCategory = array();
        foreach($_arCategory as $value) {
			$arCategory[$value['id']] = $value;	
        }
       
       	$this->model_clobucks_ImportProduct->clear();
       	$this->model_clobucks_ImportCategory->clear();
	    if ($products = $this->model_clobucks_ClobucksShop->apiGetProductsByCategory($supplier, $arrayCategory)) {
	      
        	if(is_array($products) && count($products)) {
        		foreach($products AS $product) {
        			if(in_array($product['category'],$arCategory)) continue;
        			
        			if(!($categoryId = $this->model_clobucks_ImportCategory->execute($arCategory[$product['category']],$arCategory))) continue;
					$this->model_clobucks_ImportProduct->execute($product,$categoryId);
        		}
			}  
	    }
    }

    /**
     * @return bool
     */
    private function isPostRequest()
    {
        return $this->isRequestMethod('post');
    }

    /**
     * @return bool
     */
    private function isGetRequest()
    {
        return $this->isRequestMethod('get');
    }

    /**
     * @param string $method HTTP request method
     * @return bool
     */
    private function isRequestMethod($method)
    {
        return $this->request->server['REQUEST_METHOD'] == strtoupper($method);
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return $this->session->data['token'];
    }

    /**
     * @param string $parameter
     * @return null|string
     */
    private function getPostParameter($parameter)
    {
        return $this->getRequestParameter($parameter, 'POST');
    }

    /**
     * @param string $parameter
     * @return null|string
     */
    private function getGetParameter($parameter)
    {
        return $this->getRequestParameter($parameter, 'GET');
    }

    /**
     * @param string $parameter Parameter name
     * @param string $requestType Request type
     * @return null|string
     */
    private function getRequestParameter($parameter, $requestType)
    {
        $requestParameters = $this->request->{strtolower($requestType)};

        return array_key_exists($parameter, $requestParameters) ? $requestParameters[$parameter] : null;
    }

    /**
     * @return bool
     */
    private function isAuthenticationFormValid()
    {
        return !is_null($this->getPostParameter('login'))
            && !is_null($this->getPostParameter('password'))
            && !is_null($this->getPostParameter('hash'));
    }
}

class Tree
{
    /**
     * @var array All nodes
     */
    private $nodes = [];

    /**
     * @var array Children nodes
     */
    private $children = [];

    /**
     * @var array Tree roots
     */
    private $roots = [];

    /**
     * @param array $node
     * @return array
     */
    private function _build(array $node)
    {
        $children = [];
        foreach ($this->getChildren($node) as $child) {
            if ($this->hasChildren($child)) {
                $child = $this->_build($child);
            } else {
                $child['children'] = [];
            }

            $children[] = $child;
        }
        $node['children'] = $children;

        return $node;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isNodeExists($id)
    {
        return array_key_exists($id, $this->nodes);
    }

    /**
     * @param string $id
     * @param null|string $parentId
     * @param array $data
     */
    public function addNode($id, $parentId = null, $data = [], $checked = false)
    {
        $node = [
            'id' => $id ,
            'parent' => $parentId ,
            'data' => $data,
            'checked' => $checked
        ];
        $this->nodes[$id] = $node;

        if ($parentId) {
            if (!array_key_exists($parentId, $this->children)) {
                $this->children[$parentId] = [];
            }

            $this->children[$parentId][$id] = $node;
        } else {
            $this->roots[$id] = $node;
        }
    }

    /**
     * @param array $node
     * @return bool
     */
    public function hasChildren(array $node)
    {
        return array_key_exists($node['id'], $this->children);
    }

    /**
     * @param array $node
     * @return array
     */
    public function getChildren(array $node)
    {
        if (!$this->hasChildren($node)) {
            return [];
        }

        return $this->children[$node['id']];
    }

    /**
     * @return array
     */
    public function build()
    {
        $trees = [];
        foreach ($this->roots as $root) {
            $trees[] = $this->_build($root);                                                              
        }

        return $trees;
    }
}
