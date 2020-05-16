<?php

class q2w3_time_input  extends _q2w3_input {
	
	public $seconds = false;
	
	
	
	public function html() {
		
		$h = new q2w3_text_input(array('name'=>$this->name.'[h]', 'class'=>'h_input', 'maxlength'=>2, 'value'=>$this->value['h']));
		
		$m = new q2w3_text_input(array('name'=>$this->name.'[m]', 'class'=>'m_input', 'maxlength'=>2, 'value'=>$this->value['m']));
		
		if ($this->seconds) {
			
			$s = ' : '.$s = new q2w3_text_input(array('name'=>$this->name.'[s]', 'class'=>'s_input', 'maxlength'=>2, 'value'=>$this->value['s']));
		
		}
		
		return $h.' : '.$m.$s;
		
	}
	
	
}

?>