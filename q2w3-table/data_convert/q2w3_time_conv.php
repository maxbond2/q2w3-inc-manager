<?php

class q2w3_time_conv extends _q2w3_data_conv {



	public function php2db($data) {
	
		if (is_array($data) && !empty($data)) {
			
			$time = $data['h'].':'.$data['m'];
			
			if ($data['s']) $time .= ':'.$data['s'];
			
			return $time;
		
		} elseif ($data) {
			
			return $data;
		
		} else {
			
			return NULL;
			
		}
	
	}
	
	public function db2php($data) {
	
		if ($data && $data != '00:00:00') {
			
			list($h,$m,$s) = explode(":", $data);
			
			return array('h'=>$h,'m'=>$m,'s'=>$s);  
		
		} else { 
			
			return NULL;
		
		}
	
	}
	
	public function db2text($data) {
	
		if (!$data) {
			
			return '-';
		
		} elseif ($data != '00:00:00') {
			
			list($h,$m,$s) = explode(":", $data);
			
			$time = $h.':'.$m;
			
			if ($s != '00') $time .= ':'.$s;
			
			return $time;
			
		}
								
	}

}

?>