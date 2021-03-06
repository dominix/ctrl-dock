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

class M_oa_device extends MY_Model {

	function __construct() {
		parent::__construct();
	}

	/**
	 * Get the device details from the id
	 *
	 * @access	public
	 * @param	device_id
	 * @return	string
	 */
	function get_device($id) {
		$sql = "SELECT * FROM oa_device WHERE device_id = ? LIMIT 1";
		$data = array("$id");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);
	}

	/**
	 * Get the device's group id 
	 *
	 * @access	public
	 * @param	device_id
	 * @return	string
	 */
	function get_group_id($device_id) {
		$sql = "SELECT device_group_id FROM oa_device WHERE device_id = ? LIMIT 1";
		$data = array("$device_id");
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		return ($row->device_group_id);
	}
	
	/**
	 * Set the device's group id 
	 *
	 * @access	public
	 * @param	device_id
	 * @return	string
	 */
	function set_group_id($device_id, $group_id) {
		$sql = "UPDATE oa_device SET device_group_id = ? WHERE device_id = ? ";
		$data = array("$group_id", "$device_id");
		$query = $this->db->query($sql, $data);
	}

	/**
	 * Get the device's details, including the number of devices in the corresponding group 
	 *
	 * @access	public
	 * @param	device_id
	 * @return	string
	 */
	function get_device_details($org_id) {
		$sql = "SELECT oa_device.*, count(oa_group_sys.system_id) as total FROM oa_device LEFT JOIN oa_group_sys ON oa_group_sys.group_id = oa_device.device_group_id where oa_device.device_id = ? GROUP BY oa_device.device_id LIMIT 1";
		$data = array("$org_id");
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		return ($row);
	}

	/**
	 * Get the device id from the name
	 *
	 * @access	public
	 * @param	name of the device
	 * @return	string
	 */
	function get_device_id($name) {
		$sql = "SELECT device_id FROM oa_device WHERE device_name = ? LIMIT 1";
		$data = array("$name");
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		if ($query->num_rows() > 0) {
			$row = $query->row(); 
			return ($row->device_id);
		} else {
			return null;
		}
	}

	/**
	 * Get the device name from the id
	 *
	 * @access	public
	 * @param	id of the device
	 * @return	string
	 */
	function get_device_name($id) {
		$sql = "SELECT device_name FROM oa_device WHERE device_id = ? LIMIT 1";
		$data = array("$id");
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		return ($row->device_name);
	}

	/**
	 * Get all the devices names 
	 *
	 * @access	public
	 * @return	array
	 */
	function get_device_names() {
		$sql = "SELECT device_name, device_id FROM oa_device ORDER BY device_name";
		$query = $this->db->query($sql);
		$result = $query->result();
		return ($result);
	}

	/**
	 * Get all the devices details 
	 *
	 * @access	public
	 * @return	array
	 */
	function get_all_devices() {
		$sql = "SELECT oa_device.*, count(oa_group_sys.system_id) as total FROM oa_device LEFT JOIN oa_group_sys ON oa_group_sys.group_id = oa_device.device_group_id GROUP BY oa_device.device_id ORDER BY oa_device.device_name";
		$sql = $this->clean_sql($sql);
		$query = $this->db->query($sql);
		$result = $query->result();
		return ($result);
	}

	/**
	 * Check the device name to see if it exists, not including the supplied id
	 *
	 * @access	public
	 * @param	device_name the name of the device
	 * @param	device_id the ID of the device
	 * @return	boolean
	 */
	function check_device_name($device_name, $device_id) {
		$sql = "SELECT device_id FROM oa_device WHERE device_name = ? AND device_id <> ?";
		$data = array("$device_name", "$device_id");
		$query = $this->db->query($sql, $data);
		$row = $query->row();
		if ($query->num_rows() > 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Get the device name of a supplied system
	 *
	 * @access	public
	 * @param	system_id the ID of the system
	 * @return	string
	 */
	function get_system_device($id) {
		$sql = "SELECT oa_device.* FROM oa_device, system WHERE oa_device.device_id = system.man_device_id AND system.system_id = ? LIMIT 1";
		$sql = $this->clean_sql($sql);
		$data = array("$id");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);
	}

	/**
	 * Add a device to the database - Admin only
	 *
	 * @access	public
	 * @param	details - the relevant device details object
	 * @return	string
	 */
	function add_device($details) {
		$sql = "INSERT INTO oa_device 
				(device_name, 
				device_room, 
				device_suite, 
				device_level, 
				device_address, 
				device_city, 
				device_state,
				device_country,
				device_picture,
				device_latitude,
				device_longitude) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$sql = $this->clean_sql($sql);
		$data = array("$details->device_name", 
			"$details->device_room", 
			"$details->device_suite", 
			"$details->device_level", 
			"$details->device_address", 
			"$details->device_city", 
			"$details->device_state", 
			"$details->device_country", 
			"$details->device_picture", 
			"$details->device_latitude", 
			"$details->device_longitude");
		$query = $this->db->query($sql, $data);
		return($this->db->insert_id());
	}

	/**
	 * Delete a device from the database - Admin only
	 *
	 * @access	public
	 * @param	detail id - the id of the device to delete
	 * @return	nothing
	 */
	function delete_device($device_id) {
		$sql = "DELETE FROM oa_device WHERE device_id = ?";
		$data = array("$device_id");
		$query = $this->db->query($sql, $data);
		$sql = "UPDATE system SET man_device_id = '' WHERE man_device_id = ?";
		$data = array("$device_id");
		$query = $this->db->query($sql, $data);
	}

	/**
	 * Update a device in the database - Admin only
	 *
	 * @access	public
	 * @param	details - the relevant device details object
	 * @return	TRUE
	 */
	function edit_device($details) {
		$sql = "UPDATE 
				oa_device
			SET
				device_name = ?, 
				device_type = ?, 
				device_room = ?, 
				device_suite = ?, 
				device_level = ?, 
				device_address = ?, 
				device_city = ?, 
				device_state = ?,
				device_country = ?,
				device_picture = ?,
				device_latitude = ?,
				device_longitude = ?
			WHERE
				device_id = ?";
		$sql = $this->clean_sql($sql);
		$data = array("$details->device_name", 
			"$details->device_type", 
			"$details->device_room", 
			"$details->device_suite", 
			"$details->device_level", 
			"$details->device_address", 
			"$details->device_city", 
			"$details->device_state", 
			"$details->device_country", 
			"$details->device_picture", 
			"$details->device_latitude", 
			"$details->device_longitude", 
			"$details->device_id");
		$query = $this->db->query($sql, $data);
		return(TRUE);
	}
	
	/**
	 * List all devices in a given device
	 *
	 * @access	public
	 * @param	device_id
	 * @return	array
	 */
	function list_devices_in_device($device_id = 0, $user_id = 0) {
		// we have not requested a specific group.
		// display all items the current user has at least 'level 3' - view list rights on.
		$sql = "SELECT 
				system.system_id, 
				system.hostname, 
				system.man_description, 
				system.man_ip_address, 
				system.man_icon, 
				system.man_os_name, 
				system.man_os_family 
			FROM 
				system, 
				oa_group, 
				oa_group_sys, 
				oa_group_user 
			WHERE 
				system.system_id IN ( 
					SELECT 
						system.system_id 
					FROM 
						system, 
						oa_group_sys, 
						oa_group, 
						oa_group_user 
					WHERE 
						system.man_status = 'production' AND 
						system.system_id = oa_group_sys.system_id AND 
						oa_group_sys.group_id = oa_group.group_id AND 
						oa_group.group_id = oa_group_user.group_id AND 
						oa_group_user.user_id = ? 
						) AND 
				system.system_id = oa_group_sys.system_id AND 
				oa_group_sys.group_id = oa_group.group_id AND 
				oa_group.group_id = oa_group_user.group_id AND 
				oa_group_user.group_user_access_level > '2' AND
				oa_group_user.user_id = ? AND 
				system.man_device_id = ? 
			GROUP BY system.system_id ";
		$sql = $this->clean_sql($sql);
		$data = array("$user_id", "$user_id", "$device_id");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);	
	}
}
?>