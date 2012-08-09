(function($){

	/*
	*  Exists
	*  
	*  @since			3.1.6
	*  @description		returns true or false on a element's existance
	*/
	
	$.fn.exists = function()
	{
		return $(this).length>0;
	};
	
	
	/*
	*  uniqid
	*  
	*  @since			3.1.6
	*  @description		Returns a unique ID (secconds of time)
	*/
	
	function uniqid()
    {
    	var newDate = new Date;
    	return newDate.getTime();
    }
	
	
	/*
	*  Place Confirm message on Publish trash button
	*  
	*  @since			3.1.6
	*  @description		
	*/
	
	$('#submit-delete').live('click', function(){
			
		var response = confirm(acf_messages.move_to_trash);
		if(!response)
		{
			return false;
		}
		
	});
	
	
	/*
	*  acf/update_field_options
	*  
	*  @since			3.1.6
	*  @description		Load in the opions html
	*/
	
	$('#acf_fields tr.field_type select').live('change', function(){
		
		var tbody = $(this).closest('tbody');

		// show field options if they already exist
		if(tbody.children('tr.field_option_'+$(this).val()).exists())
		{
			// hide + disable options
			tbody.children('tr.field_option').hide().find('[name]').attr('disabled', 'true');
			
			// show and enable options
			tbody.children('tr.field_option_'+$(this).val()).show().find('[name]').removeAttr('disabled');
		}
		else
		{
			// add loading gif
			var tr = $('<tr"><td class="label"></td><td><div class="acf-loading"></div></td></tr>');
			
			// hide current options
			tbody.children('tr.field_option').hide().find('[name]').attr('disabled', 'true');
			
			// append tr
			tbody.children('tr.field_save').before(tr);
			
			var ajax_data = {
				action : "acf_field_options",
				post_id : $('#post_ID').val(),
				field_key : $(this).attr('name'),
				field_type : $(this).val()
			};
			
			$.ajax({
				url: ajaxurl,
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){

					tr.replaceWith(html);
					
				}
			});
		}
		
		
		
	});
	
	
	
	/*----------------------------------------------------------------------
	*
	*	Update Names
	*
	*---------------------------------------------------------------------*/

	$.fn.update_names = function()
	{
		var field = $(this);
		var old_id = field.attr('data-id');
		var new_id = uniqid();
		
		
		// give field a new id
		field.attr('data-id', new_id);
		
		
		field.find('[name]').each(function()
		{	
			
			var name = $(this).attr('name');
			var id = $(this).attr('id');

			if(name && name.indexOf('[' + old_id + ']') != -1)
			{
				name = name.replace('[' + old_id + ']','[' + new_id + ']');
			}
			if(id && id.indexOf('[' + old_id + ']') != -1)
			{
				id = id.replace('[' + old_id + ']','[' + new_id + ']');
			}
			
			$(this).attr('name', name);
			$(this).attr('id', id);
			
		});
	}
	
	
	/*----------------------------------------------------------------------
	*
	*	Update Order Numbers
	*
	*---------------------------------------------------------------------*/
	
	function update_order_numbers(){
		
		$('#acf_fields .fields').each(function(){
			$(this).children('.field').each(function(i){
				$(this).find('td.field_order .circle').first().html(i+1);
			});
		});

	}
	
	
	/*----------------------------------------------------------------------
	*
	*	setup_fields
	*
	*---------------------------------------------------------------------*/
        
	function setup_fields()
	{
		
		// add edit button functionality
		$('#acf_fields a.acf_edit_field').live('click', function(){

			var field = $(this).closest('.field');
			
			if(field.hasClass('form_open'))
			{
				field.removeClass('form_open');
			}
			else
			{
				field.addClass('form_open');
			}
			
			field.children('.field_form_mask').animate({'height':'toggle'}, 500);

		});
		
		
		// add delete button functionality
		$('#acf_fields a.acf_delete_field').live('click', function(){

			var field = $(this).closest('.field');
			var fields = field.closest('.fields');
			var temp = $('<div style="height:' + field.height() + 'px"></div>');
			//field.css({'-moz-transform' : 'translate(50px, 0)', 'opacity' : 0, '-moz-transition' : 'all 250ms ease-out'});
			field.animate({'left' : '50px', 'opacity' : 0}, 250, function(){
				field.before(temp);
				field.remove();
				
				temp.animate({'height' : 0 }, 250, function(){
					temp.remove();
				})
				
				update_order_numbers();
			
				if(!fields.children('.field').exists())
				{
					// no more fields, show the message
					fields.children('.no_fields_message').show();
				}
				
			});
			
			
			
		});
		
		
		// add delete button functionality
		$('#acf_fields a.acf_duplicate_field').live('click', function(){
			
			
			// vars
			var field = $(this).closest('.field');
			var orig_type = field.find('tr.field_type select').val();
			var new_field = field.clone();
			
			
			// update names
			new_field.children('input[type="hidden"]').remove();
			new_field.update_names();
			
			
			// add new field
			field.after( new_field );
			
			
			// open up form
			new_field.find('a.acf_edit_field').first().trigger('click');
			//console.log( new_field.find('tr.field_type select').first() );
			new_field.find('tr.field_type select').first().val( orig_type ).trigger('change');
			
			
			// update order numbers
			update_order_numbers();
			
		});
		

		
		// Add Field Button
		$('#acf_fields #add_field').live('click',function(){
			
			var table_footer = $(this).closest('.table_footer');
			var fields = table_footer.siblings('.fields');
			
			
			// clone last tr
			var new_field = fields.children('.field_clone').clone();
			new_field.removeClass('field_clone').addClass('field');
			
			
			// update input names
			if(new_field.hasClass('sub_field'))
			{
				
				// it is a sub field
				//console.log(fields.parents('.fields').last());
				//var field_length = fields.parents('.fields').last().children('.field').length;
				//var sub_field_length = fields.children('.field').length;
				//alert(sub_field_length);
				//alert('update numbers for sub field! field:'+field_length+', sub:'+sub_field_length);
				
				new_field.update_names();
			}
			else
			{
				//var field_length = fields.children('.field').length;
				new_field.update_names();
				
				//alert('update numbers for field! field:'+field_length);
			}
			
			
			// append to table
			fields.children('.field_clone').before(new_field);
			//fields.append(new_field);
			
			
			// remove no fields message
			if(fields.children('.no_fields_message').exists())
			{
				fields.children('.no_fields_message').hide();
			}
			
			// clear name
			new_field.find('.field_form input[type="text"]').val('');
			new_field.find('.field_form input[type="text"]').first().focus();
			new_field.find('tr.field_type select').trigger('change');	
			
			// open up form
			new_field.find('a.acf_edit_field').first().trigger('click');

			
			// update order numbers
			update_order_numbers();
			
			return false;
			
			
		});
		
		
		
		// Auto complete field name
		$('#acf_fields tr.field_label input.label').live('blur', function()
		{
			var label = $(this);
			var name = $(this).closest('tr').siblings('tr.field_name').find('input.name');

			if(name.val() == '')
			{
				var val = label.val().toLowerCase().split(' ').join('_').split('\'').join('');
				name.val(val);
				name.trigger('keyup');
			}
		});
		
		
		// update field meta
		$('#acf_fields .field_form tr.field_label input.label').live('keyup', function()
		{
			var val = $(this).val();
			var name = $(this).closest('.field').find('td.field_label strong a').first().html(val);
		});
		$('.field_form tr.field_name input.name').live('keyup', function()
		{
			var val = $(this).val();
			var name = $(this).closest('.field').find('td.field_name').first().html(val);
		});
		$('.field_form tr.field_type select').live('change', function()
		{
			var val = $(this).val();
			var label = $(this).find('option[value="' + val + '"]').html();
			
			// update field type (if not a clone field)
			if($(this).closest('.field_clone').length == 0)
			{
				$(this).closest('.field').find('td.field_type').first().html(label);
			}
			
		});
		
		
		// sortable
		$('#acf_fields td.field_order').live('mouseover', function(){
			
			var fields = $(this).closest('.fields');
			
			if(fields.hasClass('sortable')) return false;
			
			fields.addClass('sortable').sortable({
				update: function(event, ui){
					update_order_numbers();
				},
				handle: 'td.field_order',
				axis: "y",
				revert: true
			});
		});
		
	}
	
	
	/*----------------------------------------------------------------------
	*
	*	setup_rules
	*
	*---------------------------------------------------------------------*/
	
	function setup_rules()
	{
		// vars
		var location_rules = $('table#location_rules');
		
		
		// does it have options?
		if(!location_rules.find('td.param select option[value="options_page"]').exists())
		{
			var html = $('#acf_location_options_deactivated').html();
			location_rules.find('td.param select').append( html );
				
		}
		
		
		// show field type options
		location_rules.find('td.param select').live('change', function(){
			
			// vars
			var tr = $(this).closest('tr'); 
			var key = $(this).attr('name').split("]["); key = key[1];
			var ajax_data = {
				'action' : "acf_location",
				'key' : key,
				'value' : '',
				'param' : $(this).val()
			};
			
			
			// add loading gif
			var div = $('<div class="acf-loading"></div>');
			tr.find('td.value').html(div);
			
			
			// load location html
			$.ajax({
				url: ajaxurl,
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){

					div.replaceWith(html);

				}
			});
			
			
		});
		
		
		// Add Button
		location_rules.find('td.buttons a.add').live('click',function(){

			var tr = $(this).closest('tr').clone();
			
			$(this).closest('tr').after(tr);
			
			update_location_names();
			
			location_rules.find('td.buttons a.remove').removeClass('disabled');
					
			return false;
			
		});
		
		
		// Remove Button
		location_rules.find('td.buttons a.remove').live('click',function(){
			
			if($(this).hasClass('disabled'))
			{
				return false;
			}
			
			var tr = $(this).closest('tr').remove();
			
			if(location_rules.find('tr').length <= 1)
			{
				location_rules.find('td.buttons a.remove').addClass('disabled');
			}
			
			return false;
			
		});
		
		if(location_rules.find('tr').length <= 1)
		{
			location_rules.find('td.buttons a.remove').addClass('disabled');
		}
		
		function update_location_names()
		{
			location_rules.find('tr').each(function(i){

				$(this).find('[name]').each(function(){
				
					var name = $(this).attr('name').split("][");
					
					var new_name = name[0] + "][" + i + "][" + name[2];

					$(this).attr('name', new_name).attr('id', new_name);
				});
				
			})
		}
		
	}

	/*----------------------------------------------------------------------
	*
	*	Document Ready
	*
	*---------------------------------------------------------------------*/
	
	$(document).ready(function(){
		
		// custom Publish metabox
		$('#submitdiv #publish').attr('class', 'acf-button');
		$('#submitdiv a.submitdelete').attr('class', 'delete-field-group').attr('id', 'submit-delete');
		
		// setup fields
		setup_fields();
		setup_rules();
		
	});
	
	
	/*
	*  Flexible Content
	*
	*  @description: extra javascript for the flexible content field
	*  @created: 3/03/2011
	*/
	
	/*----------------------------------------------------------------------
	*
	*	Add Layout Option
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_add').live('click', function(){
		
		// vars
		var tr = $(this).closest('tr.field_option_flexible_content');
		var new_tr = $(this).closest('.field_form').find('tr.field_option_flexible_content:first').clone(false);
		
		// remove sub fields
		new_tr.find('.sub_field.field').remove();
		
		// show add new message
		new_tr.find('.no_fields_message').show();
		
		// reset layout meta values
		new_tr.find('.acf_cf_meta input[type="text"]').val('');
		new_tr.find('.acf_cf_meta select').val('table');
		
		// update id / names
		var new_id = uniqid();
		
		new_tr.find('[name]').each(function(){
		
			var name = $(this).attr('name').replace('[layouts][0]','[layouts]['+new_id+']');
			$(this).attr('name', name);
			$(this).attr('id', name);
			
		});
		
		// add new tr
		tr.after(new_tr);
		
		// add drag / drop
		new_tr.find('.fields').sortable({
			handle: 'td.field_order',
			axis: "y",
			revert: true
		});
		
		return false;
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Delete Layout Option
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_delete').live('click', function(){

		var tr = $(this).closest('tr.field_option_flexible_content');
		var tr_count = tr.siblings('tr.field_option.field_option_flexible_content').length;

		if(tr_count == 0)
		{
			alert('Flexible Content requires at least 1 layout');
			return false;
		}
		
		tr.animate({'left' : '50px', 'opacity' : 0}, 250, function(){
			tr.remove();
		});
		
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Sortable Layout Option
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_reorder').live('mouseover', function(){
		
		var table = $(this).closest('table.acf_field_form_table');
		
		if(table.hasClass('sortable')) return false;
		
		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};
		
		table.addClass('sortable').children('tbody').sortable({
			items: ".field_option_flexible_content",
			handle: 'a.acf_fc_reorder',
			helper: fixHelper,
			placeholder: "ui-state-highlight",
			axis: "y",
			revert: true
		});
		
	});
	
	
	/*----------------------------------------------------------------------
	*
	*	Label update name
	*
	*---------------------------------------------------------------------*/
	
	$('#acf_fields .acf_fc_label input[type="text"]').live('blur', function(){
		
		var label = $(this);
		var name = $(this).parents('td').siblings('td.acf_fc_name').find('input[type="text"]');

		if(name.val() == '')
		{
			var val = label.val().toLowerCase().split(' ').join('_').split('\'').join('');
			name.val(val);
			name.trigger('keyup');
		}

	});


})(jQuery);