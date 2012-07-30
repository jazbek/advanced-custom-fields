<?php

/*
*  Meta Box: Fields
*
*  @description: This file creates the HTML for a list of fields within a Field Group
*  @created: 23/06/12
*/

 
// global
global $post;


// vars
$fields_names = array();


// get fields
$fields = $this->parent->get_acf_fields( $post->ID );


// add clone
$fields[999] = array(
	'label'		=>	__("New Field",'acf'),
	'name'		=>	__("new_field",'acf'),
	'type'		=>	'text',
	'order_no'	=>	'1',
	'instructions'	=>	'',
	'required' => '0',
);


// get name of all fields for use in field type drop down
foreach( $this->parent->fields as $field )
{
	$fields_names[$field->name] = $field->title;
}


?>

<!-- Hidden Fields -->
<div style="display:none;">
	<script type="text/javascript">
	var acf_messages = {
		move_to_trash : "<?php _e("Move to trash. Are you sure?",'acf'); ?>"
	};
	</script>
	<input type="hidden" name="save_fields" value="true" />
</div>
<!-- / Hidden Fields -->


<!-- Fields Header -->
<div class="fields_header">
	<table class="acf widefat">
		<thead>
			<tr>
				<th class="field_order"><?php _e('Field Order','acf'); ?></th>
				<th class="field_label"><?php _e('Field Label','acf'); ?></th>
				<th class="field_name"><?php _e('Field Name','acf'); ?></th>
				<th class="field_type"><?php _e('Field Type','acf'); ?></th>
			</tr>
		</thead>
	</table>
</div>
<!-- / Fields Header -->


<div class="fields">
	<div class="no_fields_message" <?php if(sizeof($fields) > 1){ echo 'style="display:none;"'; } ?>>
		<?php _e("No fields. Click the <strong>+ Add Field</strong> button to create your first field.",'acf'); ?>
	</div>
	<?php foreach($fields as $key => $field): ?>
	<div class="<?php echo ($key == 999) ? "field_clone" : "field"; ?>" data-id="<?php echo $key; ?>">
		<?php if(isset($field['key'])): ?><input type="hidden" name="fields[<?php echo $key; ?>][key]" value="<?php echo $field['key']; ?>" /><?php endif; ?>
		<div class="field_meta">
			<table class="acf widefat">
				<tr>
					<td class="field_order"><span class="circle"><?php echo (int)$field['order_no'] + 1; ?></span></td>
					<td class="field_label">
						<strong>
							<a class="acf_edit_field row-title" title="<?php _e("Edit this Field",'acf'); ?>" href="javascript:;"><?php echo $field['label']; ?></a>
						</strong>
						<div class="row_options">
							<span><a class="acf_edit_field" title="<?php _e("Edit this Field",'acf'); ?>" href="javascript:;"><?php _e("Edit",'acf'); ?></a> | </span>
							<span><a title="<?php _e("Read documentation for this field",'acf'); ?>" href="http://www.advancedcustomfields.com/docs/field-types/" target="_blank"><?php _e("Docs",'acf'); ?></a> | </span>
							<span><a class="acf_duplicate_field" title="<?php _e("Duplicate this Field",'acf'); ?>" href="javascript:;"><?php _e("Duplicate",'acf'); ?></a> | </span>
							<span><a class="acf_delete_field" title="<?php _e("Delete this Field",'acf'); ?>" href="javascript:;"><?php _e("Delete",'acf'); ?></a></span>
						</div>
					</td>
					<td class="field_name"><?php echo $field['name']; ?></td>
					<td class="field_type"><?php echo $fields_names[$field['type']]; ?></td>
				</tr>
			</table>
		</div>
		<div class="field_form_mask">
			<div class="field_form">
				
				<table class="acf_input widefat acf_field_form_table">
					<tbody>
						<tr class="field_label">
							<td class="label">
								<label><span class="required">*</span><?php _e("Field Label",'acf'); ?></label>
								<p class="description"><?php _e("This is the name which will appear on the EDIT page",'acf'); ?></p>
							</td>
							<td>
								<?php 
								$this->parent->create_field(array(
									'type'	=>	'text',
									'name'	=>	'fields['.$key.'][label]',
									'value'	=>	$field['label'],
									'class'	=>	'label',
								));
								?>
							</td>
						</tr>
						<tr class="field_name">
							<td class="label">
								<label><span class="required">*</span><?php _e("Field Name",'acf'); ?></label>
								<p class="description"><?php _e("Single word, no spaces. Underscores and dashes allowed",'acf'); ?></p>
							</td>
							<td>
								<?php 
								$this->parent->create_field(array(
									'type'	=>	'text',
									'name'	=>	'fields['.$key.'][name]',
									'value'	=>	$field['name'],
									'class'	=>	'name',
								));
								?>
							</td>
						</tr>
						<tr class="field_type">
							<td class="label"><label><span class="required">*</span><?php _e("Field Type",'acf'); ?></label></td>
							<td>
								<?php 
								$this->parent->create_field(array(
									'type'		=>	'select',
									'name'		=>	'fields['.$key.'][type]',
									'value'		=>	$field['type'],
									'choices' 	=>	$fields_names,
								));
								?>
							</td>
						</tr>
						<tr class="field_instructions">
							<td class="label"><label><?php _e("Field Instructions",'acf'); ?></label>
							<p class="description"><?php _e("Instructions for authors. Shown when submitting data",'acf'); ?></p></td>
							<td>
								<?php 
								$this->parent->create_field(array(
									'type'	=>	'textarea',
									'name'	=>	'fields['.$key.'][instructions]',
									'value'	=>	$field['instructions'],
								));
								?>
							</td>
						</tr>
						<tr class="required">
							<td class="label"><label><?php _e("Required?",'acf'); ?></label></td>
							<td>
								<?php 
								$this->parent->create_field(array(
									'type'	=>	'radio',
									'name'	=>	'fields['.$key.'][required]',
									'value'	=>	$field['required'],
									'choices'	=>	array(
										'1'	=>	__("Yes",'acf'),
										'0'	=>	__("No",'acf'),
									),
									'layout'	=>	'horizontal',
								));
								?>
							</td>
						</tr>
						<?php 
						
						$this->parent->fields[$field['type']]->create_options($key, $field);
						
						?>
						<tr class="field_save">
							<td class="label"></td>
							<td>
								<ul class="hl clearfix">
									<li>
										<a class="acf_edit_field acf-button grey" title="<?php _e("Close Field",'acf'); ?>" href="javascript:;"><?php _e("Close Field",'acf'); ?></a>
									</li>
								</ul>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>	
	</div>
	<?php endforeach; ?>
</div>
<div class="table_footer">
	<div class="order_message"><?php _e('Drag and drop to reorder','acf'); ?></div>
	<a href="javascript:;" id="add_field" class="acf-button"><?php _e('+ Add Field','acf'); ?></a>
</div>