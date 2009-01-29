<?php
/*
Plugin Name: Walk Around The World
Plugin URI: http://osteodiet.com
Description: Let your users help your blog walk around the world!
Version: 1.0
Author: Ryan Paiva (as a gift for Kathleen Alford)
Author URI: http://osteodiet.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Code is art!
-Ryan
*/

//conditionals
if(isset($_POST['submitCreateTeam'])){
	if(!empty($_POST['title']) && !empty($_POST['pin'])){
		$title = $_POST['title'];
		$pin = $_POST['pin'];
		$table_name = $wpdb->prefix . "watw_teams";
		$time = time();
	
		$insert = "INSERT INTO " . $table_name . "(name, pin, time) VALUES (%s,%s,%d)";
		$results = $wpdb->query($wpdb->prepare($insert, $title, $pin, $time));
		if($results){
			$message1 = "<p>Congratulations! Your team has been added and is pending approval by the administator.</p>";
		}
		else{
			$message1 = "<p>Error: Please make sure that all of the fields have been filled out correctly.</p>";
		}
	}
	else{
		$message1 = "<p>Error: Please make sure that all of the fields have been filled out correctly.</p>";
	}
}
elseif(isset($_POST['submitCreateTeammate']) && !empty($_POST['title']) && !empty($_POST['pin']) && !empty($_POST['etpin']) && !empty($_POST['ettitle'])){
	$title = $_POST['title'];
	$pin = $_POST['pin'];
	$ettitle = $_POST['ettitle'];
	$etpin = $_POST['etpin'];
	$table_name = $wpdb->prefix . "watw_teams";
	$time = time();
	
	$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE `name` = %s AND `pin` = %s", $ettitle, $etpin);
	$result = $wpdb->get_row($sql, ARRAY_A);
	if($result){
		$table_name = $wpdb->prefix . "watw_teammate";
		$insert = "INSERT INTO " . $table_name . "(name, pin, time, teamid) VALUES (%s,%s,%d,%s)";
		$results = $wpdb->query($wpdb->prepare($insert, $title, $pin, $time, $result['id']));
		$message2 = "<p>Congratulations! Your teammate has been added and is pending approval by the administator.</p>";
	}
	else{
		$message2 = "<p>Error: Existing team with that name and password could not be found.</p>";
	}
}
elseif(isset($_POST['submitAddMiles']) && !empty($_POST['title']) && !empty($_POST['pin'])){
	$title = $_POST['title'];
	$pin = $_POST['pin'];
	$miles = $_POST['miles'];
	if(is_numeric($miles)){
		$miles = number_format($miles,2,".","");
		$table_name = $wpdb->prefix . "watw_teammate";
		$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE `name` = %s AND `pin` = %s", $title, $pin);
		$result = $wpdb->get_row($sql, ARRAY_A);
		$teamid = $result['teamid'];
		if($result){
			$table_name2 = $wpdb->prefix . "watw_teams";
			$sql2 = $wpdb->prepare("SELECT * FROM $table_name2 WHERE `id` = %s", $teamid);
			$result2 = $wpdb->get_row($sql2, ARRAY_A);
			if($result2){
				// add miles to teammate
				$table_name = $wpdb->prefix . "watw_teammate";
				$sql = $wpdb->prepare("UPDATE $table_name SET `miles` = `miles` + %s WHERE `name` = %s AND `pin` = %s", $miles, $title, $pin);
				$result = $wpdb->query($sql);
				// add miles to team
				$table_name2 = $wpdb->prefix . "watw_teams";
				$sql2 = $wpdb->prepare("UPDATE $table_name2 SET `miles` = `miles` + %s WHERE `id` = %s", $miles, $teamid);
				$result2 = $wpdb->query($sql2);
				if($result && $result2){
					$message3 = "<p>Congratulations, your miles have been added!</p>";
				}
				else{
					$message3 = "<p>Error: Your miles could not be added.</p>";
				}
			}
			else{
				$message3 = "<p>Error: Your miles could not be added.</p>";
			}
		}
		else{
			$message3 = "<p>Error: A teammate with that name and password could not be found.</p>";
		}
	}
	else{
		$message3 = "<p>Error: You must enter a number for the 'miles' value.</p>";
	}
}

