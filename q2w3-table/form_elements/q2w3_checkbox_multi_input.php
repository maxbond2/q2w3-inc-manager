<?php

class q2w3_checkbox_multi_input  extends _q2w3_input {
	
	
	
	public function html() {
		
		/*if ($this->value) {
			
			$values = implode(',', (array)$this->value);
								
		}
		
		var_dump($this->value);*/
		
		$roles = $this->value_to_select;
		
		foreach ($roles as $role=>$name) {
			
			if (in_array($role, (array)$this->value)) $checked = true; else $checked = false;
			
			$checkbox = new q2w3_checkbox_input(array('name'=>$this->name.'['.$role.']', 'checked'=>$checked, 'value'=>1));
		
			$res[] =  $checkbox . '&nbsp;'. $name;
		
		}
		
		$res = implode(', ', $res);
		
		return $res;
		
	}
	
}

?>