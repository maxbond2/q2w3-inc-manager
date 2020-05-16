<?php

class q2w3_month_input extends _q2w3_input {
	
	public static $months = array(
			''=>'',
			'01'=>'Января',
			'02'=>'Февраля',
			'03'=>'Марта',
			'04'=>'Апреля',
			'05'=>'Майя',
			'06'=>'Июня',
			'07'=>'Июля',
			'08'=>'Августа',
			'09'=>'Сентября',
			'10'=>'Октября',
			'11'=>'Ноября',
			'12'=>'Декабря');
	
	public function html() {
		
		$selector = new q2w3_select_input(array('name'=>$this->name, 'value_to_select'=>self::$months, 'class'=>'month_input', 'value'=>$this->value));
		
		return $selector->html();
		
	}
	
}

?>