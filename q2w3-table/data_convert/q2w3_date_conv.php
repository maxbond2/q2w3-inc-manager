<?php

class q2w3_date_conv extends _q2w3_data_conv {



	public function php2db($data) {
	
		if (is_array($data) && !empty($data)) {
			
			return $data['year'].'-'.$data['month'].'-'.$data['day'];
		
		} elseif ($data) {
			
			return $data;
		
		} else {
			
			return NULL;
			
		}
	
	}
	
	public function db2php($data) {
	
		if ($data && $data != '0000-00-00') {
			
			list($year,$month,$day) = explode("-", $data);
			
			return array('year'=>$year,'month'=>$month,'day'=>$day);  
		
		} else { 
			
			return NULL;
		
		}
	
	}
	
	public function db2text($data) {
	
		if (!$data) {
			
			return '-';
		
		} elseif ($data != '0000-00-00') {
			
			list($year,$month,$day) = explode("-", $data);
			
			return $day.'-'.$month.'-'.$year;
			
		}
								
	}

}

?>