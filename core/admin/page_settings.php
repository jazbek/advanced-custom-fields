<?php

/**
 * Admin Page: Settings
 *
 * This file creates the HTML for the ACF admin page (Settings)
 * On this page you can:
 * - Activate / deactivate keys
 * - Export acf objects
 * - Update ACF global settings
 */

$action = isset($_POST['action']) ? $_POST['action'] : "";

?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->dir ?>/css/global.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->dir ?>/css/acf.css" />

<!-- Wrap -->
<div class="wrap">

	<div class="icon32" id="icon-acf"><br></div>
	<h2 style="margin: 0 0 25px;"><?php _e("Advanced Custom Fields Settings",'acf'); ?></h2>
	
<?php
 
if($action == ""): 

/**
 * Action: Settings Home Page
 */
 
?>

<form method="post">
	
	<!-- Settings -->
	<div class="wp-box">
		<div class="inner">
			<h2><?php _e("Activate Add-ons.",'acf'); ?></h2>
			<table class="acf_activate widefat">
		<thead>
			<tr>
				<th><?php _e("Field Type",'acf'); ?></th>
				<th><?php _e("Status",'acf'); ?></th>
				<th><?php _e("Activation Code",'acf'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php _e("Repeater Field",'acf'); ?></td>
				<td><?php echo $this->is_field_unlocked('repeater') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
				<td>
					<form action="" method="post">
						<?php if($this->is_field_unlocked('repeater')){
							echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->get_license_key('repeater'),-4) .'</span>';
							echo '<input type="hidden" name="acf_field_deactivate" value="repeater" />';
							echo '<input type="submit" class="button" value="Deactivate" />';
						}
						else
						{
							echo '<input type="text" name="key" value="" />';
							echo '<input type="hidden" name="acf_field_activate" value="repeater" />';
							echo '<input type="submit" class="button" value="Activate" />';
						} ?>
					</form>
				</td>
			</tr>
			<tr>
				<td><?php _e("Flexible Content Field",'acf'); ?></td>
				<td><?php echo $this->is_field_unlocked('flexible_content') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
				<td>
					<form action="" method="post">
						<?php if($this->is_field_unlocked('flexible_content')){
							echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->get_license_key('flexible_content'),-4) .'</span>';
							echo '<input type="hidden" name="acf_field_deactivate" value="flexible_content" />';
							echo '<input type="submit" class="button" value="Deactivate" />';
						}
						else
						{
							echo '<input type="text" name="key" value="" />';
							echo '<input type="hidden" name="acf_field_activate" value="flexible_content" />';
							echo '<input type="submit" class="button" value="Activate" />';
						} ?>
					</form>
				</td>
			</tr>
			<tr>
				<td><?php _e("Options Page",'acf'); ?></td>
				<td><?php echo $this->is_field_unlocked('options_page') ? __("Active",'acf') : __("Inactive",'acf'); ?></td>
				<td>
					<form action="" method="post">
						<?php if($this->is_field_unlocked('options_page')){
							echo '<span class="activation_code">XXXX-XXXX-XXXX-'.substr($this->get_license_key('options_page'),-4) .'</span>';
							echo '<input type="hidden" name="acf_field_deactivate" value="options_page" />';
							echo '<input type="submit" class="button" value="Deactivate" />';
						}
						else
						{
							echo '<input type="text" name="key" value="" />';
							echo '<input type="hidden" name="acf_field_activate" value="options_page" />';
							echo '<input type="submit" class="button" value="Activate" />';
						} ?>
					</form>
				</td>
			</tr>
		</tbody>
	</table>
		</div>
		<div class="footer">
			<ul class="hl left">
				<li><?php _e("Add-ons can be unlocked by purchasing a license key. Each key can be used on multiple sites.",'acf'); ?> <a href="http://www.advancedcustomfields.com/add-ons/"><?php _e("Find Add-ons",'acf'); ?></a></li>
			</ul>
			<ul class="hl right">
				<li></li>
			</ul>
		</div>
	</div>
	<!-- Settings -->
	
	<br />
	<br />
	<br />
	
	<!-- Export / Import -->
	<form method="post" action="<?php echo $this->dir; ?>/core/actions/export.php">
	<div class="wp-box">
		<div class="wp-box-half left">
			<div class="inner">
				<h2><?php _e("Export Field Groups to XML",'acf'); ?></h2>
		
				<?php
				$acfs = get_pages(array(
					'numberposts' 	=> 	-1,
					'post_type'		=>	'acf',
					'sort_column' => 'menu_order',
					'order' => 'ASC',
				));
	
				// blank array to hold acfs
				$acf_posts = array();
				
				if($acfs)
				{
					foreach($acfs as $acf)
					{
						$acf_posts[$acf->ID] = $acf->post_title;
					}
				}
				
				$this->create_field(array(
					'type'	=>	'select',
					'name'	=>	'acf_posts',
					'value'	=>	'',
					'choices'	=>	$acf_posts,
					'multiple'	=>	'1',
				));
				?>
					
			</div>
			<div class="footer">
				<ul class="hl left">
					<li><?php _e("ACF will create a .xml export file which is compatible with the native WP import plugin.",'acf'); ?></li>
				</ul>
				<ul class="hl right">
					<li><input type="submit" class="acf-button" value="<?php _e("Export XML",'acf'); ?>" /></li>
				</ul>
			</div>
		</div>
		<div class="wp-box-half right">
			<div class="inner">
				<h2><?php _e("Import Field Groups",'acf'); ?></h2>
				<ol>
					<li><?php _e("Navigate to the",'acf'); ?> <a href="<?php echo admin_url(); ?>import.php"><?php _e("Import Tool",'acf'); ?></a> <?php _e("and select WordPress",'acf'); ?></li>
					<li><?php _e("Install WP import plugin if prompted",'acf'); ?></li>
					<li><?php _e("Upload and import your exported .xml file",'acf'); ?></li>
					<li><?php _e("Select your user and ignore Import Attachments",'acf'); ?></li>
					<li><?php _e("That's it! Happy WordPressing",'acf'); ?></li>
				</ol>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	</form>
	<!-- / Export / Import -->
	
	<br />
	<br />
	<br />
	
	<!-- Export / Import PHP -->
	<form method="post">
	<input type="hidden" name="action" value="export_php" />
	<div class="wp-box">
		<div class="wp-box-half left">
			<div class="inner">
				<h2><?php _e("Export Field Groups to PHP",'acf'); ?></h2>
		
				<?php
				$acfs = get_pages(array(
					'numberposts' 	=> 	-1,
					'post_type'		=>	'acf',
					'sort_column' => 'menu_order',
					'order' => 'ASC',
				));
	
				// blank array to hold acfs
				$acf_posts = array();
				
				if($acfs)
				{
					foreach($acfs as $acf)
					{
						$acf_posts[$acf->ID] = $acf->post_title;
					}
				}
				
				$this->create_field(array(
					'type'	=>	'select',
					'name'	=>	'acf_posts',
					'value'	=>	'',
					'choices'	=>	$acf_posts,
					'multiple'	=>	'1',
				));
				?>
					
			</div>
			<div class="footer">
				<ul class="hl left">
					<li><?php _e("ACF will create the PHP code to include in your theme",'acf'); ?></li>
				</ul>
				<ul class="hl right">
					<li><input type="submit" class="acf-button" value="<?php _e("Create PHP",'acf'); ?>" /></li>
				</ul>
			</div>
		</div>
		<div class="wp-box-half right">
			<div class="inner">
				<h2><?php _e("Register Field Groups with PHP",'acf'); ?></h2>
				<ol>
					<li><?php _e("Copy the PHP code generated",'acf'); ?></li>
					<li><?php _e("Paste into your functions.php file",'acf'); ?></li>
					<li><?php _e("To activate any Add-ons, edit and use the code in the first few lines.",'acf'); ?></li>
				</ol>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	</form>
	<!-- / Export / Import PHP -->
		
