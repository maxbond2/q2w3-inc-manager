<?php

class q2w3_date_input  extends _q2w3_input {
	
	
	
	public function html() {
		
		$day = new q2w3_text_input(array('name'=>$this->name.'[day]', 'class'=>'day_input', 'maxlength'=>2, 'value'=>$this->value['day']));
		
		$month = new q2w3_month_input(array('name'=>$this->name.'[month]', 'value'=>$this->value['month']));
		
		$year = new q2w3_text_input(array('name'=>$this->name.'[year]', 'class'=>'year_input', 'maxlength'=>4, 'value'=>$this->value['year']));
		
		return $day.'&nbsp;'.$month.'&nbsp;'.$year;
		
	}
	
	
}

?>