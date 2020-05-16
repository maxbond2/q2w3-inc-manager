<?php

/**
 * @author Max Bond
 * 
 * Select field data conver class
 *
 */
class q2w3_select_conv extends _q2w3_data_conv {



	public function db2text($data) {
			
		if (is_array($this->propertie->input_values)) {
	
			$this->flat_input_values_array();
						
			$data = (array) explode(',',$data);
			
			$res = array();
			
			foreach ($data as $db_value) {
					
				if (key_exists($db_value, $this->propertie->input_values)) $res[] = $this->propertie->input_values[$db_value];
					
			}
				
			return implode(' // ', $res);
				
		}
	
	}
	
	/**
	 * This function is for creating 'flat' input values array. Needed if there are sub arrays in $this->propertie->input_values 
	 * 
	 */
	protected function flat_input_values_array() {
		
		foreach ($this->propertie->input_values as $key=>$value) {
			
			if (is_array($value)) {
				
				unset($this->propertie->input_values[$key]);
				
				$this->propertie->input_values += $value;
				
			}
			
		}
		
	}

}

?>