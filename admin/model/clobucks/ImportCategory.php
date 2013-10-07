<?

class ModelClobucksImportCategory extends Model {
    
    private $arCategory;
    private $languageId = 1; //only rus
    private $arTreeCategories;
    
	/**
	* Понеслась
	* 
	* @param array $category
	* @param array $arCategory
	* @return int
	*/
    public function execute($category,$arCategory) {
    	$this->arCategory = $arCategory;
        
        /**
        * Проверим, есть ли у нас уже импортируемая категория
        */
        $issetSqlResult = $this->_issetCategory($category);
        
        /**
        * Если вдруг категории еще нет, то её надо создать.
        * При этом важно не забыть о родителяъ
        */
        
        if(!($issetSqlResult->num_rows)) {
			$this->getTreeCategories($category);
        } else {
        	$this->_update($category,(int)$issetSqlResult->row['category_id']);
			return (int)$issetSqlResult->row['category_id'];
        }

        return (int)$this->_issetCategories($this->arTreeCategories);
    }
    
    public function clear() {
		$this->db->query("UPDATE " . DB_PREFIX . "category SET `status` = '0'");	
    }
    
    /**
    * Записываем в базу категорию
    * 
    * @param array $category
    * @param int $parentId
    * @return int
    */
	private function _insertCategory($category, $parentId = 0) {
		
        $this->db->query("
        	INSERT INTO " . DB_PREFIX . "category 
            SET parent_id = '" . $parentId . "', 
	            `top` = '" . (($parentId == 0) ? 1 : 0) . "', 
	            `column` = '0',  
	            status = '1', 
	            date_modified = NOW(), 
	            date_added = NOW()
	        ");		
		$categoryId = intval($this->db->getLastId());
		
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "category_description 
			SET category_id = '" . (int)$categoryId . "', 
				language_id = '" . $this->languageId . "', 
				name = '" . $this->db->escape($category['title']) . "',
				meta_keyword = '',
				meta_description = '',
				description = '', 
				seo_title = '',
				seo_h1 = ''
			");	
			
		//Дублируем для eng-названия :(
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "category_description 
			SET category_id = '" . (int)$categoryId . "', 
				language_id = '2', 
				name = '" . $this->db->escape($category['title']) . "',
				meta_keyword = '',
				meta_description = '',
				description = '', 
				seo_title = '',
				seo_h1 = ''
			");				
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . $categoryId . "', store_id = '0'");
		
		$level = 0;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parentId . "' ORDER BY `level` ASC");
		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$categoryId . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");
			
			$level++;
		}
		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$categoryId . "', `path_id` = '" . (int)$categoryId . "', `level` = '" . (int)$level . "'");		
						
		
		
		return (int)$categoryId;
				
	}
    
    /**
    * Проверяем, существует ли категория. 
    * Если нет, то создаём её и её родителей
    * 
    * @param array $category
    */
    private function _issetCategory($category) {
 		$result = $this->db->query("
 		SELECT `category_id` FROM " . DB_PREFIX . "category_description
        WHERE name = '" . $this->db->escape($category['title']) ."'
       	AND language_id = '" . (int)$this->languageId . "'");
        
        return $result;
    }
    
    private function _update($category,$categoryId) {
		$result = $this->db->query("
			UPDATE " . DB_PREFIX . "category
			SET `status` = '1'
			WHERE `category_id` = '" . $categoryId . "'
		");	
		
		$parentId = $this->getParentId($categoryId);
		if($parentId > 0) $this->_update($category,$parentId);
    }
    
    /**
    * Проверим, существуют ли какие-нибудь категории ищ родительских
    * 
    * @param mixed $arCategory
    */
    private function _issetCategories($arCategory, $parentId = 0) {
		ksort($arCategory);
		
		foreach($arCategory as $parentCategoryId=>$categoryId) {
			$category = $this->arCategory[$categoryId];
			
			$result = $this->_issetCategory($category);
			if($result->num_rows > 0) {
				unset($arCategory[$parentCategoryId]);
				$newCategoryId = $this->_issetCategories($arCategory, $result->row['category_id']);
			} 
			else {
				$newCategoryId = $this->_insertCategory($category,(int)$parentId);
				
				unset($arCategory[$parentCategoryId]);
				$this->_issetCategories($arCategory, $newCategoryId);
			}			
		}
		return $newCategoryId;
    }
    
    public function getTreeCategories($category) {
		
		$this->arTreeCategories = array();
		$this->_getTreeCategories($category);
		
		return;
    }
    
    private function _getTreeCategories($category) {
       	$this->arTreeCategories[(int)$category['parent']] = $category['id'];
       	
    	if(intval($category['parent']) == 0) return;   	
    	$categoryInfo = $this->arCategory[$category['parent']];
    	
    	$this->_getTreeCategories($categoryInfo);	
    }
    
    private function getParentId($categoryId) {
 		$result = $this->db->query("
 		SELECT `parent_id` FROM " . DB_PREFIX . "category
        WHERE category_id = '" . $categoryId ."'");
        
        return (int)$result->row['parent_id'];		
    }   
}