<?php

class acf_Checkbox extends acf_Field
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
    	
    	$this->name = 'checkbox';
		$this->title = __("Checkbox",'acf');
		
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
		if(empty($field['value']))
		{
			$field['value'] = array();
		}
		
		
		// single value to array conversion
		if( !is_array($field['value']) )
		{
			$field['value'] = array( $field['value'] );
		}
		
		
		// no choices
		if(empty($field['choices']))
		{
			echo '<p>' . __("No choices to choose from",'acf') . '</p>';
			return false;
		}
		
		
		// html
		echo '<ul class="checkbox_list '.$field['class'].'">';
		echo '<input type="hidden" name="'.$field['name'].'" value="" />';
		// checkbox saves an array
		$field['name'] .= '[]';
		
		// foreach choices
		foreach($field['choices'] as $key => $value)
		{
			$selected = '';
			if(in_array($key, $field['value']))
			{
				$selected = 'checked="yes"';
			}
			echo '<li><label><input type="checkbox" class="' . $field['class'] . '" name="' . $field['name'] . '" value="' . $key . '" ' . $selected . ' />' . $value . '</label></li>';
		}
		
		echo '</ul>';

	}


	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*
	*	@author Elliot Condon
	*	@since 2.0.6
	*	@updated 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field)
	{	
		// defaults
		
		
		// implode checkboxes so they work in a textarea
		if(isset($field['choices']) && is_array($field['choices']))
		{		
			foreach($field['choices'] as $choice_key => $choice_val)
			{
				$field['choices'][$choice_key] = $choice_key.' : '.$choice_val;
			}
			$field['choices'] = implode("\n", $field['choices']);
		}
		else
		{
			$field['choices'] = "";
		}
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label for=""><?php _e("Choices",'acf'); ?></label>
				<p class="description"><?php _e("Enter your choices one per line",'acf'); ?><br />
				<br />
				<?php _e("Red",'acf'); ?><br />
				<?php _e("Blue",'acf'); ?><br />
				<br />
				<?php _e("red : Red",'acf'); ?><br />
				<?php _e("blue : Blue",'acf'); ?><br />
				</p>
			</td>
			<td>
				<textarea rows="5" name="fields[<?php echo $key; ?>][choices]" id=""><?php echo $field['choices']; ?></textarea>
			</td>
		</tr>
		<?php
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	pre_save_field
	*	- called just before saving the field to the database.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function pre_save_field($field)
	{
		// vars
		$defaults = array(
			'choices'	=>	'',
		);
		
		$field = array_merge($defaults, $field);
		
		
		// check if is array. Normal back end edit posts a textarea, but a user might use update_field from the front end
		if( is_array( $field['choices'] ))
		{
		    return $field;
		}


		// vars
		$new_choices = array();
		
		// explode choices from each line
		if(strpos($field['choices'], "\n") !== false)
		{
			// found multiple lines, explode it
			$field['choices'] = explode("\n", $field['choices']);
		}
		else
		{
			// no multiple lines! 
			$field['choices'] = array($field['choices']);
		}
		
		// key => value
		foreach($field['choices'] as $choice)
		{
			if(strpos($choice, ' : ') !== false)
			{
				$choice = explode(' : ', $choice);
				$new_choices[trim($choice[0])] = trim($choice[1]);
			}
			else
			{
				$new_choices[trim($choice)] = trim($choice);
			}
		}
		
		// update choices
		$field['choices'] = $new_choices;
		
		// return updated field
		return $field;

	}
		
}
?>