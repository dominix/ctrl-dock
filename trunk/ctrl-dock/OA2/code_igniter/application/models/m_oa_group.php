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

class M_oa_group extends MY_Model {

	function __construct() {
		parent::__construct();
	}

	function get_group_access($group_id, $user_id = '0') {
		$sql = "SELECT group_user_access_level FROM oa_group_user WHERE user_id = ? AND group_id = ? LIMIT 1";
		$data = array("$user_id", "$group_id");
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		return $row->group_user_access_level;
	}

	function get_group_name($group_id) {
		$group_id = intval($group_id);
		$sql = "SELECT group_name from oa_group WHERE group_id = ? LIMIT 1";
		$data = array("$group_id");
		$query = $this->db->query($sql, $data);
		$row = $query->row(); 
		$group_name = $row->group_name;
		if ($group_name != '') {
			return $group_name;
		} else {
			return 'All Devices';
		}
	}

	function get_group($id = '0') {
		if ($id == '0') { return '0'; }
		$sql = "SELECT * FROM oa_group WHERE group_id = ?";
		$data = array("$id");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);
	}

	function get_group_id($group_name) {
		$sql = "SELECT group_id FROM oa_group WHERE group_name = ? LIMIT 1";
		$data = array("$group_name");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		foreach ($result as $key) {
			$group_id = $key->group_id;
		}
		if (isset($group_id)) {
			return($group_id);
		} else {
			return(NULL);
		}
	}

	function get_group_dynamic_select($group_id) {
		$group_id = intval($group_id);
		$sql = "SELECT group_dynamic_select from oa_group WHERE group_id = ? LIMIT 1";
		$data = array("$group_id");
		$query = $this->db->query($sql, $data);
		$row = $query->row(); 
		$group_dynamic_select = $row->group_dynamic_select;
		if ($group_dynamic_select != '') {
			return $group_dynamic_select;
		} else {
			return '';
		}
	}

	function update_system_groups($details) {	
		//select all dynamic groups
		$sql = "SELECT group_id, group_name, group_dynamic_select FROM oa_group";
		$query = $this->db->query($sql);
		#$start_process_groups = explode(' ',microtime()); 
		foreach ($query->result() as $myrow) {
			# Remove the system from the group first, then add it back ONLY if it meets the criteria
			$sql_delete = "DELETE FROM oa_group_sys WHERE system_id = ? AND group_id = ?";
			$data_delete = array("$details->system_id", "$myrow->group_id");
			$query_delete = $this->db->query($sql_delete, $data_delete);
			$sql_select = "SELECT system.system_id, system.type FROM system WHERE system_id = ? AND system_id in ( " . $myrow->group_dynamic_select . " )";
			$data_select = array("$details->system_id", "$myrow->group_id");
			$query_select = $this->db->query($sql_select, $data_select);
			if ($query_select->num_rows() > 0){
				// insert a row because the system matches the select criteria
				$sql_insert = "INSERT INTO oa_group_sys (system_id, group_id, group_sys_type) VALUES (?, ?, ?)";
				$data_insert = array("$details->system_id", "$myrow->group_id", "$details->type");
				$query_insert = $this->db->query($sql_insert, $data_insert);
			}
		}
	}

	function update_groups() {
		$sql = "SELECT group_id, group_dynamic_select, group_name FROM oa_group";
		$query = $this->db->query($sql);
		foreach ($query->result() as $myrow) {
			$sql_delete = "DELETE FROM oa_group_sys WHERE group_id = '" . $myrow->group_id . "'";
			$delete = $this->db->query($sql_delete) or die ("Error with delete from oa_group_sys");
			$sql_select = $myrow->group_dynamic_select;
			# update the group with all systems that match
			$sql_insert = substr_replace($sql_select, "INSERT INTO oa_group_sys (system_id, group_id, group_sys_type) ", 0, 0);
			$sql_insert = str_ireplace("SELECT DISTINCT(system.system_id)", "SELECT DISTINCT(system.system_id), '" . $myrow->group_id . "', 'system'", $sql_insert);
			$insert = $this->db->query($sql_insert);
		}
	}

	function update_specific_group($group_id) {
		# get the group select
		$sql = "SELECT group_dynamic_select FROM oa_group WHERE group_id = ? LIMIT 1";
		$data = array($group_id);
		$query = $this->db->query($sql, $data);
		foreach ($query->result() as $myrow) {
			$sql_select = $myrow->group_dynamic_select;
		}		
		# remove the existing systems in this group
		$sql_delete = "DELETE FROM oa_group_sys WHERE group_id = ?";
		$data = array($group_id);
		$delete = $this->db->query($sql_delete, $data) or die ("Error with delete from oa_group_sys");
		# update the group with all systems that match
		$sql_insert = substr_replace($sql_select, "INSERT INTO oa_group_sys (system_id, group_id, group_sys_type) ", 0, 0);
		$sql_insert = str_ireplace("SELECT DISTINCT(system.system_id)", "SELECT DISTINCT(system.system_id), '" . $group_id . "', 'system'", $sql_insert);
		$insert = $this->db->query($sql_insert);
	}

	function get_tables() {
		$result = $this->db->list_tables();
		return ($result);
	}
	
	function get_fields($table) {
		$fields_in_table = $this->db->list_fields($table);
		$result = array();
		foreach ($fields_in_table as $field_in_table) {
			if ((mb_strpos($field_in_table, '_id') === false) AND (mb_strpos($field_in_table, 'timestamp') === false)) {
				$result[] = $field_in_table;
			}
		} 
		return ($result);		
	}

	function get_field_values($table, $field) {
		$sql = "SELECT DISTINCT($field) AS value FROM $table ORDER BY value";
		$query = $this->db->query($sql);
		$result = $query->result();
		return ($result);		
	}

	function update_group($details) {
		// TODO - need some data validation in here
		$sql = "
			UPDATE 
				oa_group 
			SET 
				group_name = ?, 
				group_padded_name = ?, 
				group_dynamic_select = ?, 
				group_parent = ?, 
				group_description = ?,
				group_category = ?, 
				group_display_sql = ?,
				group_icon = ? 
			WHERE
				group_id = ?";
		$sql = $this->clean_sql($sql);
		$data = array("$details->group_name", 
					"$details->group_padded_name", 
					"$details->group_dynamic_select", 
					"$details->group_parent", 
					"$details->group_description", 
					"$details->group_category", 
					"$details->group_display_sql", 
					"$details->group_icon", 
					"$details->group_id");
		$query = $this->db->query($sql, $data);
		return;
	}

	function insert_group($details) {
		// TODO - need some data validation in here
		$sql = "INSERT INTO oa_group (group_id, 
					group_name, 
					group_padded_name, 
					group_description, 
					group_icon, 
					group_category, 
					group_dynamic_select, 
					group_parent,
					group_display_sql ) 
					VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ? )";
		$sql = $this->clean_sql($sql);
		$data = array("$details->group_name", 
					"$details->group_padded_name", 
					"$details->group_description", 
					"$details->group_icon", 
					"$details->group_category", 
					"$details->group_dynamic_select", 
					"$details->group_parent", 
					"$details->group_display_sql");
		$result = $this->db->query($sql, $data);
		$return = $this->db->insert_id();
		// We need to insert an entry into oa_group_user for any Admin level user
		// TODO - maybe we should insert '0' for all non-admin users ?
		$sql = "INSERT INTO oa_group_user (SELECT NULL, user_id, ?, '10' FROM oa_user WHERE user_admin = 'y')";
		$data = array( $this->db->insert_id() );
		$result = $this->db->query($sql, $data);
		if (!is_numeric($return)) { $return = "An eror occured"; }
		return($return);
	}

	function get_tags() {
		$sql = "SELECT 	
					oa_group.group_id, 
					oa_group.group_name, 
					oa_group.group_description, 
					count(oa_group_sys.system_id) AS total 
				FROM 	
					oa_group 
				LEFT JOIN 
					oa_group_sys 
				ON 	
					oa_group.group_id = oa_group_sys.group_id 
				WHERE 	
					oa_group_sys.group_id < '999' 
				GROUP BY 
					oa_group.group_id";
		$sql = $this->clean_sql($sql);
		$query = $this->db->query($sql);
		$result = $query->result();
		return ($result);
	}

	function get_all_groups() {
		$sql = "SELECT 	
					oa_group.group_id, 
					oa_group.group_name, 
					oa_group.group_padded_name, 
					oa_group.group_icon, 
					oa_group.group_description, 
					oa_group.group_category, 
					count(oa_group_sys.system_id) AS total 
				FROM 
					oa_group 
				LEFT JOIN 
					oa_group_sys 
				ON 
					oa_group.group_id = oa_group_sys.group_id 
				GROUP BY 
					oa_group.group_id
				ORDER BY
					oa_group.group_id";
		$sql = $this->clean_sql($sql);
		$query = $this->db->query($sql);
		$result = $query->result();
		return ($result);
	}

	function count_all_groups() {
		$sql = "SELECT count(oa_group.group_id) AS total FROM oa_group LIMIT 1";
		$query = $this->db->query($sql);
		$result = $query->result();
		$row = $query->row(); 
		return ($row->total);
	}

	function get_user_groups($user_id = '0') {
		$sql = "SELECT 	
					oa_group.group_id, 
					oa_group.group_name, 
					oa_group.group_padded_name, 
					oa_group.group_icon, 
					oa_group.group_description, 
					oa_group.group_category, 
					count(oa_group_sys.system_id) AS total 
				FROM 
					oa_group, 
					oa_group_user, 
					oa_group_sys 
				WHERE 
					oa_group.group_id = oa_group_user.group_id AND
					oa_group_user.user_id = ? AND
					oa_group_user.group_user_access_level > 0 AND
					oa_group.group_id = oa_group_sys.group_id
				GROUP BY 
					oa_group.group_id
				ORDER BY 
					oa_group.group_category, 
					oa_group.group_padded_name";
		$sql = $this->clean_sql($sql);
		$data = array("$user_id");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);
	}

	function get_all_user_groups($id) {
		// get ALL groups, even those with no entry in the oa_group_user table.
		// assign a permission level to each group. Use '0' for those without entries in oa_group_user
		$sql = "SELECT 	
					oa_group.group_id, 
					oa_group.group_name, 
					oa_group.group_description, 
					count(oa_group_sys.system_id) AS total, 
					'0' AS access_level
				FROM 
					oa_group 
				LEFT JOIN 
					oa_group_sys 
				ON 	
					oa_group.group_id = oa_group_sys.group_id 
				GROUP BY 
					oa_group.group_id
				ORDER BY
					oa_group.group_name";
		$sql = $this->clean_sql($sql);
		$query = $this->db->query($sql);
		$group = $query->result();
		// get user access levels
		$sql = "SELECT 	
					oa_group.group_id, 
					oa_group_user.group_user_access_level
				FROM 
					oa_group,
					oa_group_user
				WHERE 
					user_id = ? AND
					oa_group.group_id = oa_group_user.group_id
				GROUP BY 
					oa_group.group_id";
		$sql = $this->clean_sql($sql);
		$data = array("$id");
		$query = $this->db->query($sql, $data);
		$user = $query->result();		
		// combine the two data sets to get the user access level
		foreach ($group as $key_group) {
			//$key_group->access_level = 0;
			foreach ($user as $key_user) {
				if ($key_user->group_id == $key_group->group_id) {
					$key_group->access_level = $key_user->group_user_access_level;
				}
			}
		}
		return($group);
	}

	function delete_group($id) {
		$sql = "DELETE FROM oa_group_user WHERE group_id = ?";
		$data = array("$id");
		$query = $this->db->query($sql, $data);
		
		$sql = "DELETE FROM oa_group_sys WHERE group_id = ?";
		$data = array("$id");
		$query = $this->db->query($sql, $data);

		$sql = "DELETE FROM oa_group_column WHERE group_id = ?";
		$data = array("$id");
		$query = $this->db->query($sql, $data);

		$sql = "DELETE FROM oa_group WHERE group_id = ?";
		$data = array("$id");
		$query = $this->db->query($sql, $data);
	}

	function import_group($input) {
		//echo "<pre>\n";
		//print_r($input);
		//echo "</pre>\n";
		//echo $input->group_dynamic_select;
		//exit;
		$sql = "INSERT INTO 
					oa_group 
				SET 
					group_name = ?, 
					group_padded_name = ?, 
					group_dynamic_select = ?, 
					group_parent = ?, 
					group_description = ?, 
					group_category = ?, 
					group_display_sql = ?, 
					group_icon = ?";
		$sql = $this->clean_sql($sql);
		$data = array(	"$input->group_name", 
						"$input->group_padded_name", 
						"$input->group_dynamic_select", 
						"$input->group_parent", 
						"$input->group_description", 
						"$input->group_category", 
						"$input->group_display_sql", 
						"$input->group_icon");
		$query = $this->db->query($sql, $data);
		$group_id = $this->db->insert_id();
		// We need to insert an entry into oa_group_user for any Admin level user
		$sql = "INSERT INTO oa_group_user (SELECT NULL, user_id, ?, '10' FROM oa_user WHERE user_admin = 'y')";
		$data = array("$group_id");
		$result = $this->db->query($sql, $data);
		return($group_id);
	}

	function edit_user_groups($details) {
		# remove the existing user->group permissions
		$sql = "DELETE FROM oa_group_user WHERE user_id = ?";
		$data = array("$details->user_id");
		$query = $this->db->query($sql, $data);		
		# assign new user-> group permissions
		foreach ($details as $detail => $key) {
			$pos = mb_strpos($detail, "group_id_");
			if (( $pos !== FALSE ) AND ( $detail != "group_id_0" )) {
				$group_id_split = explode("_", $detail);
				$sql = "INSERT INTO oa_group_user (group_user_id, user_id, group_id, group_user_access_level) VALUES (NULL, ?, ?, ?)";
				$data = array("$details->user_id", $group_id_split[2], "$key");
				$query = $this->db->query($sql, $data);
			}
		}
	}
}
?>
