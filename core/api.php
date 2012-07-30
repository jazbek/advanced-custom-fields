<?php

// vars
$GLOBALS['acf_field'] = array();


/*--------------------------------------------------------------------------------------
*
*	get_fields
*
*	@author Elliot Condon
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function get_fields($post_id = false)
{
	// vars
	global $post;
	
	if(!$post_id)
	{
		$post_id = $post->ID;
	}
	
	
	// default
	$value = array();
	
	$keys = get_post_custom_keys($post_id);
		
	if($keys)
	{
		foreach($keys as $key)
		{
			if(substr($key, 0, 1) != "_")
			{
				$value[$key] = get_field($key, $post_id);
			}
		}
 	}
 	
	// no value
	if(empty($value))
	{
		return false;
	}
	
	return $value;
	
}


/*--------------------------------------------------------------------------------------
*
*	get_field
*
*	@author Elliot Condon
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/
 
function get_field($field_name, $post_id = false) 
{ 
	global $post, $acf; 
	 
	if(!$post_id) 
	{ 
		$post_id = $post->ID; 
	}
	
	
	// allow for option == options
	if( $post_id == "option" )
	{
		$post_id = "options";
	}
	
	
	// return cache 
	$cache = wp_cache_get('acf_get_field_' . $post_id . '_' . $field_name); 
	if($cache) 
	{ 
		return $cache; 
	} 
	 
	// default 
	$value = ""; 
	 
	 
	// get value
	$field_key = "";
	if( is_numeric($post_id) )
	{
		$field_key = get_post_meta($post_id, '_' . $field_name, true); 
	}
	else
	{
		$field_key = get_option('_' . $post_id . '_' . $field_name); 
	}

	
	if($field_key != "") 
	{ 
		// we can load the field properly! 
		$field = $acf->get_acf_field($field_key); 
		$value = $acf->get_value_for_api($post_id, $field); 
	} 
	else 
	{ 
		// just load the text version 
		if( is_numeric($post_id) )
		{
			$value = get_post_meta($post_id, $field_name, true);
		}
		else
		{
			$value = get_option($post_id . '_' . $field_name); 
		}
		 
	} 
	 
	// no value? 
	if($value == "") $value = false; 
	 
	// update cache 
	wp_cache_set('acf_get_field_' . $post_id . '_' . $field_name, $value); 
	 
	return $value; 
	 
}


/*--------------------------------------------------------------------------------------
*
*	the_field
*
*	@author Elliot Condon
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function the_field($field_name, $post_id = false)
{
	$value = get_field($field_name, $post_id);
	
	if(is_array($value))
	{
		$value = @implode(', ',$value);
	}
	
	echo $value;
}


/*--------------------------------------------------------------------------------------
*
*	has_sub_field
*
*	@author Elliot Condon
*	@since 3.3.4
* 
*-------------------------------------------------------------------------------------*/

function has_sub_field($field_name, $post_id = false)
{
	
	// empty?
	if( empty($GLOBALS['acf_field']) )
	{
		$GLOBALS['acf_field'][] = array(
			'name'	=>	$field_name,
			'value'	=>	get_field($field_name, $post_id),
			'row'	=>	-1
		);
	}
	
	
	// vars
	$depth = count( $GLOBALS['acf_field'] ) - 1;
	$name = $GLOBALS['acf_field'][$depth]['name'];
	$value = $GLOBALS['acf_field'][$depth]['value'];
	$row = $GLOBALS['acf_field'][$depth]['row'];
	

	
	// does the given $field_name match the current field?
	if( $field_name != $name )
	{
		// is this a "new" while loop refering to a sub field?
		if( isset($value[$row][$field_name]) )
		{
			$GLOBALS['acf_field'][] = array(
				'name'	=>	$field_name,
				'value'	=>	$value[$row][$field_name],
				'row'	=>	-1
			);
		}
		
		
		// if someone used break; We should see if the parent value has this field_name as a value.
		if( isset($GLOBALS['acf_field'][$depth-1]) && $GLOBALS['acf_field'][$depth-1]['name'] == $field_name )
		{
			unset( $GLOBALS['acf_field'][$depth] );
			$GLOBALS['acf_field'] = array_values($GLOBALS['acf_field']);
		}
		
	}
	
	
	// update vars
	$depth = count( $GLOBALS['acf_field'] ) - 1;
	$value = $GLOBALS['acf_field'][$depth]['value'];
	$row = $GLOBALS['acf_field'][$depth]['row'];

		
	// increase row number
	$GLOBALS['acf_field'][$depth]['row']++;
	$row++;
	
	
	if( isset($value[$row]) )
	{
		// next row exists
		return true;
	}
	
	
	// no next row! Unset this array and return false to stop while loop
	unset( $GLOBALS['acf_field'][$depth] );
	$GLOBALS['acf_field'] = array_values($GLOBALS['acf_field']);

	return false;
	
}


