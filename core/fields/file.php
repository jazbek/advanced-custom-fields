<?php

class acf_File extends acf_Field
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
    	
    	$this->name = 'file';
		$this->title = __('File','acf');
		
		add_action('admin_head-media-upload-popup', array($this, 'popup_head'));
		add_filter('get_media_item_args', array($this, 'allow_file_insertion'));
		add_action('wp_ajax_acf_select_file', array($this, 'ajax_select_file'));
		add_action('acf_head-update_attachment-file', array($this, 'acf_head_update_attachment'));
   	}
   	
   	
   	/*
   	*  acf_head_update_attachment
   	*
   	*  @description: 
   	*  @since: 3.2.7
   	*  @created: 4/07/12
   	*/
   	
   	function acf_head_update_attachment()
	{
		?>
<script type="text/javascript">
(function($){
	
	// vars
	var div = self.parent.acf_edit_attachment;
	
	
	// add message
	self.parent.acf.add_message("<?php _e("File Updated.",'acf'); ?>", div);
	

})(jQuery);
</script>
		<?php
	}
   	
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	render_file
	*
	*	@description : Renders the file html from an ID
	*	@author Elliot Condon
	*	@since 3.1.6
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function render_file($id = null)
   	{
   		if(!$id)
   		{
   			echo "";
   			return;
   		}
   		
   		
   		// vars
		$file_src = wp_get_attachment_url($id);
		preg_match("~[^/]*$~", $file_src, $file_name);
		$class = "active";
   		
   		
   		?>
		<ul class="hl clearfix">
			<li data-mime="<?php echo get_post_mime_type( $id ) ; ?>">
				<img class="acf-file-icon" src="<?php echo wp_mime_type_icon( $id ); ?>" alt=""/>
			</li>
			<li>
				<span class="acf-file-name"><?php echo $file_name[0]; ?></span><br />
				<a href="#" class="edit-file"><?php _e('Edit','acf'); ?></a> 
				<a href="#" class="remove-file"><?php _e('Remove','acf'); ?></a>
			</li>
		</ul>
		<?php
   		
   	}
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	ajax_select_file
	*
	*	@description ajax function to provide url of selected file
	*	@author Elliot Condon
	*	@since 3.1.5
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function ajax_select_file()
   	{
   		$id = isset($_POST['id']) ? $_POST['id'] : false;
   				
		
		// attachment ID is required
   		if(!$id)
   		{
   			echo "";
   			die();
   		}
   		
   		$this->render_file($id);
   		
		die();
   	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	allow_file_insertion
	*
	*	@author Elliot Condon
	*	@since 3.0.1
	* 
	*-------------------------------------------------------------------------------------*/
	
	function allow_file_insertion($vars)
	{
	    $vars['send'] = true;
	    return($vars);
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
			'jquery',
			'jquery-ui-core',
			'jquery-ui-tabs',

			'thickbox',
			'media-upload',			
		));
	}
	
	function admin_print_styles()
	{
  		wp_enqueue_style(array(
			'thickbox',		
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
		
		// vars
		$class = $field['value'] ? "active" : "";
		
		?>
		<div class="acf-file-uploader <?php echo $class; ?>">
			<input class="value" type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" />
			<div class="has-file">
				<?php $this->render_file( $field['value'] ); ?>
			</div>
			<div class="no-file">
				<ul class="hl clearfix">
					<li>
						<span class="acf-file-name"><?php _e('No File Selected','acf'); ?></span>. <a href="#" class="button add-file"><?php _e('Add File','acf'); ?></a>
					</li>
				</ul>
			</div>
		</div>
		<?php

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
		// vars
		$defaults = array(
			'save_format'	=>	'object',
		);
		
		$field = array_merge($defaults, $field);

		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Return Value",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][save_format]',
					'value'	=>	$field['save_format'],
					'layout'	=>	'horizontal',
					'choices' => array(
						'object'	=>	__("File Object",'acf'),
						'url'		=>	__("File URL",'acf'),
						'id'		=>	__("File ID",'acf')
					)
				));
				?>
			</td>
		</tr>
		<?php
	}
	
	
	/*---------------------------------------------------------------------------------------------
	 * popup_head - STYLES MEDIA THICKBOX
	 *
	 * @author Elliot Condon
	 * @since 1.1.4
	 * 
	 ---------------------------------------------------------------------------------------------*/
	function popup_head()
	{	
		// defults
		$access = false;
		$tab = "type";
		
		
		// GET
		if(isset($_GET["acf_type"]) && $_GET['acf_type'] == 'file')
		{
			$access = true;
			if( isset($_GET['tab']) ) $tab = $_GET['tab'];
			
			if( isset($_POST["attachments"]) )
			{
				echo '<div class="updated"><p>' . __("Media attachment updated.") . '</p></div>';
			}
			
		}
		
		
		if( $access )
		{
					
?><style type="text/css">
	#media-upload-header #sidemenu li#tab-type_url,
	#media-upload-header #sidemenu li#tab-gallery,
	#media-items .media-item a.toggle,
	#media-items .media-item tr.image-size,
	#media-items .media-item tr.align,
	#media-items .media-item tr.url,
	#media-items .media-item .slidetoggle {
		display: none !important;
	}
	
	#media-items .media-item {
		min-height: 68px;
	}
	
	#media-items .media-item .acf-checkbox {
		float: left;
		margin: 28px 10px 0;
	}
	
	#media-items .media-item .pinkynail {
		max-width: 64px;
		max-height: 64px;
		display: block !important;
	}
	
	#media-items .media-item .filename.new {
		min-height: 0;
		padding: 20px 10px 10px 10px;
		line-height: 15px;
	}
	
	#media-items .media-item .title {
		line-height: 14px;
	}
	
	#media-items .media-item .acf-select {
		float: right;
		margin: 22px 12px 0 10px;
	}
	
	#media-upload .ml-submit {
		display: none !important;
	}

	#media-upload .acf-submit {
		margin: 1em 0;
		padding: 1em 0;
		position: relative;
		overflow: hidden;
		display: none; /* default is hidden */
	}
	
	#media-upload .acf-submit a {
		float: left;
		margin: 0 10px 0 0;
	}


