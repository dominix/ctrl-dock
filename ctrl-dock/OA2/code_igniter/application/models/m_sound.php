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

class M_sound extends MY_Model {

	function __construct() {
		parent::__construct();
	}

	function get_system_sound($system_id) {
		$sql = "SELECT 
					sound_id, 
					sound_name, 
					sound_manufacturer
				FROM 
					sys_hw_sound,
					system
				WHERE 
					sys_hw_sound.system_id = system.system_id AND
					sys_hw_sound.timestamp = system.timestamp AND
					system.system_id = ?
				GROUP BY 
					sound_id";
		$sql = $this->clean_sql($sql);
		$data = array("$system_id");
		$query = $this->db->query($sql, $data);
		$result = $query->result();
		return ($result);
	}

	function process_sound_cards($input, $details) {
		// need to check for sound card changes
		$sql = "SELECT sys_hw_sound.sound_id
				FROM sys_hw_sound, system 
				WHERE sys_hw_sound.system_id = system.system_id AND 
					system.system_id = ? AND
					system.man_status = 'production' AND
					sound_manufacturer = ? AND 
					sound_name = ? AND
					( sys_hw_sound.timestamp = ? OR 
					sys_hw_sound.timestamp = ? )";
		$sql = $this->clean_sql($sql);
		$data = array("$details->system_id", 
				"$input->sound_manufacturer", 
				"$input->sound_name", 
				"$details->original_timestamp", 
				"$details->timestamp");
		$query = $this->db->query($sql, $data);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			// the optical_drive exists - need to update its timestamp
			$sql = "UPDATE sys_hw_sound SET timestamp = ? WHERE sound_id = ?";
			$data = array("$details->timestamp", "$row->sound_id");
			$query = $this->db->query($sql, $data);
		} else {
			// the sound card does not exist - insert it
			$sql = "INSERT INTO sys_hw_sound (	system_id, 
										sound_manufacturer, 
										sound_device_id, 
										sound_name, 
										timestamp,
										first_timestamp ) VALUES ( ?,?,?,?,?,? )";
			$sql = $this->clean_sql($sql);
			$data = array("$details->system_id", 
					"$input->sound_manufacturer", 
					"$input->sound_device_id", 
					"$input->sound_name", 
					"$details->timestamp", 
					"$details->timestamp");
			$query = $this->db->query($sql, $data);
		}
	}

	function alert_sound($details) {
		// sound no longer detected
		$sql = "SELECT 
					sys_hw_sound.sound_id, 
					sys_hw_sound.sound_name
				FROM 	
					sys_hw_sound, 
					system
				WHERE 	
						sys_hw_sound.system_id = system.system_id AND
						sys_hw_sound.timestamp = ? AND
						system.system_id = ? AND
						system.timestamp = ?";
		$sql = $this->clean_sql($sql);
		$data = array("$details->original_timestamp", "$details->system_id", "$details->timestamp");
		$query = $this->db->query($sql, $data);
		foreach ($query->result() as $myrow) {
			$alert_details = 'sound card removed - ' . $myrow->sound_name;
			$this->m_alerts->generate_alert($details->system_id, 'sys_hw_sound', $myrow->sound_id, $alert_details, $details->timestamp);
		}

		// new sound card
		$sql = "SELECT  
					sys_hw_sound.sound_id, 
					sys_hw_sound.sound_name
				FROM 	
					sys_hw_sound, 
					system
				WHERE 	
						sys_hw_sound.system_id = system.system_id AND
						sys_hw_sound.timestamp = sys_hw_sound.first_timestamp AND
						sys_hw_sound.timestamp = ? AND
						system.system_id = ? AND
						system.timestamp = ?";
		$sql = $this->clean_sql($sql);
		$data = array("$details->timestamp", "$details->system_id", "$details->timestamp");
		$query = $this->db->query($sql, $data);
		foreach ($query->result() as $myrow) {
			$alert_details = 'sound card installed - ' . $myrow->sound_name;
			$this->m_alerts->generate_alert($details->system_id, 'sys_hw_sound', $myrow->sound_id, $alert_details, $details->timestamp);
		}
	}
}
?>