/*--------------------------------------------------------------------------------------
*
*	get_sub_field
*
*	@author Elliot Condon
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function get_sub_field($field_name)
{

	// no field?
	if( empty($GLOBALS['acf_field']) )
	{
		return false;
	}
	
	
	// vars
	$depth = count( $GLOBALS['acf_field'] ) - 1;
	$value = $GLOBALS['acf_field'][$depth]['value'];
	$row = $GLOBALS['acf_field'][$depth]['row'];
	
	
	// no value at i
	if( !isset($GLOBALS['acf_field'][$depth]['value'][$row][$field_name]) )
	{
		return false;
	}
	
	
	return $GLOBALS['acf_field'][$depth]['value'][$row][$field_name];
}


/*--------------------------------------------------------------------------------------
*
*	the_sub_field
*
*	@author Elliot Condon
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function the_sub_field($field_name)
{
	$value = get_sub_field($field_name);
	
	if(is_array($value))
	{
		$value = implode(', ',$value);
	}
	
	echo $value;
}


/*--------------------------------------------------------------------------------------
*
*	register_field
*
*	@author Elliot Condon
*	@since 3.0.0
* 
*-------------------------------------------------------------------------------------*/

$GLOBALS['acf_register_field'] = array();

function register_field($class = "", $url = "")
{
	$GLOBALS['acf_register_field'][] =  array(
		'url'	=> $url,
		'class'	=>	$class,
	);
}

function acf_register_field($array)
{
	$array = array_merge($array, $GLOBALS['acf_register_field']);
	
	return $array;
}
add_filter('acf_register_field', 'acf_register_field');


/*--------------------------------------------------------------------------------------
*
*	register_field_group
*
*	@author Elliot Condon
*	@since 3.0.6
* 
*-------------------------------------------------------------------------------------*/

$GLOBALS['acf_register_field_group'] = array();

function register_field_group($array)
{
	// add id
	if(!isset($array['id']))
	{
		$array['id'] = uniqid();
	}
	

	// 3.2.5 - changed show_on_page option
	if( !isset($array['options']['hide_on_screen']) && isset($array['options']['show_on_page']) )
	{
		$show_all = array('the_content', 'discussion', 'custom_fields', 'comments', 'slug', 'author');
		$array['options']['hide_on_screen'] = array_diff($show_all, $array['options']['show_on_page']);
		unset( $array['options']['show_on_page'] );
	}


	$GLOBALS['acf_register_field_group'][] = $array;
}

function acf_register_field_group($array)
{
	$array = array_merge($array, $GLOBALS['acf_register_field_group']);
	
	// order field groups based on menu_order
	// Obtain a list of columns
	foreach ($array as $key => $row) {
	    $menu_order[$key] = $row['menu_order'];
	}
	
	// Sort the array with menu_order ascending
	// Add $array as the last parameter, to sort by the common key
	if(isset($menu_order))
	{
		array_multisort($menu_order, SORT_ASC, $array);
	}
	
	
	return $array;
}
add_filter('acf_register_field_group', 'acf_register_field_group');


/*--------------------------------------------------------------------------------------
*
*	register_options_page
*
*	@author Elliot Condon
*	@since 3.0.0
* 
*-------------------------------------------------------------------------------------*/

$GLOBALS['acf_register_options_page'] = array();

function register_options_page($title = "")
{
	$GLOBALS['acf_register_options_page'][] =  array(
		'title'	=> $title,
		'slug' => 'options-' . sanitize_title_with_dashes( $title ),
	);
}

function acf_register_options_page($array)
{
	$array = array_merge($array, $GLOBALS['acf_register_options_page']);
	
	return $array;
}
add_filter('acf_register_options_page', 'acf_register_options_page');



/*--------------------------------------------------------------------------------------
*
*	get_row_layout
*
*	@author Elliot Condon
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function get_row_layout()
{
	// vars
	$value = get_sub_field('acf_fc_layout');
	
	
	return $value;
}


/*--------------------------------------------------------------------------------------
*
*	shorcode support
*
*	@author Elliot Condon
*	@since 1.1.1
* 
*-------------------------------------------------------------------------------------*/