</style>
<script type="text/javascript">
(function($){
	
	/*
	*  Select File
	*
	*  @created : 29/03/2012
	*/
	
	$('#media-items .media-item a.acf-select').live('click', function(){
		
		var id = $(this).attr('href');
		
		
		// IE7 Fix
		if( id.indexOf("/") != -1 )
		{
			var split = id.split("/");
			id = split[split.length-1];
		}
		

		var data = {
			action: 'acf_select_file',
			id: id
		};
	
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(html) {
		
			if(!html || html == "")
			{
				return false;
			}
			
			self.parent.acf_div.find('input.value').val(id).trigger('change');
			self.parent.acf_div.find('.has-file').html(html);
 			self.parent.acf_div.addClass('active');
 	
 			// validation
 			self.parent.acf_div.closest('.field').removeClass('error');
 			
 			// reset acf_div and return false
 			self.parent.acf_div = null;
 			self.parent.tb_remove();
 	
		});
		
		return false;
	});
	
	
	
	$('#acf-add-selected').live('click', function(){ 
		 
		// check total 
		var ids = []; 
		var i = -1; 
		 
		$('#media-items .media-item .acf-checkbox:checked').each(function(){ 
			ids.push($(this).val()); 
		}); 
		 
		if(ids.length == 0) 
		{ 
			alert("<?php _e("No files selected",'acf'); ?>"); 
			return false; 
		} 
		 
				 
		function acf_add_next_file() 
		{ 
			i++; 
			 
			if(!ids[i]) 
			{ 
				return false; 
			} 
			 
			var this_id = ids[i]; 
			var data = {
				action: 'acf_select_file',
				id: this_id
			};
		
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(html) {
			
				if(!html || html == "")
				{
					return false;
				}
				
				self.parent.acf_div.find('input.value').val(this_id).trigger('change');
				self.parent.acf_div.find('.has-file').html(html);
	 			self.parent.acf_div.addClass('active');
	 	
	 			// validation
	 			self.parent.acf_div.closest('.field').removeClass('error');
	 			
	 			
	 			if((i+1) < ids.length) 
	 			{ 
	 				// add row 
	 				self.parent.acf_div.closest('.repeater').find('.add-row-end').trigger('click'); 
	 			 
	 				// set acf_div to new row file 
	 				self.parent.acf_div = self.parent.acf_div.closest('.repeater').find('> table > tbody > tr.row:last .acf-file-uploader'); 
	 			} 
	 			else 
	 			{ 
	 				// reset acf_div and return false 
 					self.parent.acf_div = null; 
 					self.parent.tb_remove(); 
	 			} 
	 			 
	 			// add next file 
	 			acf_add_next_file(); 
	 			 
	 	 
			}); 
			 
		} 
		acf_add_next_file(); 
		 
		 
		return false; 
		 
	}); 
	
	
	// edit toggle
	$('#media-items .media-item a.acf-toggle-edit').live('click', function(){
		
		if( $(this).hasClass('active') )
		{
			$(this).removeClass('active');
			$(this).closest('.media-item').find('.slidetoggle').attr('style', 'display: none !important');
			return false;
		}
		else
		{
			$(this).addClass('active');
			$(this).closest('.media-item').find('.slidetoggle').attr('style', 'display: table !important');
			return false;
		}
		
	});
	
	
	// set a interval function to add buttons to media items
	function acf_add_buttons()
	{
		// vars
		var is_sub_field = (self.parent.acf_div && self.parent.acf_div.closest('.repeater').length > 0) ? true : false;
		
		
		// add submit after media items (on for sub fields)
		if($('.acf-submit').length == 0 && is_sub_field)
		{
			$('#media-items').after('<div class="acf-submit"><a id="acf-add-selected" class="button"><?php _e("Add Selected Files",'acf'); ?></a></div>');
		}
		
		
		// add buttons to media items
		$('#media-items .media-item:not(.acf-active)').each(function(){
			
			// show the add all button
			$('.acf-submit').show();
			
			// needs attachment ID
			if($(this).children('input[id*="type-of-"]').length == 0){ return false; }
			
			// only once!
			$(this).addClass('acf-active');
			
			// find id
			var id = $(this).children('input[id*="type-of-"]').attr('id').replace('type-of-', '');
			
			// if inside repeater, add checkbox
			if(is_sub_field)
			{
				$(this).prepend('<input type="checkbox" class="acf-checkbox" value="' + id + '" <?php if($tab == "type"){echo 'checked="checked"';} ?> />');
			}
			
			
			// Add edit button
			$(this).find('.filename.new').append('<br /><a href="#" class="acf-toggle-edit">Edit</a>');
			
			// Add select button
			$(this).find('.filename.new').before('<a href="' + id + '" class="button acf-select"><?php _e("Select File",'acf'); ?></a>');
			
			// add save changes button
			$(this).find('tr.submit input.button').hide().before('<input type="submit" value="<?php _e("Update File",'acf'); ?>" class="button savebutton" />');
			
		});
	}
	<?php
	
	// run the acf_add_buttons ever 500ms when on the file upload tab
	if($tab == 'type'): ?>
	var acf_t = setInterval(function(){
		acf_add_buttons();
	}, 500);
	<?php endif; ?>
	
	
	// add acf input filters to allow for tab navigation
	$(document).ready(function(){
		
		setTimeout(function(){
			acf_add_buttons();
		}, 1);
		
		
		$('form#filter').each(function(){
			
			$(this).append('<input type="hidden" name="acf_type" value="file" />');
						
		});
		
		$('form#image-form, form#library-form').each(function(){
			
			var action = $(this).attr('action');
			action += "&acf_type=file";
			$(this).attr('action', action);
			
		});
	});
				
})(jQuery);
</script><?php

		}
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
		// vars
		$defaults = array(
			'save_format'	=>	'object',
		);
		
		$field = array_merge($defaults, $field);
		
		$value = parent::get_value($post_id, $field);
		
		
		// validate
		if( !$value )
		{
			return false;
		}
		
		
		// format
		if( $field['save_format'] == 'url' )
		{
			$value = wp_get_attachment_url($value);
		}
		elseif( $field['save_format'] == 'object' )
		{
			$attachment = get_post( $value );
			
			// create array to hold value data
			$value = array(
				'id' => $attachment->ID,
				'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
				'title' => $attachment->post_title,
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'url' => wp_get_attachment_url( $attachment->ID ),
			);
		}
		
		return $value;
	}
	
}

?>