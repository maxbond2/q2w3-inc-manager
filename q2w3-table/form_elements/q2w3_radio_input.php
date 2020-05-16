<?php

/**
 * @author Max Bond
 * 
 * <input type="radio"/> element
 *
 */
class q2w3_radio_input extends _q2w3_input {
	
	
	
	public function html() {
		
		if (!$this->checked && $this->value == $this->value_to_select) $this->checked = ' checked="checked"';
		
		return '<input type="radio" name="'. $this->name .'" value="'. $this->value_to_select .'"'. $this->class.$this->id.$this->checked.$this->disabled.$this->style.$this->js .'/>';
		
	}
	
}

?>