function acf_shortcode( $atts )
{
	// extract attributs
	extract( shortcode_atts( array(
		'field' => "",
		'post_id' => false,
	), $atts ) );
	
	
	// $field is requird
	if( !$field || $field == "" )
	{
		return "";
	}
	
	
	// get value and return it
	$value = get_field( $field, $post_id );
	
	
	if(is_array($value))
	{
		$value = @implode( ', ',$value );
	}
	
	return $value;
}
add_shortcode( 'acf', 'acf_shortcode' );


/*--------------------------------------------------------------------------------------
*
*	Front end form Head
*
*	@author Elliot Condon
*	@since 1.1.4
* 
*-------------------------------------------------------------------------------------*/

function acf_form_head()
{
	// global vars
	global $acf;
	
	
	
	// run database save first
	if(isset($_POST) && isset($_POST['acf_save']))
	{
		$post_id = $_POST['post_id'];
		
		// save fields
		$fields = $_POST['fields'];
		
		if($fields)
		{
			foreach($fields as $key => $value)
			{
				// get field
				$field = $acf->get_acf_field($key);
				
				$acf->update_value($post_id, $field, $value);
			}
		}
		
		
		// redirect
		if(isset($_POST['return']))
		{
			wp_redirect($_POST['return']);
			exit;
		}
		
	}
	
		
	// register css / javascript
	do_action('acf_print_scripts-input');
	do_action('acf_print_styles-input');
	
	// need wp styling
	wp_enqueue_style(array(
		'colors-fresh'
	));
	
		
	// form was not posted, load js head stuff
	add_action('wp_head', 'acf_form_wp_head');
	
}

function acf_form_wp_head()
{
	// global vars
	global $post, $acf;
	

	// Style
	echo '<link rel="stylesheet" type="text/css" href="'.$acf->dir.'/css/global.css?ver=' . $acf->version . '" />';
	echo '<link rel="stylesheet" type="text/css" href="'.$acf->dir.'/css/input.css?ver=' . $acf->version . '" />';


	// Javascript
	echo '<script type="text/javascript" src="'.$acf->dir.'/js/input-actions.js?ver=' . $acf->version . '" ></script>';
	echo '<script type="text/javascript">acf.post_id = ' . $post->ID . ';</script>';
	
	
	// add user js + css
	do_action('acf_head-input');
}


/*--------------------------------------------------------------------------------------
*
*	Front end form
*
*	@author Elliot Condon
*	@since 1.1.4
* 
*-------------------------------------------------------------------------------------*/

function acf_form($options = null)
{
	global $post, $acf;
	
	
	// defaults
	$defaults = array(
		'post_id' => $post->ID, // post id to get field groups from and save data to
		'field_groups' => array(), // this will find the field groups for this post
		'form_attributes' => array( // attributes will be added to the form element
			'class' => ''
		),
		'return' => add_query_arg( 'updated', 'true', get_permalink() ), // return url
		'html_field_open' => '<div class="field">', // field wrapper open
		'html_field_close' => '</div>', // field wrapper close
		'html_before_fields' => '', // html inside form before fields
		'html_after_fields' => '', // html inside form after fields
		'submit_value' => 'Update', // vale for submit field
		'updated_message' => 'Post updated.', // default updated message. Can be false
	);
	
	
	// merge defaults with options
	if($options && is_array($options))
	{
		$options = array_merge($defaults, $options);
	}
	else
	{
		$options = $defaults;
	}
	
	
	// register post box
	if(!$options['field_groups'])
	{
		$options['field_groups'] = $acf->get_input_metabox_ids(array('post_id' => $options['post_id']), false);
	}

	
	// updated message
	if(isset($_GET['updated']) && $_GET['updated'] == 'true' && $options['updated_message'])
	{
		echo '<div id="message" class="updated"><p>' . $options['updated_message'] . '</p></div>';
	}
	
	// display form
	?>
	<form action="" id="post" method="post" <?php if($options['form_attributes']){foreach($options['form_attributes'] as $k => $v){echo $k . '="' . $v .'" '; }} ?>>
	<div style="display:none">
		<input type="hidden" name="acf_save" value="true" />
		<input type="hidden" name="post_id" value="<?php echo $options['post_id']; ?>" />
		<input type="hidden" name="return" value="<?php echo $options['return']; ?>" />
		<?php wp_editor('', 'acf_settings'); ?>
	</div>
	
	<div id="poststuff">
	<?php
	
	// html before fields
	echo $defaults['html_before_fields'];
	
	$field_groups = $acf->get_field_groups();
	if($field_groups):
		foreach($field_groups as $field_group):
			
			if(!in_array($field_group['id'], $options['field_groups'])) continue;
			
			
			// defaults
			if(!$field_group['options'])
			{
				$field_group['options'] = array(
					'layout'	=>	'default'
				);
			}
			
				
			if($field_group['fields'])
			{
				echo '<div id="acf_' . $field_group['id'] . '" class="postbox acf_postbox"><div class="inside">';
					echo '<div class="options" data-layout="' . $field_group['options']['layout'] . '" data-show="true"></div>';
					$acf->render_fields_for_input($field_group['fields'], $options['post_id']);
				echo '</div></div>';
			}
			
		endforeach;
	endif;
	
	// html after fields
	echo $defaults['html_after_fields'];
	
	?>
	<!-- Submit -->
	<div class="field">
		<input type="submit" value="<?php echo $options['submit_value']; ?>" />
	</div>
	<!-- / Submit -->

	</div><!-- <div id="poststuff"> -->
	</form>
	<?php
	
}


