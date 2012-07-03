<?php

class acf_Wysiwyg extends acf_Field
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
    	
    	$this->name = 'wysiwyg';
		$this->title = __("Wysiwyg Editor",'acf');
		
		add_action( 'acf_head-input', array( $this, 'acf_head') );

   	}
   	
	
	
   	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*	- Add the settings for a WYSIWYG editor (as used in wp_editor / wp_tiny_mce)
	*
	*	@author Elliot Condon
	*	@since 3.2.3
	* 
	*-------------------------------------------------------------------------------------*/
	
   	function acf_head()
   	{
   		add_action( 'admin_footer', array( $this, 'admin_footer') );
   	}
   	
   	
   	function admin_footer()
   	{
	   	?>
	   	<div style="display:none;">
	   	<?php wp_editor( '', 'acf_settings' ); ?>
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
		$field['toolbar'] = isset($field['toolbar']) ? $field['toolbar'] : 'full';
		$field['media_upload'] = isset($field['media_upload']) ? $field['media_upload'] : 'yes';
		
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Toolbar",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][toolbar]',
					'value'	=>	$field['toolbar'],
					'layout'	=>	'horizontal',
					'choices' => array(
						'full'	=>	__("Full",'acf'),
						'basic'	=>	__("Basic",'acf')
					)
				));
				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Show Media Upload Buttons?",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'radio',
					'name'	=>	'fields['.$key.'][media_upload]',
					'value'	=>	$field['media_upload'],
					'layout'	=>	'horizontal',
					'choices' => array(
						'yes'	=>	__("Yes",'acf'),
						'no'	=>	__("No",'acf'),
					)
				));
				?>
			</td>
		</tr>
		<?php
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
		$field['toolbar'] = isset($field['toolbar']) ? $field['toolbar'] : 'full';
		$field['media_upload'] = isset($field['media_upload']) ? $field['media_upload'] : 'yes';
		
		$id = 'wysiwyg-' . $field['name'];
		
		
		?>
		<div id="wp-<?php echo $id; ?>-wrap" class="acf_wysiwyg wp-editor-wrap" data-toolbar="<?php echo $field['toolbar']; ?>">
			<?php if($field['media_upload'] == 'yes'): ?>
				<?php if(get_bloginfo('version') < "3.3"): ?>
					<div id="editor-toolbar">
						<div id="media-buttons" class="hide-if-no-js">
							<?php do_action( 'media_buttons' ); ?>
						</div>
					</div>
				<?php else: ?>
					<div id="wp-<?php echo $id; ?>-editor-tools" class="wp-editor-tools">
						<div id="wp-<?php echo $id; ?>-media-buttons" class="hide-if-no-js wp-media-buttons">
							<?php do_action( 'media_buttons' ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
				<textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>" ><?php echo wp_richedit_pre($field['value']); ?></textarea>
			</div>
		</div>
		
		<?php

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
		$value = parent::get_value($post_id, $field);
		
		$value = apply_filters('the_content',$value); 
		
		return $value;
	}
	

}

?>