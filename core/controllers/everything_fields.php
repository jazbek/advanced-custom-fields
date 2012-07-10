<?php 

/*--------------------------------------------------------------------------
*
*	Everything_fields
*
*	@author Elliot Condon
*	@since 3.1.8
* 
*-------------------------------------------------------------------------*/
 
 
class acf_everything_fields 
{

	var $parent;
	var $dir;
	var $data;
	
	/*--------------------------------------------------------------------------------------
	*
	*	Everything_fields
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
		// vars
		$this->parent = $parent;
		$this->dir = $parent->dir;
		
		
		// data for passing variables
		$this->data = array(
			'page_id' => '', // a string used to load values
			'metabox_ids' => array(),
			'page_type' => '', // taxonomy / user / media
			'page_action' => '', // add / edit
			'option_name' => '', // key used to find value in wp_options table. eg: user_1, category_4
		);
		
		
		// actions
		add_action('admin_menu', array($this,'admin_menu'));
		add_action('wp_ajax_acf_everything_fields', array($this, 'acf_everything_fields'));
		
		
		// save
		add_action('create_term', array($this, 'save_taxonomy'));
		add_action('edited_term', array($this, 'save_taxonomy'));
		
		add_action('edit_user_profile_update', array($this, 'save_user'));
		add_action('personal_options_update', array($this, 'save_user'));
		add_action('user_register', array($this, 'save_user'));
		
		
		add_filter("attachment_fields_to_save", array($this, 'save_attachment'), null , 2);

	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_menu
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_menu() 
	{
	
		global $pagenow;

		// we dont want to waste php memory, only check for pages we care about
		if( !in_array( $pagenow, array( 'edit-tags.php', 'profile.php', 'user-new.php', 'user-edit.php', 'media.php' ) ) )
		{
			return false;
		}
		
		
		// set page type
		$options = array();
		
		if( $pagenow == "edit-tags.php" && isset($_GET['taxonomy']) )
		{
		
			$this->data['page_type'] = "taxonomy";
			$options['ef_taxonomy'] = $_GET['taxonomy'];
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";
			
			if(isset($_GET['action']) && $_GET['action'] == "edit")
			{
				$this->data['page_action'] = "edit";
				$this->data['option_name'] = $_GET['taxonomy'] . "_" . $_GET['tag_ID'];
			}
			
		}
		elseif( $pagenow == "profile.php" )
		{
		
			$this->data['page_type'] = "user";
			$options['ef_user'] = get_current_user_id();
			
			$this->data['page_action'] = "edit";
			$this->data['option_name'] = "user_" . get_current_user_id();
			
		}
		elseif( $pagenow == "user-edit.php" && isset($_GET['user_id']) )
		{
		
			$this->data['page_type'] = "user";
			$options['ef_user'] = $_GET['user_id'];
			
			$this->data['page_action'] = "edit";
			$this->data['option_name'] = "user_" . $_GET['user_id'];
			
		}
		elseif( $pagenow == "user-new.php" )
		{
			$this->data['page_type'] = "user";
			$options['ef_user'] ='all';
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";

		}
		elseif( $pagenow == "media.php" )
		{
			
			$this->data['page_type'] = "media";
			$options['ef_media'] = 'all';
			
			$this->data['page_action'] = "add";
			$this->data['option_name'] = "";
			
			if(isset($_GET['attachment_id']))
			{
				$this->data['page_action'] = "edit";
				$this->data['option_name'] = $_GET['attachment_id'];
			}
			
		}
		
		
		// find metabox id's for this page
		$this->data['metabox_ids'] = $this->parent->get_input_metabox_ids( $options , false );

		
		// dont continue if no ids were found
		if(empty( $this->data['metabox_ids'] ))
		{
			return false;	
		}
		
		
		// some fields require js + css
		do_action('acf_print_scripts-input');
		do_action('acf_print_styles-input');
		
		
		// Add admin head
		add_action('admin_head-'.$pagenow, array($this,'admin_head'));
		//add_action('admin_footer-'.$pagenow, array($this,'admin_footer'));
		
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{	
		global $pagenow;
		
		
		// Style
		echo '<link rel="stylesheet" type="text/css" href="'.$this->parent->dir.'/css/global.css?ver=' . $this->parent->version . '" />';
		echo '<link rel="stylesheet" type="text/css" href="'.$this->parent->dir.'/css/input.css?ver=' . $this->parent->version . '" />';
		
		
		// Javascript
		echo '<script type="text/javascript" src="'.$this->parent->dir.'/js/input-actions.js?ver=' . $this->parent->version . '" ></script>';
		echo '<script type="text/javascript">acf.post_id = 0;</script>';
		
		
		// add user js + css
		do_action('acf_head-input');
		
		
		?>
		<script type="text/javascript">
		(function($){

		acf.data = {
			action 			:	'acf_everything_fields',
			metabox_ids		:	'<?php echo implode( ',', $this->data['metabox_ids'] ); ?>',
			page_type		:	'<?php echo $this->data['page_type']; ?>',
			page_action		:	'<?php echo $this->data['page_action']; ?>',
			option_name		:	'<?php echo $this->data['option_name']; ?>'
		};
		
		$(document).ready(function(){

			$.ajax({
				url: ajaxurl,
				data: acf.data,
				type: 'post',
				dataType: 'html',
				success: function(html){
					<?php 
					
					if($this->data['page_type'] == "user")
					{
						if($this->data['page_action'] == "add")
						{
							echo "$('#createuser > table.form-table > tbody').append( html );";
						}
						else
						{
							echo "$('#your-profile > p.submit').before( html );";
						}
					}
					elseif($this->data['page_type'] == "taxonomy")
					{
						if($this->data['page_action'] == "add")
						{
							echo "$('#addtag > p.submit').before( html );";
						}
						else
						{
							echo "$('#edittag > p.submit').before( html );";
						}
					}
					elseif($this->data['page_type'] == "media")
					{
						if($this->data['page_action'] == "add")
						{
							echo "$('#addtag > p.submit').before( html );";
						}
						else
						{
							echo "$('#media-single-form table tbody tr.submit').before( html );";
						}
					}
										
					echo "setTimeout( function(){ $(document).trigger('acf/setup_fields', $('#wpbody') ); }, 200);";
					
					?>
				}
			});
		
		});
		})(jQuery);
		</script>
		<?php
	}
	
		
	
	/*--------------------------------------------------------------------------------------
	*
	*	save_taxonomy
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_taxonomy( $term_id )
	{
		// validate
		if( !isset( $_POST['fields'] ) )
		{
			return;
		}
		
		
		// options name to save against
		$option_name = $_POST['taxonomy'] . '_' . $term_id;
		
		
		// save fields
		$fields = $_POST['fields'];
		
		foreach($fields as $key => $value)
		{
			// get field
			$field = $this->parent->get_acf_field($key);
			
			$this->parent->update_value( $option_name , $field, $value );
		}
		
	}
		
		
	/*--------------------------------------------------------------------------------------
	*
	*	profile_save
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_user( $user_id )
	{
		
		// validate
		if( !isset( $_POST['fields'] ) )
		{
			return;
		}
		
		
		// options name to save against
		$option_name = 'user_' . $user_id;
		
		
		// save fields
		$fields = $_POST['fields'];
		
		foreach($fields as $key => $value)
		{
			// get field
			$field = $this->parent->get_acf_field($key);
			
			$this->parent->update_value( $option_name , $field, $value );
		}

		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	save_attachment
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function save_attachment( $post, $attachment )
	{

		// validate
		if( !isset( $_POST['fields'] ) )
		{
			return $post;
		}

		
		// save fields
		$fields = $_POST['fields'];
		
		foreach($fields as $key => $value)
		{
			// get field
			$field = $this->parent->get_acf_field($key);
			
			$this->parent->update_value( $post['ID'] , $field, $value );
		}
		
		return $post;

		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	acf_everything_fields
	*
	*	@description		Ajax call that renders the html needed for the page
	*	@author 			Elliot Condon
	*	@since 				3.1.8
	* 
	*-------------------------------------------------------------------------------------*/
	
