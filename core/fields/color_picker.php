<?php

class acf_Color_picker extends acf_Field
{

	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
    	parent::__construct($parent);
    	
    	$this->name = 'color_picker';
		$this->title = __("Color Picker",'acf');
		
   	}
   	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_print_scripts / admin_print_styles
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_print_scripts()
	{
		wp_enqueue_script(array(
			'farbtastic'
		));
	}
	
	function admin_print_styles()
	{
		wp_enqueue_style(array(
			'farbtastic'
		));
  
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*
	*	@author Elliot Condon
	*	@since 2.0.5
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{		
		// defaults
		if($field['value'] == "") $field['value'] = '#ffffff';
		
		// html
		echo '<input type="text" value="' . $field['value'] . '" class="acf_color_picker" name="' . $field['name'] . '" id="' . $field['name'] . '" />';

	}
	
	
}

?>