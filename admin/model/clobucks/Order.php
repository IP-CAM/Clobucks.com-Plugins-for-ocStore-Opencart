<?
class ModelClobucksOrder extends Model 
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
	
    public function getOrder($ocOrderId) {
		$result = $this->db->query("SELECT order_id FROM ". DB_PREFIX ."clobucks_related_order WHERE oc_order_id = '" . $ocOrderId . "'");
		
		return (count($result->row)) ? $result->row['order_id'] : null;
    }  
}