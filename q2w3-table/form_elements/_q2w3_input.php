<?php

/**
 * @author Max Bond
 * 
 * Describes basic methods for <input> classes
 *
 */
abstract class _q2w3_input {
	
	public $name; // <input name=""/> attribute value
	
	public $value; // <input value=""/> attribute value // also used for <select> to input value of selected elements
	
	public $value_to_select; // hear you can set values for checkbox, radio elements // for select element values it should be an associative array ('id'=>'name') // for groups use array in array: array('group_name'=>array('id'=>'name'))
	
	public $disabled; // <input disabled=""/> attribute value
	
	public $multiple; // <input multiple=""/> attribute value
	
	public $checked; // <input checked=""/> attribute value
	
	public $maxlength; // maxlenght attribute value
	
	public $class; // element css class name
	
	public $id; // element id
	
	public $style; // element style
	
	public $js; // element javascript code
	
		
	
	function __construct($values = false) {
		
		$this->set($values);
		
	}
	
	/**
	 * Quickly converts class to a string. Used for echo $object 
	 */
	public function __toString() {
				
		$html = $this->html(); 
		
		if (!$html) return ''; else return $html;
		
	}
	
	
	/**
	 * Used for setting object properties with array values. Array keys are propertie names and array values are properties values
	 */
	public function set($values) {
		
		if (is_array($values)) {
			
			foreach ($this as $key=>$value) {
				
				if (key_exists($key, $values)) $this->$key = $values[$key];
				
			}
			
		}
		
		$this->prepare();
		
	}
	
	/**
	 * Cnverts properties values to html form
	 */
	public function prepare() {
		
		if ($this->disabled) $this->disabled = ' disabled="disabled"';
		if ($this->multiple) $this->multiple = ' multiple="multiple"';
		if ($this->checked) $this->checked = ' checked="checked"';
		if ($this->maxlength) $this->maxlength = ' maxlength="'. $this->maxlength .'"';
		if ($this->class) $this->class = ' class="'. $this->class .'"';
		if ($this->id) $this->id = ' id="'. $this->id .'"';
		if ($this->style) $this->style = ' style= "'. $this->style .'"';
		if ($this->js) $this->js = ' '.$this->js;
							
	}
	
	/**
	 * This function must be overloaded in a child class. Used for providing form element html code 
	 */
	abstract public function html();
	
}

?>