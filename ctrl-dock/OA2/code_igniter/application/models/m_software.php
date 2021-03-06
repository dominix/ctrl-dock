<?php
/**
 * OAv2
 *
 * An open source network auditing application
 *
 * @package OAv2
 * @author Mark Unwin <mark.unwin@gmail.com>
 * @version beta 8
 * @copyright Copyright (c) 2011, Mark Unwin
 * @license http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 */

class M_software extends MY_Model {

	function __construct() {
		parent::__construct();
	}

	function get_software_name($id = '1') {
		$id = intval($id);
		if ($id == 0){ return("error"); }
		$sql = "SELECT software_name FROM sys_sw_software WHERE software_id = ?";
		$data = array($id);
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		return($row->software_name);
	}

	function get_system_software($system_id, $type = '0') {
		switch ($type)
		{
			case 0:
				// default. do not display patches, hotfix, update, etc
				// $sql_where = "sys_sw_software.software_name NOT LIKE '%hotfix%' AND sys_sw_software.software_name NOT LIKE '%update%' AND sys_sw_software.software_name NOT LIKE '%Service Pack%' AND sys_sw_software.software_name NOT REGEXP '[KB|Q][0-9]{6,}' AND ";
				$sql_where ="sys_sw_software.software_comment = '' AND ";
				break;
			
			case 1:
				// show everything
				$sql_where = '';
				break;
			
			case 2:
				// only updates
				$sql_where = "sys_sw_software.software_comment = 'update' AND ";
				break;
			
			case 3:
				// only odbc drivers
				$sql_where = "sys_sw_software.software_comment = 'odbc driver' AND ";
				break;		

			case 4:
				// only browser addons
				$sql_where = "sys_sw_software.software_comment = 'browser addon' AND ";
				break;	

			case 5:
				// only codecs addons
				$sql_where = "sys_sw_software.software_comment = 'codec' AND ";
				break;	
			
			case 6:
				// only .net assemblies
				$sql_where = "software_comment = '.NET Assembly' AND ";
			}
		$sql = "SELECT 	
				DISTINCT(software_name), 
				software_uninstall, 
				software_id, 
				software_version, 
				software_publisher, 
				software_url, 
				software_email, 
				software_location, 
				software_installed_on, 
				software_installed_by, 
				sys_sw_software.first_timestamp 
			FROM 	
				sys_sw_software,
				system
			WHERE 
				$sql_where 
				sys_sw_software.system_id = system.system_id AND
				sys_sw_software.timestamp = system.timestamp AND
				system.system_id = ?
			ORDER BY 
				sys_sw_software.software_name";
		$sql = $this->clean_sql($sql);
		$data = array($system_id);
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);
	}

	function process_software($input, $details) {
		// select all the current software from the DB
		$sql = "SELECT sys_sw_software.* 
				FROM sys_sw_software, system 
				WHERE sys_sw_software.system_id 	= system.system_id AND 
					system.system_id		= ? AND 
					system.man_status		= 'production' AND 
					sys_sw_software.timestamp 	= ? ";
		$sql = $this->clean_sql($sql);
		$data = array("$details->system_id", "$details->original_timestamp");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		foreach ($input->package as $software_xml) {
			$flag = 'insert';
			// insert an 'update' tag where necessary
			// note - CPSID_ is for Adobe updates
			if (mb_stripos($software_xml->software_name, 'update') OR mb_stripos($software_xml->software_name, 'hotfix') OR mb_stripos($software_xml->software_name, 'CPSID_') OR mb_strpos($software_xml->software_name, 'KB')) {
				$software_xml->software_comment = 'update';
			}
			foreach ($result as $id => $software_db) {
				// enumerate the array of retrieved packages, looking for a match
				if (!isset($software_db->timestamp)){$software_db->timestamp = '';}
				if 	((strval($software_db->software_name) == strval($software_xml->software_name)) AND (strval($software_db->software_version) == strval($software_xml->software_version)) AND
					((strval($software_db->timestamp) == strval($details->timestamp)) OR (strval($software_db->timestamp) == strval($details->original_timestamp)))) {
					$software_db = & $result[$id];
					// we have a match.
					$flag = 'update';
					
					// update the fields if they are empty
					if (($software_db->software_version == '') AND ($software_xml->software_version != '')) {
							$software_db->software_version = "$software_xml->software_version";
					} 
					
					if (($software_db->software_location == '') AND ($software_xml->software_location != '')) {
							$software_db->software_location = "$software_xml->software_location";
					} 
					
					if (($software_db->software_uninstall == '') AND ($software_xml->software_uninstall != '')) {
							$software_db->software_uninstall = "$software_xml->software_uninstall";
					} 
					
					if (($software_db->software_install_date == '') AND ($software_xml->software_install_date != '')) {
							$software_db->software_install_date = "$software_xml->software_install_date";
					} 
					
					if (($software_db->software_publisher == '') AND ($software_xml->software_publisher != '')) {
							$software_db->software_publisher = "$software_xml->software_publisher";
					} 
					
					if (($software_db->software_install_source == '') AND ($software_xml->software_install_source != '')) {
							$software_db->software_install_source = "$software_xml->software_install_source";
					} 
					
					if (($software_db->software_system_component == '') AND ($software_xml->software_system_component != '')) {
							$software_db->software_system_component = "$software_xml->software_system_component";
					} 
					
					if (($software_db->software_url == '') AND ($software_xml->software_url != '')) {
							$software_db->software_url = "$software_xml->software_url";
					} 
					
					if (($software_db->software_email == '') AND ($software_xml->software_email != '')) {
							$software_db->software_email = "$software_xml->software_email";
					} 
					
					if (($software_db->software_comment == '') AND ($software_xml->software_comment != '')) {
							$software_db->software_comment = "$software_xml->software_comment";
					} 

					if (($software_db->software_installed_by == '') AND ($software_xml->software_installed_by != '')) {
							$software_db->software_installed_by = "$software_xml->software_installed_by";
					} 

					if (($software_db->software_installed_on == '') AND ($software_xml->software_installed_on != '')) {
							$software_db->software_installed_on = "$software_xml->software_installed_on";
					} 
					$software_db->timestamp = $details->timestamp;
					// update the database
					$sql = "UPDATE sys_sw_software SET 
							software_version = ? , 
							software_location = ? , 
							software_uninstall = ? , 
							software_install_date = ? , 
							software_publisher = ? , 
							software_install_source = ? , 
							software_system_component = ? , 
							software_url = ? , 
							software_email = ? , 
							software_comment = ? , 
							software_installed_by = ? , 
							software_installed_on = ? , 
							timestamp = ? 
						WHERE software_id = ?";
					$sql = $this->clean_sql($sql);
					$data = array(	"$software_db->software_version", 
							"$software_db->software_location", 
							"$software_db->software_uninstall", 
							"$software_db->software_install_date", 
							"$software_db->software_publisher", 
							"$software_db->software_install_source", 
							"$software_db->software_system_component", 
							"$software_db->software_url", 
							"$software_db->software_email", 
							"$software_db->software_comment", 
							"$software_db->software_installed_by", 
							"$software_db->software_installed_on", 
							"$software_db->timestamp", 
							"$software_db->software_id");
					$sql = $this->clean_sql($sql);
					$query = $this->db->query($sql, $data);
					unset($software_db);
					// stop the loop
					break;
				} 
			}
			if ($flag == 'insert') {
				// we did not get any matches to the array
				// insert a new row
				$sql = "INSERT INTO sys_sw_software (	system_id, 
						software_name, 
						software_version, 
						software_location, 
						software_uninstall, 
						software_install_date, 
						software_publisher, 
						software_install_source, 
						software_system_component, 
						software_url,
						software_email,
						software_comment, 
						software_code_base, 
						software_status, 
						software_installed_by, 
						software_installed_on, 
						timestamp,
						first_timestamp ) VALUES ( ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? )";
				$sql = $this->clean_sql($sql);
				$sql = $this->clean_sql($sql);
				$data = array("$details->system_id", 
						"$software_xml->software_name", 
						"$software_xml->software_version", 
						"$software_xml->software_location", 
						"$software_xml->software_uninstall", 
						"$software_xml->software_install_date", 
						"$software_xml->software_publisher", 
						"$software_xml->software_install_source", 
						"$software_xml->software_system_component", 
						"$software_xml->software_url", 
						"$software_xml->software_email", 
						"$software_xml->software_comment", 
						"$software_xml->software_code_base", 
						"$software_xml->software_status", 
						"$software_xml->software_installed_by", 
						"$software_xml->software_installed_on", 
						"$details->timestamp", 
						"$details->timestamp");
				$query = $this->db->query($sql, $data);
				$software_db_new = new stdClass();
				$software_db_new->software_id = $this->db->insert_id();
				$software_db_new->software_name = "$software_xml->software_name";
				$software_db_new->software_version = "$software_xml->software_version";
				$software_db_new->software_location = "$software_xml->software_location";
				$software_db_new->software_uninstall = "$software_xml->software_uninstall";
				$software_db_new->software_install_date = "$software_xml->software_install_date";
				$software_db_new->software_publisher = "$software_xml->software_publisher";
				$software_db_new->software_install_source = "$software_xml->software_install_source";
				$software_db_new->software_system_component = "$software_xml->software_system_component";
				$software_db_new->software_url = "$software_xml->software_url";
				$software_db_new->software_email = "$software_xml->software_email";
				$software_db_new->software_comment = "$software_xml->software_comment";
				$software_db_new->software_code_base = "$software_xml->software_code_base";
				$software_db_new->software_status = "$software_xml->software_status";
				$software_db_new->software_installed_by = "$software_xml->software_installed_by";
				$software_db_new->software_installed_on = "$software_xml->software_installed_on";
				$software_db_new->software_timestamp = "$details->timestamp";
				$software_db_new->software_first_timestamp = "$details->timestamp";
				$result[] = $software_db_new;
				unset($software_db_new);
			}
		}
	}



	function alert_software($details) {
		// software no longer detected
		$sql = "SELECT 
					sys_sw_software.software_id, 
					sys_sw_software.software_name, 
					sys_sw_software.software_version
				FROM 	
					sys_sw_software, 
					system
				WHERE 	
					sys_sw_software.system_id = system.system_id AND
					sys_sw_software.timestamp = ? AND
					system.system_id = ? AND
					system.timestamp = ?";
		$sql = $this->clean_sql($sql);
		$data = array("$details->original_timestamp", "$details->system_id", "$details->timestamp");
		$query = $this->db->query($sql, $data);
		foreach ($query->result() as $myrow) { 
			if ( $myrow->software_version == '' ) {
				$version = '';
			} else {
				$version = ' (' . $myrow->software_version . ')';
			}
			$alert_details =  'software removed - ' . $myrow->software_name . $version;
			$this->m_alerts->generate_alert($details->system_id, 'sys_sw_software', $myrow->software_id, $alert_details, $details->timestamp);
		}
		
		// new software
		$sql = "SELECT sys_sw_software.software_id, sys_sw_software.software_name, sys_sw_software.software_version
				FROM 	sys_sw_software, system
				WHERE 	sys_sw_software.system_id = system.system_id AND
						sys_sw_software.timestamp = sys_sw_software.first_timestamp AND
						sys_sw_software.timestamp = ? AND
						system.system_id = ? AND
						system.timestamp = ?";
		$sql = $this->clean_sql($sql);
		$data = array("$details->timestamp", "$details->system_id", "$details->timestamp");
		$query = $this->db->query($sql, $data);
		foreach ($query->result() as $myrow) {
			if ((mb_strpos($myrow->software_name, 'Hotfix') !== false) OR (mb_strpos($myrow->software_name, 'Update') !== false) OR (mb_strpos($myrow->software_name, '(KB') !== false)){
				$alert_details = 'software update installed - ';
			} else {
				$alert_details = 'software installed - ';
			}
			$alert_details .= $myrow->software_name . ' (' . $myrow->software_version . ')';
			$this->m_alerts->generate_alert($details->system_id, 'sys_sw_software', $myrow->software_id, $alert_details, $details->timestamp);
		}
	}
}
?>
