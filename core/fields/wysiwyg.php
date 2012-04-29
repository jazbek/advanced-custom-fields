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
		
		add_action('admin_head', array($this, 'add_tiny_mce'));
		add_filter( 'wp_default_editor', array($this, 'my_default_editor'));
		
   	}
   	
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	my_default_editor
	*	- this temporarily fixes a bug which causes the editors to break when the html tab 
	*	is activeon page load
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	*	@updated 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
   	
   	function my_default_editor()
   	{
    	return 'tinymce'; // html or tinymce
    }
   	
   	
   	/*--------------------------------------------------------------------------------------
	*
	*	add_tiny_mce
	*
	*	@author Elliot Condon
	*	@since 3.0.3
	*	@updated 3.0.3
	* 
	*-------------------------------------------------------------------------------------*/
   	
   	function add_tiny_mce()
   	{
   		global $post;
   		
   		if($post && post_type_supports($post->post_type, 'editor'))
   		{
   			// do nothing, wysiwyg will render correctly!
   		}
   		else
   		{
   			wp_tiny_mce();
   		}
		
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

			// wysiwyg
			'editor',
			'thickbox',
			'media-upload',
			'word-count',
			'post',
			'editor-functions',
			'tiny_mce',
						
		));
	}
	
	function admin_print_styles()
	{
  		wp_enqueue_style(array(
  			'editor-buttons',
			'thickbox',		
		));
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
						<?php /*<a onclick="switchEditors.switchto(this);" class="hide-if-no-js wp-switch-editor switch-html active" id="<?php echo $id; ?>-html">HTML</a>
						<a onclick="switchEditors.switchto(this);" class="hide-if-no-js wp-switch-editor switch-tmce" id="<?php echo $id; ?>-tmce">Visual</a>*/ ?>
						<div id="wp-<?php echo $id; ?>-media-buttons" class="hide-if-no-js wp-media-buttons">
							<?php do_action( 'media_buttons' ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div id="wp-<?php echo $id; ?>-editor-container" class="wp-editor-container">
				<?php /*<div id="qt_<?php echo $id; ?>_toolbar" class="quicktags-toolbar">
					<input type="button" value="b" title="" class="ed_button" accesskey="b" id="qt_<?php echo $id; ?>_strong">
					<input type="button" value="i" title="" class="ed_button" accesskey="i" id="qt_<?php echo $id; ?>_em">
					<input type="button" value="link" title="" class="ed_button" accesskey="a" id="qt_<?php echo $id; ?>_link">
					<input type="button" value="b-quote" title="" class="ed_button" accesskey="q" id="qt_<?php echo $id; ?>_block">
					<input type="button" value="del" title="" class="ed_button" accesskey="d" id="qt_<?php echo $id; ?>_del">
					<input type="button" value="ins" title="" class="ed_button" accesskey="s" id="qt_<?php echo $id; ?>_ins">
					<input type="button" value="img" title="" class="ed_button" accesskey="m" id="qt_<?php echo $id; ?>_img">
					<input type="button" value="ul" title="" class="ed_button" accesskey="u" id="qt_<?php echo $id; ?>_ul">
					<input type="button" value="ol" title="" class="ed_button" accesskey="o" id="qt_<?php echo $id; ?>_ol">
					<input type="button" value="li" title="" class="ed_button" accesskey="l" id="qt_<?php echo $id; ?>_li">
					<input type="button" value="code" title="" class="ed_button" accesskey="c" id="qt_<?php echo $id; ?>_code">
					<input type="button" value="more" title="" class="ed_button" accesskey="t" id="qt_<?php echo $id; ?>_more">
					<input type="button" value="lookup" title="Dictionary lookup" class="ed_button" id="qt_<?php echo $id; ?>_spell">
					<input type="button" value="close tags" title="Close all open tags" class="ed_button" id="qt_<?php echo $id; ?>_close">
					<input type="button" value="fullscreen" title="Toggle fullscreen mode" class="ed_button" accesskey="f" id="qt_<?php echo $id; ?>_fullscreen">
				</div>*/ ?>
				<textarea id="<?php echo $id; ?>" class="wp-editor-area" name="<?php echo $field['name']; ?>" ><?php echo wp_richedit_pre($field['value']); ?></textarea>
			</div>
		</div>
		<?php //wp_editor('', $id); ?>
		
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