/*--------------------------------------------------------------------------------------
*
*	update_field
*
*	@author Elliot Condon
*	@since 3.1.9
* 
*-------------------------------------------------------------------------------------*/

function update_field($field_name, $value, $post_id = false)
{
	global $post, $acf; 
	 
	if(!$post_id) 
	{ 
		$post_id = $post->ID; 
	}
	
	
	// allow for option == options
	if( $post_id == "option" )
	{
		$post_id = "options";
	}
	 
	 
	// get value
	$field_key = "";
	if( is_numeric($post_id) )
	{
		$field_key = get_post_meta($post_id, '_' . $field_name, true); 
	}
	else
	{
		$field_key = get_option('_' . $post_id . '_' . $field_name); 
	}

	
	// create default field to save the data as plain text
	$field = array(
		'type' => 'text',
		'name' => $field_name,
		'key' => ''
	);
	
	if($field_key != "") 
	{ 
		// we can load the field properly! 
		$field = $acf->get_acf_field($field_key); 
	}
	
	
	// sub fields? They need formatted data
	if( isset($field['sub_fields']) )
	{
		// define sub field keys
		$sub_field_keys = array();
		if( $field['sub_fields'] )
		{
			foreach( $field['sub_fields'] as $sub_field )
			{
				$sub_field_keys[ $sub_field['name'] ] = $sub_field['key'];
			}
		}
		
		
		// loop through the values and format the array to use sub field keys
		if( $value )
		{
			foreach( $value as $row_i => $row)
			{
				if( $row )
				{
					foreach( $row as $sub_field_name => $sub_field_value )
					{
						
						if( isset($sub_field_keys[$sub_field_name]) )
						{
							// change the array key from "sub_field_name" to "sub_field_key"
							$value[$row_i][ $sub_field_keys[$sub_field_name] ] = $sub_field_value;
							
							unset( $value[$row_i][$sub_field_name] );
						}
						
					}
					// foreach( $row as $sub_field_name => $sub_field_value )
				}
				// if( $row )
			}
			// foreach( $value as $row_i => $row)
		}
		// if( $value )

	}
	
	
	
	
	$acf->update_value($post_id, $field, $value);
}


/*
*  Depreceated Functions
*
*  @description: 
*  @created: 23/07/12
*/


/*--------------------------------------------------------------------------------------
*
*	reset_the_repeater_field
*
*	@author Elliot Condon
*	@depreciated: 3.3.4 - now use has_sub_field
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function reset_the_repeater_field()
{
	// do nothing
}


/*--------------------------------------------------------------------------------------
*
*	the_repeater_field
*
*	@author Elliot Condon
*	@depreciated: 3.3.4 - now use has_sub_field
*	@since 1.0.3
* 
*-------------------------------------------------------------------------------------*/

function the_repeater_field($field_name, $post_id = false)
{
	return has_sub_field($field_name, $post_id);
}


/*--------------------------------------------------------------------------------------
*
*	the_flexible_field
*
*	@author Elliot Condon
*	@depreciated: 3.3.4 - now use has_sub_field
*	@since 3.?.?
* 
*-------------------------------------------------------------------------------------*/

function the_flexible_field($field_name, $post_id = false)
{
	return has_sub_field($field_name, $post_id);
}


?>