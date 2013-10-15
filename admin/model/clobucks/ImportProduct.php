<?
class ModelClobucksImportProduct extends Model 
{
	private $arLanguageId = [1,2]; 
	
	public function execute($product,$categoryId) {    
		$productId = $this->_alwaysInserted($product);

		if(!$productId)            
			$this->_import($product,$categoryId);
		else {
			$this->_update($productId,$product,$categoryId);
		}
    }
    
    public function clear() {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET `status` = '0'");		
    }
    
    private function _alwaysInserted($product) {
        $result = $this->db->query("SELECT `product_id` FROM " . DB_PREFIX . "product
        WHERE
        sku = '" . $this->db->escape($product['sku']) . "'");

        if($result->num_rows > 0) return $result->row['product_id'];
        
        return false;          
    }

    private function _import($product,$categoryId) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product 
        SET 
        model = '" . $this->db->escape($product['title']) . "', 
        sku = '" . $this->db->escape($product['sku']) . "', 
        price = '" . (float)$product['price'] . "',
        status = '1',
        quantity = '999',
        date_added = NOW()");
        
        $productId = $this->db->getLastId();  
        
        $this->relateId($productId,$product);
        
        foreach($this->arLanguageId as $languageId) {
	        $this->db->query("
	        INSERT INTO " . DB_PREFIX . "product_description 
	        SET 
		        product_id = '" . (int)$productId . "', 
		        language_id = '" . (int)$languageId . "', 
		        name = '" . $this->db->escape($product['title']) . "',  
		        meta_description = '" . $this->db->escape($product['description']) . "', 
		        description = '" . $this->db->escape($product['description']) . "'");
		}
		
		$this->db->query("
		INSERT INTO " . DB_PREFIX . "product_to_category
		SET
			product_id = '" . $productId . "',
			category_id = '" . $categoryId . "'
		");  
		
		$this->db->query("
		INSERT INTO " . DB_PREFIX . "product_to_store 
		SET 
			product_id = '" . (int)$productId . "', 
			store_id = '0'
		");	
		
		$this->updateImage($product,$productId);	  
    }
    
    private function updateImage($product,$productId) {
		if(is_array($product['images']) && count($product['images'])) {
			$sort = 0;
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$productId . "'");
			
			foreach($product['images'] as $image) {				
				if($this->issetImage($image,$productId)) continue;
				
				$path = $this->getImage($image);
	
				if($sort == 0) {
					$this->db->query("
						UPDATE " . DB_PREFIX . "product
						SET 
							image = '" . $this->db->escape(html_entity_decode($path, ENT_QUOTES, 'UTF-8')) . "'
						WHERE 
							product_id = '" . (int)$productId  . "'
					");
				}	
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$productId . "', image = '" . $this->db->escape(html_entity_decode($path, ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$sort++ . "'");
			}			
		}		
    }
    
    private function issetImage($image,$productId) {
    	$imgName = $this->getImageName($image);

		$result = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "product_image 
			WHERE 
			product_id = '" . (int)$productId . "' 
			AND image = '" . $this->db->escape($imgName) . "'
		");

		
		return ($result->num_rows) ? true : false;		
    }
    
    private function getImageName($image) {
     	$arPath = explode('/',$image);
    	return end($arPath);   
	}
	
    private function getImage($image) {
    	$arPath = explode('/',$image);
    	$imgName = $this->getImageName($image);
    	
    	$dir = DIR_IMAGE.'clobucks/';
		if(file_exists($dir.$imgName)) return 'clobucks/'.$imgName;
    	
    	
    	if(!file_exists($dir)) {
			mkdir($dir);
    	}
		$ch = curl_init($image);
		$fp = fopen($dir.$imgName, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		
		return 'clobucks/'.$imgName;
    }   
    
    private function relateId($productId,$product) {
		$this->checkTableRelatedId();

		$this->db->query("
			INSERT INTO ". DB_PREFIX ."clobucks_related_id
			SET
				product_id = '". $product['id'] ."',
				oc_id = '". $productId ."'
			ON DUPLICATE KEY UPDATE
				product_id = '". $product['id'] ."',
				oc_id = '". $productId ."'");		
    }
    
    private function checkTableRelatedId() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS ". DB_PREFIX ."clobucks_related_id (
			  product_id varchar(255) NOT NULL,
			  oc_id int(11) NOT NULL,
			  UNIQUE INDEX UK_clobucks_related_id_oc_id (oc_id),
			  UNIQUE INDEX UK_clobucks_related_id_product_id (product_id)
			)
			ENGINE = MYISAM;");
    }    
    
    
    private function _update($productId,$product,$categoryId) {
		$this->relateId($productId,$product);
		
        $this->db->query("
        UPDATE " . DB_PREFIX . "product 
        SET 
	        model = '" . $this->db->escape($product['title']) . "', 
	        sku = '" . $this->db->escape($product['sku']) . "', 
	        price = '" . (float)$product['price'] . "',
	        status = '1'
        WHERE 
        	product_id = '" . (int)$productId . "'");		


 		foreach($this->arLanguageId as $languageId) {
	        $this->db->query("
	        UPDATE " . DB_PREFIX . "product_description 
	        SET  
		        
		        name = '" . $this->db->escape($product['title']) . "',  
		        meta_description = '" . $this->db->escape($product['description']) . "', 
		        description = '" . $this->db->escape($product['description']) . "'
		    WHERE
		    	product_id = '" . (int)$productId . "'
		    	AND language_id = '" . (int)$languageId . "'");
		}
		
		$this->db->query("
		UPDATE " . DB_PREFIX . "product_to_category
		SET
			category_id = '" . $categoryId . "'
		WHERE
			product_id = '" . $productId . "'");  	
			
			
		$this->updateImage($product,$productId);	
    }     	
}
?>