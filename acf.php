<?php
/*
Plugin Name: Advanced Custom Fields
Plugin URI: http://www.advancedcustomfields.com/
Description: Fully customise WordPress edit screens with powerful fields. Boasting a professional interface and a powerfull API, it’s a must have for any web developer working with WordPress.Field types include: Wysiwyg, text, textarea, image, file, select, checkbox, page link, post object, date picker, color picker and more!
Version: 3.2.3
Author: Elliot Condon
Author URI: http://www.elliotcondon.com/
License: GPL
Copyright: Elliot Condon
*/

include('core/api.php');

$acf = new Acf();

class Acf
{ 
	var $dir;
	var $path;
	var $siteurl;
	var $wpadminurl;
	var $version;
	var $upgrade_version;
	var $fields;
	var $options_page;
	var $cache;
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function Acf()
	{
		
		// set class variables
		$this->path = dirname(__FILE__).'';
		$this->dir = plugins_url('',__FILE__);
		$this->siteurl = get_bloginfo('url');
		$this->wpadminurl = admin_url();
		$this->version = '3.2.3';
		$this->upgrade_version = '3.1.8'; // this is the latest version which requires an upgrade
		$this->cache = array(); // basic array cache to hold data throughout the page load
		
		
		// set text domain
		//load_plugin_textdomain('acf', false, $this->path.'/lang' );
		load_plugin_textdomain('acf', false, basename(dirname(__FILE__)).'/lang' );
		
		
		// load options page
		$this->setup_options_page();
		$this->setup_everything_fields();
		
		
		// actions
		add_filter('pre_get_posts', array($this, 'pre_get_posts'));  
		add_action('init', array($this, 'init'));
		add_action('admin_menu', array($this,'admin_menu'));
		add_action('admin_head', array($this,'admin_head'));
		add_filter('name_save_pre', array($this, 'save_name'));
		add_action('save_post', array($this, 'save_post'));
		add_action('wp_ajax_get_input_metabox_ids', array($this, 'get_input_metabox_ids'));
		add_action('wp_ajax_get_input_style', array($this, 'the_input_style'));
		add_action('admin_footer', array($this, 'admin_footer'));
		add_action('admin_print_scripts', array($this, 'admin_print_scripts'));
		add_action('admin_print_styles', array($this, 'admin_print_styles'));
		add_action('wp_ajax_acf_upgrade', array($this, 'upgrade_ajax'));
		add_action('wp_ajax_acf_field_options', array($this, 'ajax_acf_field_options'));
		add_action('wp_ajax_acf_input', array($this, 'ajax_acf_input'));
		add_action('wp_ajax_acf_location', array($this, 'ajax_acf_location'));
		
		
		// custom actions (added in 3.1.8)
		add_action('acf_head-input', array($this, 'acf_head_input'));
		add_action('acf_print_scripts-input', array($this, 'acf_print_scripts_input'));
		add_action('acf_print_styles-input', array($this, 'acf_print_styles_input'));
		
		
		return true;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_cache
	*
	*	@author Elliot Condon
	*	@since 3.1.9
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_cache($key = false)
	{
		// key is required
		if( !$key )
			return false;
		
		
		// does cache at key exist?
		if( !isset($this->cache[$key]) )
			return false;
		
		
		// return cahced item
		return $this->cache[$key];
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	set_cache
	*
	*	@author Elliot Condon
	*	@since 3.1.9
	* 
	*-------------------------------------------------------------------------------------*/
	
	function set_cache($key = false, $value = null)
	{
		// key is required
		if( !$key )
			return false;
		
		
		// update the cache array
		$this->cache[$key] = $value;
		
		
		// return true. Probably not needed
		return true;
	}
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	pre_get_posts
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function pre_get_posts($query)
	{
		global $pagenow;
		if($pagenow == "edit.php" && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'acf')
		{
			$query->query_vars['posts_per_page'] = 99;  
    	}
    	return $query; 
	}
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	setup_fields
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function setup_fields()
	{
		// vars
		$return = array();
		
		// include parent field
		include_once('core/fields/acf_field.php');
		
		// include child fields
		include_once('core/fields/acf_field.php');
		include_once('core/fields/text.php');
		include_once('core/fields/textarea.php');
		include_once('core/fields/wysiwyg.php');
		include_once('core/fields/image.php');
		include_once('core/fields/file.php');
		include_once('core/fields/select.php');
		include_once('core/fields/checkbox.php');
		include_once('core/fields/radio.php');
		include_once('core/fields/true_false.php');
		include_once('core/fields/page_link.php');
		include_once('core/fields/post_object.php');
		include_once('core/fields/relationship.php');
		include_once('core/fields/date_picker/date_picker.php');
		include_once('core/fields/color_picker.php');
		
		$return['text'] = new acf_Text($this); 
		$return['textarea'] = new acf_Textarea($this); 
		$return['wysiwyg'] = new acf_Wysiwyg($this); 
		$return['image'] = new acf_Image($this); 
		$return['file'] = new acf_File($this); 
		$return['select'] = new acf_Select($this); 
		$return['checkbox'] = new acf_Checkbox($this);
		$return['radio'] = new acf_Radio($this);
		$return['true_false'] = new acf_True_false($this);
		$return['page_link'] = new acf_Page_link($this);
		$return['post_object'] = new acf_Post_object($this);
		$return['relationship'] = new acf_Relationship($this);
		$return['date_picker'] = new acf_Date_picker($this);
		$return['color_picker'] = new acf_Color_picker($this);
		
		if($this->is_field_unlocked('repeater'))
		{
			include_once('core/fields/repeater.php');
			$return['repeater'] = new acf_Repeater($this);
		}
		
		if($this->is_field_unlocked('flexible_content'))
		{
			include_once('core/fields/flexible_content.php');
			$return['flexible_content'] = new acf_flexible_content($this);
		}
		
		// hook to load in third party fields
		$custom = apply_filters('acf_register_field',array());
		
		if(!empty($custom))
		{
			foreach($custom as $v)
			{
				//var_dump($v['url']);
				include($v['url']);
				$name = $v['class'];
				$custom_field = new $name($this);
				$return[$custom_field->name] = $custom_field;
			}
		}
		
		$this->fields = $return;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	setup_options_page
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function setup_options_page()
	{
		include_once('core/options_page.php');
		$this->options_page = new Options_page($this);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	setup_everything_fields
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function setup_everything_fields()
	{
		include_once('core/everything_fields.php');
		$this->everything_fields = new Everything_fields($this);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	acf
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_menu() {
	
		// add acf page to options menu
		add_utility_page(__("Custom Fields",'acf'), __("Custom Fields",'acf'), 'manage_options', 'edit.php?post_type=acf');
		add_submenu_page('edit.php?post_type=acf', __('Settings','acf'), __('Settings','acf'), 'manage_options','acf-settings',array($this,'admin_page_settings'));
		add_submenu_page('edit.php?post_type=acf', __('Upgrade','acf'), __('Upgrade','acf'), 'manage_options','acf-upgrade',array($this,'admin_page_upgrade'));
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	Init
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function init()
	{	
		include('core/actions/init.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_page_upgrade
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_page_upgrade()
	{
		include('core/admin/upgrade.php');
	}
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_page_settings
	*
	*	@author Elliot Condon
	*	@since 3.0.5
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_page_settings()
	{
		include('core/admin/page_settings.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	ajax_upgrade
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function upgrade_ajax()
	{	
		include('core/admin/upgrade_ajax.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_print_scripts
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_print_scripts() {
		
		// thickbox
		if($GLOBALS['pagenow'] == 'edit.php' && isset($GLOBALS['post_type']) && $GLOBALS['post_type'] == 'acf')
		{
			wp_enqueue_script( 'jquery' );
    		wp_enqueue_script( 'thickbox' );
		}
		
		if(in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php')))
		{
			if($GLOBALS['post_type'] == 'acf')
			{
				// remove autosave from acf post type
				wp_dequeue_script( 'autosave' );
				do_action('acf_print_scripts-fields');
			}
			else
			{
				do_action('acf_print_scripts-input');
			}
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_print_styles
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_print_styles() {
		
		// thickbox
		if($GLOBALS['pagenow'] == 'edit.php' && isset($GLOBALS['post_type']) && $GLOBALS['post_type'] == 'acf')
		{
			wp_enqueue_style( 'thickbox' );
		}
		
		if(in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php')))
		{
			if($GLOBALS['post_type'] == 'acf')
			{
				do_action('acf_print_styles-fields');
			}
			else
			{
				do_action('acf_print_styles-input');
			}
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	acf_print_scripts
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function acf_print_scripts_input()
	{
		foreach($this->fields as $field)
		{
			$this->fields[$field->name]->admin_print_scripts();
		}
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	acf_print_styles
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function acf_print_styles_input()
	{
		foreach($this->fields as $field)
		{
			$this->fields[$field->name]->admin_print_styles();
		}
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{
		// vars
		global $post, $pagenow;
		
		// hide upgrade page from nav
		echo '<style type="text/css"> 
			#toplevel_page_edit-post_type-acf a[href="edit.php?post_type=acf&page=acf-upgrade"]{ display:none; }
			#toplevel_page_edit-post_type-acf .wp-menu-image { background: url("../wp-admin/images/menu.png") no-repeat scroll 0 -33px transparent; }
			#toplevel_page_edit-post_type-acf:hover .wp-menu-image { background-position: 0 -1px; }
			#toplevel_page_edit-post_type-acf .wp-menu-image img { display:none; }
		</style>';
		

		// only add to edit pages
		if( !in_array($pagenow, array('post.php', 'post-new.php')) )
		{
			return false;
		}
		
		
		// edit field
		if($GLOBALS['post_type'] == 'acf')
		{
			
			// add acf fields js + css
			echo '<script type="text/javascript" src="'.$this->dir.'/js/fields.js?ver=' . $this->version . '" ></script>';
			echo '<link rel="stylesheet" type="text/css" href="'.$this->dir.'/css/global.css?ver=' . $this->version . '" />';
			echo '<link rel="stylesheet" type="text/css" href="'.$this->dir.'/css/fields.css?ver=' . $this->version . '" />';
			
			
			// add user js + css
			do_action('acf_head-fields');
			
			
			// add metaboxes
			add_meta_box('acf_fields', __("Fields",'acf'), array($this, 'meta_box_fields'), 'acf', 'normal', 'high');
			add_meta_box('acf_location', __("Location",'acf') . ' </span><span class="description">- ' . __("Add Fields to Edit Screens",'acf'), array($this, 'meta_box_location'), 'acf', 'normal', 'high');
			add_meta_box('acf_options', __("Options",'acf') . '</span><span class="description">- ' . __("Customise the edit page",'acf'), array($this, 'meta_box_options'), 'acf', 'normal', 'high');
		
		}
		else
		{
			$post_type = get_post_type($post);
			
			// get style for page
			$metabox_ids = $this->get_input_metabox_ids(array('post_id' => $post->ID), false);
			$style = isset($metabox_ids[0]) ? $this->get_input_style($metabox_ids[0]) : '';
			echo '<style type="text/css" id="acf_style" >' .$style . '</style>';
			

			// Style
			echo '<link rel="stylesheet" type="text/css" href="'.$this->dir.'/css/global.css?ver=' . $this->version . '" />';
			echo '<link rel="stylesheet" type="text/css" href="'.$this->dir.'/css/input.css?ver=' . $this->version . '" />';
			echo '<style type="text/css">.acf_postbox, .postbox[id*="acf_"] { display: none; }</style>';
			
			
			// find user editor setting
			$user = wp_get_current_user();
			$editor_mode = get_user_setting('editor', 'tinymce');
			
			
			// Javascript
			echo '<script type="text/javascript" src="'.$this->dir.'/js/input-actions.js?ver=' . $this->version . '" ></script>';
			echo '<script type="text/javascript" src="'.$this->dir.'/js/input-ajax.js?ver=' . $this->version . '" ></script>';
			echo '<script type="text/javascript">
				acf.validation_message = "' . __("Validation Failed. One or more fields below are required.",'acf') . '";
				acf.post_id = ' . $post->ID . ';
				acf.editor_mode = "' . $editor_mode . '";
				acf.admin_url = "' . admin_url() . '";
			</script>';
			
			
			// add user js + css
			do_action('acf_head-input');
			
			
			// get acf's
			$acfs = $this->get_field_groups();
			if($acfs)
			{
				foreach($acfs as $acf)
				{
					// hide / show
					$show = in_array($acf['id'], $metabox_ids) ? "true" : "false";
					
					// add meta box
					add_meta_box(
						'acf_' . $acf['id'], 
						$acf['title'], 
						array($this, 'meta_box_input'), 
						$post_type, 
						$acf['options']['position'], 
						'high', 
						array( 'fields' => $acf['fields'], 'options' => $acf['options'], 'show' => $show, 'post_id' => $post->ID )
					);
				}
			}
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	acf_head_input
	*
	*	This is fired from an action: acf_head-input
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function acf_head_input()
	{
		foreach($this->fields as $field)
		{
			$this->fields[$field->name]->admin_head();
		}
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_field_groups
	*
	*	This function returns an array of post objects found in the get_pages and the 
	*	register_field_group calls.
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_field_groups()
	{
		// return cache
		$cache = $this->get_cache('acf_field_groups');
		if($cache != false)
		{
			return $cache;
		}
		
		// vars
		$acfs = array();
		
		// get acf's
		$result = get_pages(array(
			'numberposts' 	=> 	-1,
			'post_type'		=>	'acf',
			'sort_column' => 'menu_order',
			'order' => 'ASC',
		));
		
		// populate acfs
		if($result)
		{
			foreach($result as $acf)
			{
				$acfs[] = array(
					'id' => $acf->ID,
					'title' => get_the_title($acf->ID),
					'fields' => $this->get_acf_fields($acf->ID),
					'location' => $this->get_acf_location($acf->ID),
					'options' => $this->get_acf_options($acf->ID),
					'menu_order' => $acf->menu_order,
				);
			}
		}
		
		// hook to load in registered field groups
		$acfs = apply_filters('acf_register_field_group', $acfs);
		
		// update cache
		$this->set_cache('acf_field_groups', $acfs);
		
		// return
		if(empty($acfs))
		{
			return false;
		}
		return $acfs;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_footer
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_footer()
	{
		// acf edit list
		if($GLOBALS['pagenow'] == 'edit.php' && isset($GLOBALS['post_type']) && $GLOBALS['post_type'] == 'acf')
		{
			include('core/admin/page_acf.php');
		}

	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	meta_box_fields
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function meta_box_fields()
	{
		include('core/admin/meta_box_fields.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	meta_box_location
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function meta_box_location()
	{
		include('core/admin/meta_box_location.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	meta_box_options
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function meta_box_options()
	{
		include('core/admin/meta_box_options.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	meta_box_input
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function meta_box_input($post, $args)
	{
		include('core/admin/meta_box_input.php');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_acf_fields
	*	- returns an array of fields for a acf object
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/

	function get_acf_fields($post_id)
	{
		// vars
		$return = array();
		$keys = get_post_custom_keys($post_id);
		
		if($keys)
		{
			foreach($keys as $key)
			{
				if(strpos($key, 'field_') !== false)
				{
					$field = $this->get_acf_field($key, $post_id);
	
			 		$return[$field['order_no']] = $field;
				}
			}
		 	
		 	ksort($return);
	 	}
	 	// return fields
		return $return;
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_acf_field
	*	- returns a field
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/

	function get_acf_field($field_name, $post_id = false)
	{
		// vars
		$post_id = $post_id ? $post_id : $this->get_post_meta_post_id($field_name);
		$field = false;
		
		// if this acf ($post_id) is trashed don't use it's fields
		if(get_post_status($post_id) != "trash")
		{
			$field = get_post_meta($post_id, $field_name, true);
		}
 		
 		// field could be registered via php, and not in db at all!
 		if(!$field)
 		{ 			
 			// hook to load in registered field groups
			$acfs = apply_filters('acf_register_field_group', array());
			if($acfs)
			{
				// loop through acfs
				foreach($acfs as $acf)
				{
					// loop through fields
					if($acf['fields'])
					{
						foreach($acf['fields'] as $field)
						{
							if($field['key'] == $field_name)
							{
								return $field;
							}
						}
					}
					// if($acf['fields'])
				}
				// foreach($acfs as $acf)
			}
			// if($acfs)
 		}
 		// if(!$field)
 		
 		return $field;
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_post_meta_post_id
	*	- returns the post_id for a meta_key
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/

	function get_post_meta_post_id($field_name)
	{
		global $wpdb;
		$post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $field_name) );
		
		if($post_id) return (int)$post_id;
		 
		return false;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		if(!isset($this->fields[$field['type']]) || !is_object($this->fields[$field['type']]))
		{
			_e('Error: Field Type does not exist!','acf');
			return false;
		}
		
		// defaults
		if(!isset($field['class'])) $field['class'] = $field['type'];
		
		$this->fields[$field['type']]->create_field($field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_acf_location
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_acf_location($post_id)
	{
		// vars
		$return = array(
	 		'rules'		=>	array(),
	 		'allorany'	=>	get_post_meta($post_id, 'allorany', true) ? get_post_meta($post_id, 'allorany', true) : 'all', 
	 	);
		
		// get all fields
	 	$rules = get_post_meta($post_id, 'rule', false);
	 	
	 	if($rules)
	 	{
		 	foreach($rules as $rule)
		 	{
		 		$return['rules'][$rule['order_no']] = $rule;
		 	}
	 	}
	 	
	 	ksort($return['rules']);
	 	
	 	// return fields
		return $return;
	 	
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_acf_options
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_acf_options($post_id)
	{
		// defaults
	 	$options = array(
	 		'position'		=>	get_post_meta($post_id, 'position', true) ? get_post_meta($post_id, 'position', true) : 'normal',
	 		'layout'		=>	get_post_meta($post_id, 'layout', true) ? get_post_meta($post_id, 'layout', true) : 'default',
	 		'show_on_page'	=>	get_post_meta($post_id, 'show_on_page', true) ? get_post_meta($post_id, 'show_on_page', true) : array(),
	 	);
	 	
	 	// If this is a new acf, there will be no custom keys!
	 	if(!get_post_custom_keys($post_id))
	 	{
	 		$options['show_on_page'] = array('the_content', 'discussion', 'custom_fields', 'comments', 'slug', 'author');
	 	}
	 	
	 	// return
	 	return $options;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	save_post
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_post($post_id)
	{	
		
		// do not save if this is an auto save routine
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
		
		// only save once! WordPress save's twice for some strange reason.
		global $acf_flag;
		if ($acf_flag != 0) return $post_id;
		$acf_flag = 1;
		
		// set post ID if is a revision		
		if(wp_is_post_revision($post_id)) 
		{
			$post_id = wp_is_post_revision($post_id);
		}
		
		// include save files
		if(isset($_POST['save_fields']) &&  $_POST['save_fields'] == 'true') include('core/actions/save_fields.php');
		if(isset($_POST['save_input']) &&  $_POST['save_input'] == 'true') include('core/actions/save_input.php');
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	save_name
	*	- this function intercepts the acf post obejct and adds an "acf_" to the start of 
	*	it's name to stop conflicts between acf's and page's urls
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_name($name)
	{
        if (isset($_POST['post_type']) && $_POST['post_type'] == 'acf') 
        {
			$name = 'acf_' . sanitize_title_with_dashes($_POST['post_title']);
        }
        return $name;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value($post_id, $field)
	{
		if(!isset($this->fields[$field['type']]) || !is_object($this->fields[$field['type']]))
		{
			return '';
		}
		
		return $this->fields[$field['type']]->get_value($post_id, $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field)
	{
		if(!isset($this->fields[$field['type']]) || !is_object($this->fields[$field['type']]))
		{
			return '';
		}
		
		return $this->fields[$field['type']]->get_value_for_api($post_id, $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	update_value
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function update_value($post_id, $field, $value)
	{
		$this->fields[$field['type']]->update_value($post_id, $field, $value);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	update_field
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function update_field($post_id, $field)
	{
		// format the field (select, repeater, etc)
		$field = $this->pre_save_field($field);
		
		// save it!
		update_post_meta($post_id, $field['key'], $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	pre_save_field
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function pre_save_field($field)
	{
		// format the field (select, repeater, etc)
		return $this->fields[$field['type']]->pre_save_field($field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	format_value_for_input
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	//function format_value_for_input($value, $field)
	//{
	//	return $this->fields[$field['type']]->format_value_for_input($value, $field);
	//}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	format_value_for_api
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function format_value_for_api($value, $field)
	{
		if(!isset($this))
		{
			// called form api!
			
		}
		else
		{
			// called from object
		}
		return $this->fields[$field['type']]->format_value_for_api($value, $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_format_data
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_format_data($field)
	{
		return $this->fields[$field['type']]->create_format_data($field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_input_metabox_ids
	*	- called by function.fields to hide / show metaboxes
	*	
	*	@author Elliot Condon
	*	@since 2.0.5
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_input_metabox_ids($overrides = array(), $json = true)
	{
		// overrides
		if(isset($_POST))
		{
			if(isset($_POST['post_id']) && $_POST['post_id'] != 'false') $overrides['post_id'] = $_POST['post_id'];
			if(isset($_POST['page_template']) && $_POST['page_template'] != 'false') $overrides['page_template'] = $_POST['page_template'];
			if(isset($_POST['page_parent']) && $_POST['page_parent'] != 'false') $overrides['page_parent'] = $_POST['page_parent'];
			if(isset($_POST['page_type']) && $_POST['page_type'] != 'false') $overrides['page_type'] = $_POST['page_type'];
			if(isset($_POST['page']) && $_POST['page'] != 'false') $overrides['page'] = $_POST['page'];
			if(isset($_POST['post']) && $_POST['post'] != 'false') $overrides['post'] = $_POST['post'];
			if(isset($_POST['post_category']) && $_POST['post_category'] != 'false') $overrides['post_category'] = $_POST['post_category'];
			if(isset($_POST['post_format']) && $_POST['post_format'] != 'false') $overrides['post_format'] = $_POST['post_format'];
			if(isset($_POST['taxonomy']) && $_POST['taxonomy'] != 'false') $overrides['taxonomy'] = $_POST['taxonomy'];
		}
		
		// create post object to match against
		$post = isset($overrides['post_id']) ? get_post($_POST['post_id']) : false;
		
		// find all acf objects
		$acfs = $this->get_field_groups();
		
		// blank array to hold acfs
		$return = array();
		
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				$add_box = false;

				if($acf['location']['allorany'] == 'all')
				{
					// ALL
					$add_box = true;
					
					if($acf['location']['rules'])
					{
						foreach($acf['location']['rules'] as $rule)
						{
							
							// if any rules dont return true, dont add this acf
							if(!$this->match_location_rule($post, $rule, $overrides))
							{
								$add_box = false;
							}
						}
					}
					
				}
				elseif($acf['location']['allorany'] == 'any')
				{
					// ANY
					
					$add_box = false;
					
					if($acf['location']['rules'])
					{
						foreach($acf['location']['rules'] as $rule)
						{
							// if any rules return true, add this acf
							if($this->match_location_rule($post, $rule, $overrides))
							{
								$add_box = true;
							}
						}
					}
				}
							
				if($add_box == true)
				{
					$return[] = $acf['id'];
				}
				
			}
		}
		
		if($json)
		{
			echo json_encode($return);
			die;
		}
		else
		{
			return $return;
		}
		
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_input_style
	*	- called by admin_head to generate acf css style (hide other metaboxes)
	*	
	*	@author Elliot Condon
	*	@since 2.0.5
	*	@updated 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_input_style($acf_id = false)
	{
		// vars
		$acfs = $this->get_field_groups();
		$html = "";
		
		// find acf
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				if($acf['id'] == $acf_id)
				{
					// add style to html 
					if(!in_array('the_content',$acf['options']['show_on_page']))
					{
						$html .= '#postdivrich {display: none;} ';
					}
					if(!in_array('custom_fields',$acf['options']['show_on_page']))
					{
						$html .= '#postcustom, #screen-meta label[for=postcustom-hide] { display: none; } ';
					}
					if(!in_array('discussion',$acf['options']['show_on_page']))
					{
						$html .= '#commentstatusdiv, #screen-meta label[for=commentstatusdiv-hide] {display: none;} ';
					}
					if(!in_array('comments',$acf['options']['show_on_page']))
					{
						$html .= '#commentsdiv, #screen-meta label[for=commentsdiv-hide] {display: none;} ';
					}
					if(!in_array('slug',$acf['options']['show_on_page']))
					{
						$html .= '#slugdiv, #screen-meta label[for=slugdiv-hide] {display: none;} ';
					}
					if(!in_array('author',$acf['options']['show_on_page']))
					{
						$html .= '#authordiv, #screen-meta label[for=authordiv-hide] {display: none;} ';
					}
					
					break;
				}
				// if($acf['id'] == $acf_id)
			}
			// foreach($acfs as $acf)
		}
		//if($acfs)
		
		return $html;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	the_input_style
	*	- called by function.fields to hide / show other metaboxes
	*	
	*	@author Elliot Condon
	*	@since 2.0.5
	* 
	*-------------------------------------------------------------------------------------*/
	
	function the_input_style()
	{
		// overrides
		if(isset($_POST['acf_id']))
		{
			echo $this->get_input_style($_POST['acf_id']);
		}
		
		die;
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	match_location_rule
	*
	*	@author Elliot Condon
	*	@since 2.0.0
	* 
	*-------------------------------------------------------------------------------------*/

	function match_location_rule($post = null, $rule = array(), $overrides = array())
	{
		
		// no post? Thats okay if you are one of the bellow exceptions. Otherwise, return false
		if(!$post)
		{
			$exceptions = array(
				'user_type',
				'options_page',
				'ef_taxonomy',
				'ef_user',
				'ef_media',
			);
			
			if( !in_array($rule['param'], $exceptions) )
			{
				return false;
			}
		}
		
		
		if(!isset($rule['value']))
		{
			return false;
		}
		
		
		switch ($rule['param']) {
		
			// POST TYPE
		    case "post_type":
		    
		    	$post_type = isset($overrides['post_type']) ? $overrides['post_type'] : get_post_type($post);
		        
		        if($rule['operator'] == "==")
		        {
		        	if($post_type == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($post_type != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
		    // PAGE
		    case "page":
		        
		        $page = isset($overrides['page']) ? $overrides['page'] : $post->ID;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($page == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($page != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
			// PAGE
		    case "page_type":
		        
		        $page_type = isset($overrides['page_type']) ? $overrides['page_type'] : $post->post_parent;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($rule['value'] == "parent" && $page_type == "0")
		        	{
		        		return true; 
		        	}
		        	
		        	if($rule['value'] == "child" && $page_type != "0")
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($rule['value'] == "parent" && $page_type != "0")
		        	{
		        		return true; 
		        	}
		        	
		        	if($rule['value'] == "child" && $page_type == "0")
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
		    // PAGE PARENT
		    case "page_parent":
		        
		        $page_parent = isset($overrides['page_parent']) ? $overrides['page_parent'] : $post->post_parent;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($page_parent == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        	
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($page_parent != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		    
		    // PAGE
		    case "page_template":
		        
		        $page_template = isset($overrides['page_template']) ? $overrides['page_template'] : get_post_meta($post->ID,'_wp_page_template',true);
		        
		        if($rule['operator'] == "==")
		        {
		        	if($page_template == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	if($rule['value'] == "default" && !$page_template)
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($page_template != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		       
		    // POST
		    case "post":
		        
		        $post_id = isset($overrides['post']) ? $overrides['post'] : $post->ID;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($post_id == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($post_id != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
		    // POST CATEGORY
		    case "post_category":
		        
		        $cats = array();
		        
		        if(isset($overrides['post_category']))
		        {
		        	$cats = $overrides['post_category'];
		        }
		        else
		        {
		        	$all_cats = get_the_category($post->ID);
		        	foreach($all_cats as $cat)
					{
						$cats[] = $cat->term_id;
					}
		        }
		        if($rule['operator'] == "==")
		        {
		        	if($cats)
					{
						if(in_array($rule['value'], $cats))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($cats)
					{
						if(!in_array($rule['value'], $cats))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		        
		        break;
			
			
			// USER TYPE
		    case "user_type":
		        		
		        if($rule['operator'] == "==")
		        {
		        	if(current_user_can($rule['value']))
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if(!current_user_can($rule['value']))
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		    
		    // Options Page
		    case "options_page":
		
				if(!function_exists('get_admin_page_title'))
				{
					return false;
				}
				
		        if($rule['operator'] == "==")
		        {
		        	if(get_admin_page_title() == $rule['value'])
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if(get_admin_page_title() != $rule['value'])
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		    
		    
		    // Post Format
		    case "post_format":
		        
		       	
		       	$post_format = isset($overrides['post_format']) ? $overrides['post_format'] : get_post_format( $post->ID );
		        if($post_format == "0") $post_format = "standard";
		        
		        if($rule['operator'] == "==")
		        {
		        	if($post_format == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($post_format != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        
		        break;
		    
		    // Taxonomy
		    case "taxonomy":
		        
		        $terms = array();

		        if(isset($overrides['taxonomy']))
		        {
		        	$terms = $overrides['taxonomy'];
		        }
		        else
		        {
		        	$taxonomies = get_object_taxonomies($post->post_type);
		        	if($taxonomies)
		        	{
			        	foreach($taxonomies as $tax)
						{
							$all_terms = get_the_terms($post->ID, $tax);
							if($all_terms)
							{
								foreach($all_terms as $all_term)
								{
									$terms[] = $all_term->term_id;
								}
							}
						}
					}
		        }
		        
		        if($rule['operator'] == "==")
		        {
		        	if($terms)
					{
						if(in_array($rule['value'], $terms))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		       elseif($rule['operator'] == "!=")
		        {
		        	if($terms)
					{
						if(!in_array($rule['value'], $terms))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		        
		        
		        break;
			
			// Everything Fields: Taxonomy
		    case "ef_taxonomy":
		       	
		       	if( !isset($overrides['ef_taxonomy']) )
		       	{
		       		return false;
		       	}
		       	
		       	$ef_taxonomy = $overrides['ef_taxonomy'];
				
		        if($rule['operator'] == "==")
		        {
		        	if( $ef_taxonomy == $rule['value'] || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( $ef_taxonomy != $rule['value'] || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        
		        
		        break;
			
			// Everything Fields: User
		    case "ef_user":
		       	
		       	if( !isset($overrides['ef_user']) )
		       	{
		       		return false;
		       	}
		       	
		       	$ef_user = $overrides['ef_user'];
				
		        if($rule['operator'] == "==")
		        {
		        	if( user_can($ef_user, $rule['value']) || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( user_can($ef_user, $rule['value']) || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        
		        
		        break;
			
			// Everything Fields: Media
		    case "ef_media":
		       	
		       	if( !isset($overrides['ef_media']) )
		       	{
		       		return false;
		       	}
		       	
		       	$ef_media = $overrides['ef_media'];
				
		        if($rule['operator'] == "==")
		        {
		        	if( $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        
		        
		        break;
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	is_field_unlocked
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function is_field_unlocked($field_name)
	{
		switch ($field_name) {
		    case 'repeater':
		    	if(md5($this->get_license_key($field_name)) == "bbefed143f1ec106ff3a11437bd73432"){ return true; }else{ return false; }
		        break;
		    case 'options_page':
		        if(md5($this->get_license_key($field_name)) == "1fc8b993548891dc2b9a63ac057935d8"){ return true; }else{ return false; }
		        break;
		    case 'flexible_content':
		    	if(md5($this->get_license_key($field_name)) == "d067e06c2b4b32b1c1f5b6f00e0d61d6"){ return true; }else{ return false; }
		    	break;
		    case 'everything_fields':
		    	if(md5($this->get_license_key($field_name)) == "b6ecc9cd639f8f17d061b3eccad49b75"){ return true; }else{ return false; }
		    	break;
	    }
	}
	
	/*--------------------------------------------------------------------------------------
	*
	*	is_field_unlocked
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_license_key($field_name)
	{
		return get_option('acf_' . $field_name . '_ac');
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_message
	*
	*	@author Elliot Condon
	*	@since 2.0.5
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_message($message = "", $type = 'updated')
	{
		$GLOBALS['acf_mesage'] = $message;
		$GLOBALS['acf_mesage_type'] = $type;
		
		add_action('admin_notices', array($this, 'acf_admin_notice'));
	}
	
	function acf_admin_notice()
	{
	    echo '<div class="' . $GLOBALS['acf_mesage_type'] . '" id="message">'.$GLOBALS['acf_mesage'].'</div>';
	}
		
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_taxonomies_for_select
	*
	*---------------------------------------------------------------------------------------
	*
	*	returns a multidimentional array of taxonomies grouped by the post type / taxonomy
	*
	*	@author Elliot Condon
	*	@since 3.0.2
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_taxonomies_for_select()
	{
		$post_types = get_post_types();
		$choices = array();
		
		if($post_types)
		{
			foreach($post_types as $post_type)
			{
				$post_type_object = get_post_type_object($post_type);
				$taxonomies = get_object_taxonomies($post_type);
				if($taxonomies)
				{
					foreach($taxonomies as $taxonomy)
					{
						if(!is_taxonomy_hierarchical($taxonomy)) continue;
						$terms = get_terms($taxonomy, array('hide_empty' => false));
						if($terms)
						{
							foreach($terms as $term)
							{
								$choices[$post_type_object->label . ': ' . $taxonomy][$term->term_id] = $term->name; 
							}
						}
					}
				}
			}
		}
		
		return $choices;
	}
	
	
	function in_taxonomy($post, $ids)
	{
		$terms = array();
		
        $taxonomies = get_object_taxonomies($post->post_type);
    	if($taxonomies)
    	{
        	foreach($taxonomies as $tax)
			{
				$all_terms = get_the_terms($post->ID, $tax);
				if($all_terms)
				{
					foreach($all_terms as $all_term)
					{
						$terms[] = $all_term->term_id;
					}
				}
			}
		}
        
        if($terms)
		{
			if(is_array($ids))
			{
				foreach($ids as $id)
				{
					if(in_array($id, $terms))
					{
						return true; 
					}
				}
			}
			else
			{
				if(in_array($ids, $terms))
				{
					return true; 
				}
			}
		}
        	
        return false;
        	
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	ajax_acf_field_options
	*
	*	@author Elliot Condon
	*	@since 3.1.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function ajax_acf_field_options()
	{
		// defaults
		$defaults = array(
			'field_key' => null,
			'field_type' => null,
			'post_id' => null,
		);
		
		// load post options
		$options = array_merge($defaults, $_POST);
		
		// required
		if(!$options['field_type'])
		{
			echo "";
			die();
		}
		
		$options['field_key'] = str_replace("fields[", "", $options['field_key']);
		$options['field_key'] = str_replace("][type]", "", $options['field_key']) ;
		
		
		// load field
		//$field = $this->get_acf_field("field_" . $options['field_key'], $options['post_id']);
		$field = array();
		
		// render options
		$this->fields[$options['field_type']]->create_options($options['field_key'], $field);
		die();
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	ajax_acf_input
	*
	*	@author Elliot Condon
	*	@since 3.1.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function ajax_acf_input()
	{
		
		// defaults
		$defaults = array(
			'acf_id' => null,
			'post_id' => null,
		);
		
		// load post options
		$options = array_merge($defaults, $_POST);
		
		// required
		if(!$options['acf_id'] || !$options['post_id'])
		{
			echo "";
			die();
		}
		
		// get fields
		$fields = $this->get_acf_fields($options['acf_id']);
		
		$this->render_fields_for_input($fields, $options['post_id']);

		die();
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	render_fields_for_input
	*
	*	@author Elliot Condon
	*	@since 3.1.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function render_fields_for_input($fields, $post_id)
	{
		
		// create fields
		if($fields)
		{
			foreach($fields as $field)
			{
				// if they didn't select a type, skip this field
				if($field['type'] == 'null') continue;
				
				// set value
				$field['value'] = $this->get_value($post_id, $field);
				
				// required
				if(!isset($field['required']))
				{
					$field['required'] = "0";
				}
				
				$required_class = "";
				$required_label = "";
				
				if($field['required'] == "1")
				{
					$required_class = ' ' . __("required",'acf');
					$required_label = ' <span class="required">*</span>';
				}
				
				echo '<div id="acf-' . $field['name'] . '" class="field field-' . $field['type'] . $required_class . '">';

					echo '<p class="label">';
						echo '<label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label>';
						echo $field['instructions'];
					echo '</p>';
					
					$field['name'] = 'fields[' . $field['key'] . ']';
					$this->create_field($field);
				
				echo '</div>';
				
			}
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	ajax_acf_location
	*
	*	@author Elliot Condon
	*	@since 3.1.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function ajax_acf_location($options = array())
	{
		// defaults
		$defaults = array(
			'key' => null,
			'value' => null,
			'param' => null,
		);
		
		// Is AJAX call?
		if(isset($_POST['action']) && $_POST['action'] == "acf_location")
		{
			$options = array_merge($defaults, $_POST);
		}
		else
		{
			$options = array_merge($defaults, $options);
		}
		
		
		// some case's have the same outcome
		if($options['param'] == "page_parent")
		{
			$options['param'] = "page";
		}

		
		$choices = array();
		$optgroup = false;
		
		switch($options['param'])
		{
			case "post_type":
				
				$choices = get_post_types(array('public' => true));
				unset($choices['attachment']);
		
				break;
			
			
			case "page":
				
				$pages = get_pages('sort_column=menu_order&sort_order=desc');
				
				foreach($pages as $page)
				{
					$value = '';
					$ancestors = get_ancestors($page->ID, 'page');
					if($ancestors)
					{
						foreach($ancestors as $a)
						{
							$value .= '– ';
						}
					}
					$value .= get_the_title($page->ID);
					
					$choices[$page->ID] = $value;
					
				}
				
				break;
			
			
			case "page_type" :
				
				$choices = array(
					'parent'	=>	__("Parent Page",'acf'),
					'child'		=>	__("Child Page",'acf'),
				);
								
				break;
				
			case "page_template" :
				
				$choices = array(
					'default'	=>	__("Default Template",'acf'),
				);
				
				$templates = get_page_templates();
				foreach($templates as $k => $v)
				{
					$choices[$v] = $k;
				}
				
				break;
			
			case "post" :
				
				$posts = get_posts( array('numberposts' => '-1' ));
				foreach($posts as $v)
				{
					$choices[$v->ID] = $v->post_title;
				}
				
				break;
			
			case "post_category" :
				
				$category_ids = get_all_category_ids();
		
				foreach($category_ids as $cat_id) 
				{
				  $cat_name = get_cat_name($cat_id);
				  $choices[$cat_id] = $cat_name;
				}
				
				break;
			
			case "post_format" :
				
				$choices = get_post_format_strings();
								
				break;
			
			case "user_type" :
				
				global $wp_roles;
				
				$choices = $wp_roles->get_names();
								
				break;
			
			case "options_page" :
				
				$choices = array(
					'Options' => 'Options', 
				);
					
				$custom = apply_filters('acf_register_options_page',array());
				if(!empty($custom))
				{	
					$choices = array();
					foreach($custom as $c)
					{
						$choices[$c['title']] = $c['title'];
					}
				}
								
				break;
			
			case "taxonomy" :
				
				$choices = $this->get_taxonomies_for_select();
				$optgroup = true;
								
				break;
			
			case "ef_taxonomy" :
				
				$choices = array('all' => 'All');
				$taxonomies = get_taxonomies( array('public' => true), 'objects' );
				
				foreach($taxonomies as $taxonomy)
				{
					$choices[ $taxonomy->name ] = $taxonomy->labels->name;
				}
				
				// unset post_format (why is this a public taxonomy?)
				if( isset($choices['post_format']) )
				{
					unset( $choices['post_format']) ;
				}
			
								
				break;
			
			case "ef_user" :
				
				global $wp_roles;
				
				$choices = array_merge( array('all' => 'All'), $wp_roles->get_names() );
			
				break;
				
				
			case "ef_media" :
				
				$choices = array('all' => 'All');
			
				break;
				
		}
		
		$this->create_field(array(
			'type'	=>	'select',
			'name'	=>	'location[rules][' . $options['key'] . '][value]',
			'value'	=>	$options['value'],
			'choices' => $choices,
			'optgroup' => $optgroup,
		));
		
		// ajax?
		if(isset($_POST['action']) && $_POST['action'] == "acf_location")
		{
			die();
		}
								
	}
	
	
	
}
?>