	function acf_everything_fields()
	{
		// defaults
		$defaults = array(
			'metabox_ids' => '',
			'page_type' => '',
			'page_action' => '',
			'option_name' => '',
		);
		
		
		// load post options
		$options = array_merge($defaults, $_POST);
		
		
		// metabox ids is a string with commas
		$options['metabox_ids'] = explode( ',', $options['metabox_ids'] );
		
			
		// get acfs
		$acfs = $this->parent->get_field_groups();
		
		
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// only add the chosen field groups
				if( !in_array( $acf['id'], $options['metabox_ids'] ) )
				{
					continue;
				}
				
				
				// needs fields
				if(!$acf['fields'])
				{
					continue;
				}
				
				
				// title 
				if( $options['page_action'] == "edit" && $options['page_type'] != "media")
				{
					echo '<h3>' . get_the_title( $acf['id'] ) . '</h3>';
					echo '<table class="form-table">';
				}
				
				
				// render
				foreach($acf['fields'] as $field)
				{
				
					// if they didn't select a type, skip this field
					if($field['type'] == 'null') continue;
					
					// set value
					$field['value'] = $this->parent->get_value( $options['option_name'], $field);
					
					// required
					if(!isset($field['required']))
					{
						$field['required'] = "0";
					}
					
					$required_class = "";
					$required_label = "";
					
					if($field['required'] == "1")
					{
						$required_class = ' required';
						$required_label = ' <span class="required">*</span>';
					}
					
					if( $options['page_type'] == "taxonomy" && $options['page_action'] == "add")
					{
						echo '<div id="acf-' . $field['name'] . '" class="form-field' . $required_class . '">';
							echo '<label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label>';	
							$field['name'] = 'fields[' . $field['key'] . ']';
							$this->parent->create_field($field);
							if($field['instructions']) echo '<p class="description">' . $field['instructions'] . '</p>';
						echo '</div>';
					}
					else
					{
						echo '<tr id="acf-' . $field['name'] . '" class="field form-field' . $required_class . '">';
							echo '<th valign="top" scope="row"><label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label></th>';	
							echo '<td>';
								$field['name'] = 'fields[' . $field['key'] . ']';
								$this->parent->create_field($field);
								if($field['instructions']) echo '<span class="description">' . $field['instructions'] . '</span>';
							echo '</td>';
						echo '</tr>';

					}
					
										
				}
				// foreach($fields as $field)
				
				
				// footer
				if( $options['page_action'] == "edit" && $options['page_type'] != "media")
				{
					echo '</table>';
				}
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
		
		// exit for ajax
		die();

	}
	
			
}

?>