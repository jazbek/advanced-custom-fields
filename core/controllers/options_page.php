<?php 

/*--------------------------------------------------------------------------
*
*	Acf_options_page
*
*	@author Elliot Condon
*	@since 2.0.4
* 
*-------------------------------------------------------------------------*/
 
 
class acf_options_page 
{

	var $parent;
	var $dir;
	var $data;
	
	/*--------------------------------------------------------------------------------------
	*
	*	Acf_options_page
	*
	*	@author Elliot Condon
	*	@since 2.0.4
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
		// vars
		$this->parent = $parent;
		$this->dir = $parent->dir;
		
		// data for passing variables
		$this->data = array();
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'));
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_menu
	*
	*	@author Elliot Condon
	*	@since 2.0.4
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_menu() 
	{
		// validate
		if(!$this->parent->is_field_unlocked('options_page'))
		{
			return true;
		}
		
		$parent_slug = 'acf-options';
		$parent_title = __('Options','acf');
		
		// set parent slug
		$custom = apply_filters('acf_register_options_page',array());
		if(!empty($custom))
		{	
			$parent_slug = $custom[0]['slug'];
			$parent_title = $custom[0]['title'];
		}
		
		
		// Parent
		$parent_page = add_menu_page($parent_title, __('Options','acf'), 'edit_posts', $parent_slug, array($this, 'html'));	
		
		// some fields require js + css
		add_action('admin_print_scripts-'.$parent_page, array($this, 'admin_print_scripts'));
		add_action('admin_print_styles-'.$parent_page, array($this, 'admin_print_styles'));
		
		// Add admin head
		add_action('admin_head-'.$parent_page, array($this,'admin_head'));
		add_action('admin_footer-'.$parent_page, array($this,'admin_footer'));
		
		if(!empty($custom))
		{
			foreach($custom as $c)
			{
				$child_page = add_submenu_page($parent_slug, $c['title'], $c['title'], 'edit_posts', $c['slug'], array($this, 'html'));
				
				// some fields require js + css
				add_action('admin_print_scripts-'.$child_page, array($this, 'admin_print_scripts'));
				add_action('admin_print_styles-'.$child_page, array($this, 'admin_print_styles'));
				
				// Add admin head
				add_action('admin_head-'.$child_page, array($this,'admin_head'));
				add_action('admin_footer-'.$child_page, array($this,'admin_footer'));
			}
		}

	}
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*
	*	@author Elliot Condon
	*	@since 2.0.4
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{	
	
		// save
		if(isset($_POST['update_options']))
		{
			
			// options name to save against
			$option_name = 'options';
			
			
			// save fields
			$fields = isset($_POST['fields']) ? $_POST['fields'] : false;
			
			if($fields)
			{
				foreach($fields as $key => $value)
				{
					// get field
					$field = $this->parent->get_acf_field($key);
				
					$this->parent->update_value( $option_name , $field, $value );
					
				}
			}
			
			
			$this->data['admin_message'] = __("Options Updated",'acf');
			
		}
		
		$metabox_ids = $this->parent->get_input_metabox_ids(false, false);

		
		if(empty($metabox_ids))
		{
			$this->data['no_fields'] = true;
			return false;	
		}
		
		// Style
		echo '<link rel="stylesheet" type="text/css" href="'.$this->parent->dir.'/css/global.css?ver=' . $this->parent->version . '" />';
		echo '<link rel="stylesheet" type="text/css" href="'.$this->parent->dir.'/css/input.css?ver=' . $this->parent->version . '" />';
		echo '<style type="text/css">#side-sortables.empty-container { border: 0 none; }</style>';

		// Javascript
		echo '<script type="text/javascript" src="'.$this->parent->dir.'/js/input-actions.js?ver=' . $this->parent->version . '" ></script>';
		echo '<script type="text/javascript">acf.post_id = 0;</script>';
		
		
		// add user js + css
		do_action('acf_head-input');
		

		// get acf's
		$acfs = $this->parent->get_field_groups();
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// hide / show
				$show = in_array($acf['id'], $metabox_ids) ? "true" : "false";
				if($show == "true")
				{				
					// add meta box
					add_meta_box(
						'acf_' . $acf['id'], 
						$acf['title'], 
						array($this->parent->input, 'meta_box_input'), 
						'acf_options_page', 
						$acf['options']['position'], 
						'high', 
						array( 'fields' => $acf['fields'], 'options' => $acf['options'], 'show' => $show, 'post_id' => "options" )
					);
				}
			}
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_footer
	*
	*	@author Elliot Condon
	*	@since 2.0.4
	* 
	*-------------------------------------------------------------------------------------*/
	function admin_footer()
	{
		
	}
	
	
	/*---------------------------------------------------------------------------------------------
	 * admin_print_scripts / admin_print_styles
	 *
	 * @author Elliot Condon
	 * @since 2.0.4
	 * 
	 ---------------------------------------------------------------------------------------------*/
	function admin_print_scripts() {

  		do_action('acf_print_scripts-input');

	}
	
	function admin_print_styles() {
		
		do_action('acf_print_styles-input');
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	options_page
	*
	*	@author Elliot Condon
	*	@since 2.0.4
	* 
	*-------------------------------------------------------------------------------------*/
	function html()
	{
		?>
		<div class="wrap no_move">
		
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo get_admin_page_title(); ?></h2>
			
			<?php if(isset($this->data['admin_message'])): ?>
			<div id="message" class="updated"><p><?php echo $this->data['admin_message']; ?></p></div>
			<?php endif; ?>
			
			<?php if(isset($this->data['no_fields'])): ?>
			<div id="message" class="updated"><p><?php _e("No Custom Field Group found for the options page",'acf'); ?>. <a href="<?php echo admin_url(); ?>post-new.php?post_type=acf"><?php _e("Create a Custom Field Group",'acf'); ?></a></p></div>
			<?php else: ?>
			
			<form id="post" method="post" name="post">
			<div class="metabox-holder has-right-sidebar" id="poststuff">
				
				<!-- Sidebar -->
				<div class="inner-sidebar" id="side-info-column">
					
					<!-- Update -->
					<div class="postbox">
						<h3 class="hndle"><span><?php _e("Publish",'acf'); ?></span></h3>
						<div class="inside">
							<input type="hidden" name="HTTP_REFERER" value="<?php echo $_SERVER['HTTP_REFERER'] ?>" />
							<input type="submit" class="acf-button" value="<?php _e("Save Options",'acf'); ?>" name="update_options" />
						</div>
					</div>
					
					<?php $meta_boxes = do_meta_boxes('acf_options_page', 'side', null); ?>
					
				</div>
					
				<!-- Main -->
				<div id="post-body">
				<div id="post-body-content">
					<?php $meta_boxes = do_meta_boxes('acf_options_page', 'normal', null); ?>
					<script type="text/javascript">
					(function($){
					
						$('#poststuff .postbox[id*="acf_"]').addClass('acf_postbox');

					})(jQuery);
					</script>
				</div>
				</div>
			
			</div>
			</form>
			
			<?php endif; ?>
		
		</div>
		
		<?php
				
	}
			
}

?>