<?php

class q2w3_datetime_conv extends _q2w3_data_conv {



	public function php2db($data) {
	
		if (is_array($data) && !empty($data)) {
			
			$time = $data['h'].':'.$data['m'];
			
			if ($data['s']) $time .= ':'.$data['s'];
			
			return $data['year'].'-'.$data['month'].'-'.$data['day'].' '.$time;
		
		} elseif ($data) {
			
			return $data;
		
		} else {
			
			return NULL;
			
		}
	
	}
	
	public function php2db_search($data) {
		
		if (is_array($data) && !empty($data)) {
			
			if (!$data['year']) $data['year'] = '%';

			if (!$data['month']) $data['month'] = '%';
			
			if (!$data['day']) $data['day'] = '%';
			
				elseif (strlen(trim($data['day'])) == 1) $data['day'] = '0'.$data['day'];
			
			if (!$data['h']) $data['h'] = '%';
			
				elseif (strlen(trim($data['h'])) == 1) $data['h'] = '0'.$data['h'];
			
			if (!$data['m']) $data['m'] = '%';
			
				elseif (strlen(trim($data['m'])) == 1) $data['m'] = '0'.$data['m'];
			
			if (!$data['s']) $data['s'] = '%';
			
				elseif (strlen(trim($data['s'])) == 1) $data['s'] = '0'.$data['s'];
			
			return $data['year'].'-'.$data['month'].'-'.$data['day'].' '.$data['h'].':'.$data['m'].':'.$data['s'];
		
		} 
		
		return false;
		
	}
	
	public function db2php($data) {
	
		if ($data && $data != '0000-00-00 00:00:00') {
			
			list($date, $time) = explode(" ", $data);
			
			list($year,$month,$day) = explode("-", $date);
			
			list($h,$m,$s) = explode(":", $time);
			
			return array('date'=>array('year'=>$year,'month'=>$month,'day'=>$day),'time'=>array('h'=>$h,'m'=>$m,'s'=>$s));  
		
		} else { 
			
			return NULL;
		
		}
	
	}
	
	public function db2text($data) {
	
		if (!$data) {
			
			return '-';
		
		} elseif ($data != '0000-00-00 00:00:00') {
			
			list($date, $time) = explode(" ", $data);
			
			list($year,$month,$day) = explode("-", $date);
			
			list($h,$m,$s) = explode(":", $time);
			
			$time = $h.':'.$m;
			
			if ($s != '00') $time .= ':'.$s;
			
			return $day.'-'.$month.'-'.$year.' '.$time;
			
		}
								
	}

}

?>