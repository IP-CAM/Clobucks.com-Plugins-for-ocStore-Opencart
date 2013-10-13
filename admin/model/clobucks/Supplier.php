<?
class ModelClobucksSupplier extends Model 
{
	private $arLanguageId = [1,2]; 
	
	public function import($obStatus) {

		$sql = "TRUNCATE TABLE " . DB_PREFIX . "order_status";
		$result = $this->db->query($sql);
		foreach($this->arLanguageId as $languageId) {
    		foreach($obStatus->statuses->status as  $xml) {
    			$name = (string)$xml;
    			$id = (int)$xml['id'];
				$sql = "
					INSERT INTO " . DB_PREFIX . "order_status
					SET 
						order_status_id = '" . $id . "',
						language_id = '" . (int)$languageId . "',
						`name` = '" . $name . "'
					";
				//echo $sql;
				$this->db->query($sql);	
			}	
		}	
	}
	
	public function importDelivery($obDelivery) {
		$this->_checkDeliveryTable();
		
        $sql = "TRUNCATE TABLE " . DB_PREFIX . "clobucks_delivery";
        $result = $this->db->query($sql);
        foreach($obDelivery->deliveries->delivery as  $xml) {
            $title = (string)$xml->title;
            $description = (string)$xml->description;
            $id = (int)$xml['id'];
            $sql = "
                INSERT INTO " . DB_PREFIX . "clobucks_delivery
                SET 
                    `id` = '" . $id . "',
                    `title` = '" . $title . "',
                    `description` = '" . $description . "'
                ";
            $this->db->query($sql);    
		}
		$this->deleteUnrelated();  	    	
	}

	private function deleteUnrelated() {
		return;
	}
	
    public function relateId($deliveryId,$ocDeliveryId) {
		$this->_checkDeliveryRelatedTable();

		$this->db->query("
			INSERT INTO ". DB_PREFIX ."clobucks_related_delivery
			SET
				delivery_id = '". $deliveryId ."',
				oc_delivery_code = '". $ocDeliveryId ."'
			ON DUPLICATE KEY UPDATE
				delivery_id = '". $deliveryId ."',
				oc_delivery_code = '". $ocDeliveryId ."'");		
    }	

	public function getDelivery() {
        $this->_checkDeliveryTable();
        $this->_checkDeliveryRelatedTable();        
        
		$result = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "clobucks_delivery
		");
		$arResult = array();
		foreach($result->rows AS $value) {		
			$resultSelected = $this->db->query("
				SELECT oc_delivery_code 
				FROM " . DB_PREFIX . "clobucks_related_delivery 
				WHERE delivery_id = '". $value['id'] ."'
				LIMIT 1
			");
			$value['selected'] = array();
			
			if($resultSelected->num_rows) {
				foreach($resultSelected->rows as $valueSelected)
					$value['selected'][] = $valueSelected['oc_delivery_code'];	
			}
			
			$arResult[] = $value;
			
		}
		return $arResult;
	}
	
	private function _checkDeliveryTable() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "clobucks_delivery (
			  id varchar(255) NOT NULL,
			  title varchar(255) DEFAULT NULL,
			  description varchar(255) DEFAULT NULL,
			  timestamp_x timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  PRIMARY KEY (id)
			)
			ENGINE = MYISAM
			CHARACTER SET utf8
			COLLATE utf8_general_ci;
		");		
	}
	
	private function _checkDeliveryRelatedTable() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "clobucks_related_delivery (
			  delivery_id varchar(255) NOT NULL,
			  oc_delivery_code varchar(255) NOT NULL,
			  UNIQUE INDEX UK_related_delivery_oc_delivery_code (oc_delivery_code)
			)
			ENGINE = MYISAM
			CHARACTER SET utf8
			COLLATE utf8_general_ci;
		");		
	}
}