// go go plugin!
if (!class_exists("watwPlugin")) {
	class watwPlugin {
		var $adminOptionsName = "watwPluginAdminOptions";
		function watwPlugin(){
			// constructor
		}
		function init() {
			$this->install_watw();
			$this->getAdminOptions();
		}
		function install_watw() {			
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			// teams
			$table_name = $wpdb->prefix . "watw_teams";
			if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
				$sql = "CREATE TABLE " . $table_name . " (
					id INT(11) NOT NULL AUTO_INCREMENT,
					name VARCHAR(250) NOT NULL,
					pin INT(5) NOT NULL,
					approved TINYINT(1) NOT NULL,
					time INT(11) NOT NULL,
					miles DECIMAL(10,2) UNSIGNED NOT NULL,
					UNIQUE KEY id (id)
					)";
				dbDelta($sql);
			}
			//teammate
			$table_name = $wpdb->prefix . "watw_teammate";
			if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
				$sql = "CREATE TABLE " . $table_name . " (
					id INT(11) NOT NULL AUTO_INCREMENT,
					name VARCHAR(250) NOT NULL,
					teamid INT(11) NOT NULL,
					pin INT(5) NOT NULL,
					approved TINYINT(1) NOT NULL,
					time INT(11) NOT NULL,
					miles DECIMAL(10,2) UNSIGNED NOT NULL,
					UNIQUE KEY id (id)
					)";
				dbDelta($sql);
			}
			$table_name = $wpdb->prefix . "watw_teammate";
			$sql= "ALTER TABLE `". $table_name ."` MODIFY pin varchar(40)";
			$result = $wpdb->query($sql);
			$table_name = $wpdb->prefix . "watw_teams";
			$sql= "ALTER TABLE `". $table_name ."` MODIFY pin varchar(40)";
			$result = $wpdb->query($sql);
		}
		//Returns an array of admin options
		function getAdminOptions() {
			$watwAdminOptions = array('watw-page' => '');
			$devOptions = get_option($this->adminOptionsName);
			if (!empty($devOptions)) {
				foreach ($devOptions as $key => $option)
					$watwAdminOptions[$key] = $option;
			}
			update_option($this->adminOptionsName, $watwAdminOptions);
			return $watwAdminOptions;
		}		
		// display options page
		function watw_options_page() {
			$devOptions = $this->getAdminOptions();
			if (isset($_POST['update_watwSettings'])) { 
				if (isset($_POST['watw-page'])) {
					$devOptions['watw-page'] = apply_filters('content_save_pre', $_POST['watw-page']);
				}
				update_option($this->adminOptionsName, $devOptions);
?>

<div class="updated">
	<p><strong>
		<?php _e("Settings Updated.", "watwPlugin");?>
		</strong></p>
</div>
<?php
			}
?>
<div class=wrap>
	<form method="post" action="<?php echo get_settings('siteurl'); ?><?php echo $_SERVER["REQUEST_URI"]; ?>">
		<h2>Walk Around The World Options</h2>
		<h3>Page that you would like to display the widget:</h3>
		<p>Example: for "yourdomain.com/pagename", "pagename" would be the value to enter below.</p>
		<input type="text" name="watw-page" value="<?php _e(apply_filters('format_to_edit',$devOptions['watw-page']), 'watwPlugin') ?>" />
		<div class="submit">
			<input type="submit" name="update_watwSettings" value="<?php _e('Update Settings', 'DevloungePluginSeries') ?>" />
		</div>
	</form>
</div>
<?php
		}
		// display manage page
		function watw_manage_page() {
?>
<style type="text/css">
table.watwtable {
	border-width: 1px 1px 1px 1px;
	border-spacing: 2px;
	border-style: outset outset outset outset;
	border-color: black black black black;
	border-collapse: collapse;
	background-color: white;
}
table.watwtable tr {
	border-width: 1px 1px 1px 1px;
	padding: 4px 4px 4px 4px;
	border-style: inset inset inset inset;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.watwtable td {
	border-width: 1px 1px 1px 1px;
	padding: 4px 4px 4px 4px;
	border-style: inset inset inset inset;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
</style>
<?php
			global $wpdb;
			if(!empty($_POST['watwManageSubmit'])){
				foreach($_POST['teams'] as $key => $value){
					$table_name = $wpdb->prefix . "watw_teams";
					$approved = ($value['approved'] == 1) ? 1:0;
					$sql = $wpdb->prepare("UPDATE $table_name SET `miles` = %s, `approved` = %s, `name` = %s, `pin` = %s WHERE `id` = %s", $value['miles'],$approved,$value['name'],$value['pin'],$key);
					$result = $wpdb->query($sql);
					if($value['delete'] == 1){
						$sql = "DELETE FROM `$table_name` WHERE `id` = '$key'";
						$result = $wpdb->query($sql);
					}
				}
				foreach($_POST['teammates'] as $key => $value){
					$table_name = $wpdb->prefix . "watw_teammate";
					$approved = ($value['approved'] == 1) ? 1:0;
					$sql = $wpdb->prepare("UPDATE $table_name SET `miles` = %s, `approved` = %s, `name` = %s, `pin` = %s WHERE `id` = %s", $value['miles'],$approved,$value['name'],$value['pin'],$key);
					$result = $wpdb->query($sql);
					if($value['delete'] == 1){
						$sql = "DELETE FROM `$table_name` WHERE `id` = '$key'";
						$result = $wpdb->query($sql);
					}
				}
			}			
			echo "<div class=wrap><h2>WATW Manager</h2>";
			$table_name = $wpdb->prefix . "watw_teams";
			$table_name2 = $wpdb->prefix . "watw_teammate";
			$result = $wpdb->get_results("SELECT * FROM $table_name");
			
			echo"<br /><br />
				<form action='" . get_settings('siteurl') . $_SERVER["REQUEST_URI"] ."' method='post'>
				<table class='watwtable'><tr><td><b>Team</b></td><td><b>Teammate</b></td><td><b>Miles</b></td><td><b>Password</b></td><td><b>Approved</b></td><td><b>Delete</b></td></tr>";
			foreach ($result as $value) {
				echo"<tr>
						<td><input type='text' name='teams[".$value->id."][name]' value='" . $value->name . "' /></td>
						<td></td>
						<td><input type='text' name='teams[".$value->id."][miles]' value='" . $value->miles . "' /></td>
						<td><input type='text' name='teams[".$value->id."][pin]' value='" . $value->pin . "' /></td>
						<td><input type='checkbox' name='teams[".$value->id."][approved]' value='1'"; if($value->approved == 1){echo"CHECKED";} echo" /></td>
						<td><input type='checkbox' name='teams[".$value->id."][delete]' value='1'"; echo" /></td>
					</tr>";
				$result2 = $wpdb->get_results("SELECT * FROM $table_name2 WHERE `teamid` = '$value->id'");
				foreach ($result2 as $value2) {
					echo"<tr>
							<td></td>
							<td><input type='text' name='teammates[".$value2->id."][name]' value='" . $value2->name . "' /></td>
							<td><input type='text' name='teammates[".$value2->id."][miles]' value='" . $value2->miles . "' /></td>
							<td><input type='text' name='teammates[".$value2->id."][pin]' value='" . $value2->pin . "' /></td>
							<td><input type='checkbox' name='teammates[".$value2->id."][approved]' value='1'"; if($value2->approved == 1){echo"CHECKED";} echo" /></td>
							<td><input type='checkbox' name='teammates[".$value2->id."][delete]' value='1'"; echo" /></td>
						</tr>";
				}
			}
			echo"</table>
			<input type='submit' name='watwManageSubmit' value='Update' />
			</form></div>";
		}
		// display watw on page
		function display_watw(){
			global $wpdb;
			global $message1;
			global $message2;
			global $message3;
			$devOptions = $this->getAdminOptions();
			if(is_page($devOptions['watw-page']) && $devOptions['watw-page'] != ""){
				$table_name = $wpdb->prefix . "watw_teams";
				$result = $wpdb->get_results("SELECT * FROM $table_name WHERE `approved` = '1'");
				$miles = 0;
				foreach ($result as $value) {
					$miles += $value->miles;
				}
?>
<style type="text/css">
table.watwtable {
	border-width: 1px 1px 1px 1px;
	border-spacing: 2px;
	border-style: outset outset outset outset;
	border-color: black black black black;
	border-collapse: collapse;
	background-color: white;
}
table.watwtable tr {
	border-width: 1px 1px 1px 1px;
	padding: 4px 4px 4px 4px;
	border-style: inset inset inset inset;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
table.watwtable td {
	border-width: 1px 1px 1px 1px;
	padding: 4px 4px 4px 4px;
	border-style: inset inset inset inset;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
</style>

<div>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="300" height="300" id="watw" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="<?php echo get_settings('siteurl'); ?>/wp-content/plugins/WATW/watw.swf?miles=<?php echo $miles; ?>" />
<param name="wmode" value="transparent" />
<param name="quality" value="high" />
<embed src="<?php echo get_settings('siteurl'); ?>/wp-content/plugins/WATW/watw.swf?miles=<?php echo $miles; ?>" wmode="transparent" quality="high" width="300" height="300" name="watw" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
</div>
<br />
<div>
<?php
$table_name = $wpdb->prefix . "watw_teams";
$table_name2 = $wpdb->prefix . "watw_teammate";
$result = $wpdb->get_results("SELECT * FROM $table_name WHERE `approved` = '1'");

echo"<p>Distance around the world = 24,902 miles</p>";

echo"<table class='watwtable'><tr><td><b>Team</b></td><td><b>Teammate</b></td><td><b>Miles</b></td></tr>";
foreach ($result as $value) {
	echo"<tr><td>" . $value->name . "</td><td></td><td>" . $value->miles . "</td></tr>";
	$result2 = $wpdb->get_results("SELECT * FROM $table_name2 WHERE `teamid` = '$value->id' AND `approved` = '1'");
	foreach ($result2 as $value2) {
		echo"<tr><td></td><td>" . $value2->name . "</td><td>" . $value2->miles . "</td></tr>";
	}
}
echo"</table></div><br />";
?>

<div><b>Add Miles Walked</b>
<?php echo $message3; ?>
<br />(Must be a number, can be specific to two decimals Ex. 234.43)
<form action="<?php echo get_settings('siteurl'); ?><?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
<table><tr>
<td>Teammate Name:</td><td><input type="text" name="title" /></td>
</tr><tr>
<td>Miles:</td><td><input type="text" name="miles" /></td>
</tr><tr>
<td>Password:</td><td><input type="text" name="pin" maxlength="10" /></td>
</tr><tr>
<td></td><td><input type="submit" name="submitAddMiles" value="Add Miles" /></td>
</tr></table>
</form>
</div>
<br />

<div style="border:1px solid;padding:5px;">
<div><b>Create Team</b>
<?php echo $message1; ?>
<form action="<?php echo get_settings('siteurl'); ?><?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
<table><tr>
<td>New Team Name:</td><td><input type="text" name="title" /></td>
</tr><tr>
<td>New Password:</td><td><input type="text" name="pin" maxlength="10" /></td>
</tr><tr>
<td></td><td><input type="submit" name="submitCreateTeam" value="Create Team" /></td>
</tr></table>
</form>
</div>
<br />
<div><b>Add Teammate to Team</b>
<?php echo $message2; ?>
<form action="<?php echo get_settings('siteurl'); ?><?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
<table><tr>
<td>Existing Team Name:</td><td><input type="text" name="ettitle" /></td>
</tr><tr>
<td>Existing Team Password:</td><td><input type="text" name="etpin" maxlength="10" /></td>
</tr><tr>
<td>New Teammate Name:</td><td><input type="text" name="title" /></td>
</tr><tr>
<td>New Teammate Password:</td><td><input type="text" name="pin" maxlength="10" /></td>
</tr><tr>
<td></td><td><input type="submit" name="submitCreateTeammate" value="Create Teammate" /></td>
</tr></table>
</form></div>
</div>
<p>Created by <a href="http://osteodiet.com">The Osteoporosis Diet</a></p>
<?php
			}
		}
	}
}


if (class_exists("watwPlugin")) {
	$watw_Plugin = new watwPlugin();
}

//Initialize the admin panel
if (!function_exists("watwPlugin_ap")) {
	function watwPlugin_ap() {
		global $watw_Plugin;
		if (!isset($watw_Plugin)) {
			return;
		}
		if (function_exists('add_options_page')) {
			// Add a new submenu under Options:
			add_options_page('WATW Options', 'WATW Options', 9, 'watwoptions', array(&$watw_Plugin, 'watw_options_page'));
			// Add a new submenu under Manage:
			add_management_page('WATW Manager', 'WATW Manager', 9, 'watwmanage', array(&$watw_Plugin, 'watw_manage_page'));
		}
	}	
}

//Actions and Filters	
if (isset($watw_Plugin)) {
	//Actions
	add_action('admin_menu', 'watwPlugin_ap');
	add_action('activate_WATW/watw-plugin.php',  array(&$watw_Plugin, 'init'));
}
?>