</form>

<?php
 
elseif($action == "export_php"): 

/**
 * Action: Export PHP
 */

?>

<p><a href="">&laquo; <?php _e("Back to settings",'acf'); ?></a></p>
<div class="wp-box">
	<div class="inner">
		<h2><?php _e("Register Field Groups with PHP",'acf'); ?></h2>
		<ol>
			<li><?php _e("Copy the PHP code generated",'acf'); ?></li>
			<li><?php _e("Paste into your functions.php file",'acf'); ?></li>
			<li><?php _e("To activate any Add-ons, edit and use the code in the first few lines.",'acf'); ?></li>
		</ol>
	</div>
	<div class="footer">
		<pre><?php
		
		$acfs = array();
		
		if(isset($_POST['acf_posts']))
		{
			$acfs = get_pages(array(
				'numberposts' 	=> 	-1,
				'post_type'		=>	'acf',
				'sort_column' => 'menu_order',
				'order' => 'ASC',
				'include'	=>	$_POST['acf_posts']
			));
		}
		if($acfs)
		{
			?>
<?php _e("/**
 * Activate Add-ons
 * Here you can enter your activation codes to unlock Add-ons to use in your theme. 
 * Since all activation codes are multi-site licenses, you are allowed to include your key in premium themes. 
 * Use the commented out code to update the database with your activation code. 
 * You may place this code inside an IF statement that only runs on theme activation.
 */",'acf'); ?>
 
// if(!get_option('acf_repeater_ac')) update_option('acf_repeater_ac', "xxxx-xxxx-xxxx-xxxx");
// if(!get_option('acf_options_page_ac')) update_option('acf_options_page_ac', "xxxx-xxxx-xxxx-xxxx");
// if(!get_option('acf_flexible_content_ac')) update_option('acf_flexible_content_ac', "xxxx-xxxx-xxxx-xxxx");


<?php _e("/**
 * Register field groups
 * The register_field_group function accepts 1 array which holds the relevant data to register a field group
 * You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 * This code must run every time the functions.php file is read
 */",'acf'); ?>

if(function_exists("register_field_group"))
{
<?php
			foreach($acfs as $acf)
			{
				$var = array(
					'id' => uniqid(),
					'title' => get_the_title($acf->ID),
					'fields' => $this->get_acf_fields($acf->ID),
					'location' => $this->get_acf_location($acf->ID),
					'options' => $this->get_acf_options($acf->ID),
					'menu_order' => $acf->menu_order,
				);
				
?>register_field_group(<?php var_export($var); ?>);
<?php
			}
?>
}
<?php
		}
		else
		{
			_e("No field groups were selected",'acf');
		}
		?></pre>
	</div>
</div>

<?php
 
endif;

?>

</div>
<!-- / Wrap -->