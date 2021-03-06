<?php
/**********************************************************************************************************
Module:	include_config_defaults.php

Description:
	This module is included by "include_config.php".  Provides a default set of values for the system. These default values 
	are overridden by the values in "include_config.php".
		
Change Control:
	
	[Nick Brown]	02/03/2009
	Increased $max_log_entries value to 1000. Increased $systems_audited_days value to 45. Added $admin_list and 
	$user_list.
	
	[Nick Brown]	23/04/2009
	Re-organised settings into sections. Removed $domain_suffix and $ldap_connect_string settings as they don't appear to 
	be used anywhere.
	
**********************************************************************************************************/

// ****************  General Settings *******************************************
$language = 'en';
$mysql_server = 'localhost';
$mysql_user = 'root';
$mysql_password = 'password';
$mysql_database = 'openaudit';

// ****************  Security Settings *******************************************
$use_https = 'n';
$use_pass = 'n';
// An array of allowed users and their passwords (set use_pass = "n" if you do not wish to use passwords)
$users = array(
  'admin' => 'Open-AudIT'
);
$use_ldap_integration= 'n';
$ldap_base_dn = 'dc=mydomain,dc=local';
$ldap_server = 'domain.local';
$ldap_user = 'unknown@domain.local';
$ldap_secret = 'password';
$use_ldap_login = 'n';
$full_details = 'y';
$human_readable_ldap_fields = 'y';
$image_link_ldap_attribute = "name";

// ****************  Homepage Settings *******************************************
$show_other_discovered = 'y';
$other_detected = '3';
$show_system_discovered = 'y';
$system_detected = '3';
$show_systems_not_audited = 'y';
$days_systems_not_audited = '3';
$show_partition_usage = 'y';
$partition_free_space = '1000';
$show_software_detected = 'y';
$days_software_detected = '1';
$show_patches_not_detected = 'y';
$number_patches_not_detected = '5';
$show_detected_servers = 'y';
$show_detected_xp_av = 'y';
$show_detected_rdp = 'y';
$show_os = 'y';
$show_date_audited = 'y';
$show_type = 'y';
$show_description = 'n';
$show_domain = 'n';
$show_service_pack = 'n';
$count_system = '30';
$vnc_type = 'real';
$round_to_decimal_places = '2';
$management_domain_suffix = 'domain.local';
$show_systems_audited_graph = 'y';
$systems_audited_days='45';
$show_ldap_changes = 'y';
$ldap_changes_days = 7;

// ****************  Settings that have no associated GUI *******************************************
$enable_remote_management = 'y';
$max_log_entries = 1000;
$utf8 = 'y';
$show_dell_warranty = 'y';
$show_tips = 'n';
$admin_list = Array('Domain Admins');
$user_list = Array('Domain Admins');
$user_ldap_attributes = Array("company","department","description","displayname",
	"mail","manager","msexchhomeservername","name","physicaldeliveryofficename","samaccountname","telephonenumber");
$computer_ldap_attributes = Array("description","name","operatingsystem","operatingsystemversion","operatingsystemservicepack");
$timezone = 'Europe/London';
$show_summary_barcode = FALSE ;
$summary_barcode = "name";
?>
