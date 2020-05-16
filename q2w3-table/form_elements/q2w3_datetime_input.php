<?php

class q2w3_datetime_input  extends _q2w3_input {
	
	
	
	public function html() {
		
		$date = new q2w3_date_input(array('name'=>$this->name, 'value'=>$this->value['date']));
		
		$time = new q2w3_time_input(array('name'=>$this->name, 'value'=>$this->value['time']));
		
		return $date.'&nbsp;'.$time;
		
	}
		
}

?>