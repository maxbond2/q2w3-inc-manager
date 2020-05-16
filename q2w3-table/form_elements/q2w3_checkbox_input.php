<?php

/**
 * @author Max Bond
 * 
 * <input type="checkbox"/> element
 *
 */
class q2w3_checkbox_input extends _q2w3_input {
	
	
	
	public function html() {
		
		if (!$this->checked && $this->value == $this->value_to_select) $this->checked = ' checked="checked"';
				
		return '<input type="checkbox" name="'. $this->name .'" value="'. $this->value_to_select .'"'. $this->class.$this->id.$this->checked.$this->disabled.$this->style.$this->js .'/>';
		
	}
	
}

?>