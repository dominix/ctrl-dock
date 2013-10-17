<table cellspacing="1" class="tablesorter" width="900">
	<thead>
		<tr>
			<th><?php echo __('Package Name')?></th>
			<th><?php echo __('Type')?></th>
			<th><?php echo __('Version')?></th>
			<th><?php echo __('Publisher')?></th>
			<th align="center"><?php echo __('Installs')?></th>
			<th align="center"><?php echo __('Licenses')?></th>
			<?php if ($this->session->userdata('user_sam') > '1') { ?>
			<!--<th align="center"><?php //echo __('Edit')?></th>-->
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach($query as $key): 
		#if ($key->software_licenses > '0' ) {$key->software_licenses = ($key->software_licenses / $key->software_count); }
		if (($key->software_licenses == '0' ) or (!isset($key->software_licenses))){
			$key->software_licenses = '-';
		} else {
			if (($key->software_licenses > '0' ) and ($key->software_licenses > $key->software_count )) { $key->software_licenses = '<font color="green">' . $key->software_licenses . '</font>';}
			if (($key->software_licenses > '0' ) and ($key->software_licenses < $key->software_count )) { $key->software_licenses = '<font color="red">' . $key->software_licenses . '</font>';}
			if (($key->software_licenses > '0' ) and ($key->software_licenses == $key->software_count )) { $key->software_licenses = '<font color="blue">' . $key->software_licenses . '</font>';}
		}
	?>
		<tr>
			<td><a href="<?php echo base_url(); ?>index.php/report/specific_software/<?php echo $group_id; ?>/<?php echo $key->software_id; ?>"><?php echo $key->software_name; ?></a></td>
			<td><?php echo $key->software_comment; ?></td>
			<td><?php echo $key->software_version; ?></td>
			<td><?php echo $key->software_publisher; ?></td>
			<td align="center"><?php echo $key->software_count; ?></td>
			<td align="center"><?php echo $key->software_licenses ?></td>
			<?php 
			if ($this->session->userdata('user_sam') > '1') {
				//echo "\t\t\t<td align=\"center\"><a class='AssetPopupTrigger' rel='" . htmlentities($key->software_name) . "' href='#' ><img src='" . $image_path . "16_edit.png' alt='' title='' width='16' /></a></td>\n";
			//} else if ($this->session->userdata('user_sam') > '1') {
				//if ($key->software_licenses == '') {
					//echo "\t\t\t<td align=\"center\"></td>\n";
				//} else {
					//echo "\t\t\t<td align=\"center\"><a href='" . $key->software_licenses . "'><img src='" . $image_path . "16_edit.png' alt='' title='' width='16' /></a></td>\n";
				//} 
			} 
			?>
		</tr>
	<?php endforeach; ?>
	<?php if (count($query) == 0) { echo "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\n"; } ?>
	</tbody>
</table>
<script type="text/javascript">
function dynamic_asset( software_name )
{
	licenses = document.getElementById("licenses").value;
	location.href = '<?php echo site_url(); ?>/admin_licenses/change_license/<?php echo $group_id; ?>/' + licenses + '/' + software_name;
	return false;
}
</script>
