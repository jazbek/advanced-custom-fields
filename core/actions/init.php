<?php

/*
 * Settup Fields
 * this function will create an array of field objects, including custom registered field types
 */
 
$this->setup_fields();
		
/*
 * Upgrade
 * test the current acf version against the version in the main file
 * If this is a newer version, show the upgrade database message
 */
 
$version = get_option('acf_version', false);

if( $version )
{
	if( $version < $this->upgrade_version )
	{
		$this->admin_message('<p>' . __("Advanced Custom Fields",'acf') . 'v' . $this->version . ' ' . __("requires a database upgrade",'acf') .' (<a class="thickbox" href="' . admin_url() . 'plugin-install.php?tab=plugin-information&plugin=advanced-custom-fields&section=changelog&TB_iframe=true&width=640&height=559">' . __("why?",'acf') .'</a>). ' . __("Please",'acf') .' <a href="http://codex.wordpress.org/Backing_Up_Your_Database">' . __("backup your database",'acf') .'</a>, '. __("then click",'acf') . ' <a href="' . admin_url() . 'edit.php?post_type=acf&page=acf-upgrade" class="button">' . __("Upgrade Database",'acf') . '</a></p>');
		
	}
}
else
{
	update_option('acf_version', $this->version );
}

/*
 * Deactivate add-on
 * called from page_settings, this code will deactivate an add-on
 */
 
if(isset($_POST['acf_field_deactivate']))
{
	// vars
	$message = "";
	$field = $_POST['acf_field_deactivate'];
	
	// delete field
	delete_option('acf_'.$field.'_ac');
	
	//set message
	if($field == "repeater")
	{
		$message = '<p>' . __("Repeater field deactivated",'acf') . '</p>';
	}
	elseif($field == "options_page")
	{
		$message = '<p>' . __("Options page deactivated",'acf') . '</p>';
	}
	elseif($field == "flexible_content")
	{
		$message = '<p>' . __("Flexible Content field deactivated",'acf') . '</p>';
	}
	elseif($field == "everything_fields")
	{
		$message = '<p>' . __("Everything Fields deactivated",'acf') . '</p>';
	}
	
	// show message on page
	$this->admin_message($message);
}

/*
 * Actviate add-on
 * called from page_settings, this code will activate an add-on
 */
 
 if(isset($_POST['acf_field_activate']) && isset($_POST['key']))
{
	// vars
	$message = "";
	$field = $_POST['acf_field_activate'];
	$key = trim($_POST['key']);

	// update option
	update_option('acf_'.$field.'_ac', $key);
	
	// did it unlock?
	if($this->is_field_unlocked($field))
	{
		//set message
		if($field == "repeater")
		{
			$message = '<p>' . __("Repeater field activated",'acf') . '</p>';
		}
		elseif($field == "options_page")
		{
			$message = '<p>' . __("Options page activated",'acf') . '</p>';
		}
		elseif($field == "flexible_content")
		{
			$message = '<p>' . __("Flexible Content field activated",'acf') . '</p>';
		}
		elseif($field == "everything_fields")
		{
			$message = '<p>' . __("Everything Fields activated",'acf') . '</p>';
		}
	}
	else
	{
		$message = '<p>' . __("License key unrecognised",'acf') . '</p>';
	}
	
	$this->admin_message($message);
}

/*
 * Create ACF Post Type
 */
 
$labels = array(
    'name' => __( 'Field&nbsp;Groups', 'acf' ),
	'singular_name' => __( 'Advanced Custom Fields', 'acf' ),
    'add_new' => __( 'Add New' , 'acf' ),
    'add_new_item' => __( 'Add New Field Group' , 'acf' ),
    'edit_item' =>  __( 'Edit Field Group' , 'acf' ),
    'new_item' => __( 'New Field Group' , 'acf' ),
    'view_item' => __('View Field Group', 'acf'),
    'search_items' => __('Search Field Groups', 'acf'),
    'not_found' =>  __('No Field Groups found', 'acf'),
    'not_found_in_trash' => __('No Field Groups found in Trash', 'acf'), 
);

register_post_type('acf', array(
	'labels' => $labels,
	'public' => false,
	'show_ui' => true,
	'_builtin' =>  false,
	'capability_type' => 'page',
	'hierarchical' => true,
	'rewrite' => false,
	'query_var' => "acf",
	'supports' => array(
		'title',
	),
	'show_in_menu'	=> false,
));


/*
 * Messages for ACF
 */
 
function acf_post_updated_messages( $messages )
{
	global $post, $post_ID;

	$messages['acf'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __('Field group updated.', 'acf'),
		2 => __('Custom field updated.', 'acf'),
		3 => __('Custom field deleted.', 'acf'),
		4 => __('Field group updated.', 'acf'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Field group restored to revision from %s', 'acf'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => __('Field group published.', 'acf'),
		7 => __('Field group saved.', 'acf'),
		8 => __('Field group submitted.', 'acf'),
		9 => __('Field group scheduled for.', 'acf'),
		10 => __('Field group draft updated.', 'acf'),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'acf_post_updated_messages' );


/*
 * Set Custom Columns
 * for the acf edit field groups page
 */

add_filter("manage_edit-acf_columns", "acf_columns_filter");
function acf_columns_filter($columns)
{
	$columns = array(
		'cb'	 	=> '<input type="checkbox" />',
		'title' 	=> __("Title"),
	);
	return $columns;
}

?>