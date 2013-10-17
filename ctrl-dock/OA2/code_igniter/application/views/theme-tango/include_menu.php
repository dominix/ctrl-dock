<div id="menu" style="float: left; width: 100%; ">
<ul id="nav">
	<?php
	# add any custom reports to the $menu array
	# leave the ID blank
	# the name will have spaces converted to underscores and that function from controllers/report.php will be called
	# reset the new_object each time - just copy/paste the four lines below for each report
	
	if ($this->session->userdata('user_sam') > '0') { 
		$new_object = new stdClass();
		$new_object->report_id = '';
		$new_object->report_name = 'Software Licensing';
		$menu[] = $new_object;
	}



	#a sort function for $menuso the items added above are in their correct alphabetical order
	function cmp($a, $b)
	{
		return strcmp($a->report_name, $b->report_name);
	}
	
	#use the function above
	usort($menu, "cmp");
	
	if (isset($group_id)){
		//echo "<li><a href='" . base_url() . "index.php/main/list_devices/" . $group_id . "'>" . mb_strtoupper(__('Reports')) . "</a>\n";
		//echo "<ul>\n";
		echo "<li style=\"width:200px;\"><a href='" . base_url() . "index.php/main/list_devices/" . $group_id . "'>List All Hosts</a></li>\n";
		foreach ($menu as $report):
			if ($report->report_id > '') {
				echo "<li style=\"width:200px;\"><a href='" . base_url() . "index.php/report/show_report/" . $report->report_id . "/" . $group_id . "'>" .  __($report->report_name) . "</a></li>\n";
			} else {
				echo "<li style=\"width:200px;\"><a href='" . base_url() . "index.php/report/" . strtolower(str_replace(" ", "_", $report->report_name)) . "/" . $group_id . "'>" .  __($report->report_name) . "</a></li>\n";
			}
		endforeach;
		//echo "</ul>\n";
	//echo "</li>\n";
	} ?>
	<!--
	<li><a href='#'><?php echo mb_strtoupper(__('Changes'))?></a>
		<ul>
			<li><a href='<?php echo base_url()?>index.php/change/add_change'><?php echo __('Add a Change')?></a></li>
		</ul>
	</li>
	-->
	<!--
	<?php
	if ($this->session->userdata('user_sam') > '2') { ?>
		<li><a href='#'><?php echo mb_strtoupper(__('Licensing'))?></a>
			<ul>
				<li><a href='<?php echo base_url()?>index.php/admin_sam/add_software_definition'><?php echo __('Add a Package')?></a></li>
			</ul>
		</li>
	<?php } ?>
	-->
	<?php
	/*
	if ($user_admin == 'y') { ?>
	<!-- // Only display the below code if the logged in user is an Admin -->
	<li><a href='#'><?php echo mb_strtoupper(__('Admin'))?></a>
		<ul>

			<li><a href='javascript:void(0)'><?php echo __('Reports')?></a>
				<ul>
					<li><a href='<?php echo base_url()?>index.php/admin_report/list_reports'><?php echo __('List Reports')?></a></li>
					<li><a href='<?php echo base_url()?>index.php/admin_report/activate_report'><?php echo __('Activate Report')?></a></li>
					<li><a href='<?php echo base_url()?>index.php/admin_report/import_report'><?php echo __('Import Report')?></a></li>
				</ul>
			</li>
		</ul>
	</li>
	<?php
	}
	*/
	?>

	<?php
	if (($user_admin == 'y') and (isset($config->nmis) and $config->nmis == 'y')){ ?>
	<!-- // Only display the below code if the logged in user is an Admin -->
	<li><a href='#'><?php echo mb_strtoupper(__('NMIS'))?></a>
		<ul>
			<li><a href='<?php echo base_url()?>index.php/admin/import_nmis'><?php echo __('Import')?></a></li>
			<?php if (isset($group_id)){ ?>
			<li><a href='<?php echo base_url()?>index.php/admin/export_nmis/<?php echo $group_id; ?>'><?php echo __('Export')?></a></li>
			<?php } ?>
		</ul>
	</li>
	<?php
	}
	?>
	<?php if (isset($export_report)) { ?>
		<?php if (isset($group_id)) { ?>
			<?php if (($config->non_admin_search == 'y') or ($user_admin == 'y')) { ?>
				<li style="float: right; position: relative; top:-2px; padding-right: 4px;">
					<form name="search_form" action="<?php echo base_url()?>index.php/main/search/<?php echo $group_id; ?>/" method="post">
						<table>
							<tr>
								<td><input type="text" name="search"/></td>
								<td><input type="submit" value="Search" /></td>
							</tr>
						</table>
					</form>
				</li>
			<?php } ?>
		<?php } ?>
	<?php
		// see if this is a report with a timestamp column - if so, display an RSS icon 
		if (isset($column)){
			$hit = 'n';
			foreach($column as $col) {
				if (strpos($col->column_variable, "timestamp") !== false) {
					$hit = 'y';
				}
			}
			if ($hit == 'y'){ ?>
				<li style="float: right; position: relative; top:-1px; padding-right: 6px;"><a href="<?php echo base_url() . 'index.php/' . uri_string(); ?>/username/<?php echo $this->session->userdata['username']; ?>/password/YOUR_PASSWORD/rss"><img src="<?php echo base_url()?>theme-<?php echo $user_theme;?>/<?php echo $user_theme;?>-images/16_rss.png" alt="RSS Link" title="RSS Link"/></a></li>
			<?php } 
		} ?>
		<li style="float: right; position: relative; top:-1px; padding-right: 6px;"><a href="<?php echo base_url() . 'index.php/' . uri_string(); ?>/xml"><img src="<?php echo base_url()?>theme-<?php echo $user_theme;?>/<?php echo $user_theme;?>-images/16_text-x-generic-template.png" alt="Export as XML" title="Export as XML"/></a></li>
		<li style="float: right; position: relative; top:-1px; padding-right: 3px;"><a href="<?php echo base_url() . 'index.php/' . uri_string(); ?>/csv"><img src="<?php echo base_url()?>theme-<?php echo $user_theme;?>/<?php echo $user_theme;?>-images/16_csv.png" alt="Export as CSV" title="Export as CSV"/></a></li>
		<li style="float: right; position: relative; top:-1px; padding-right: 3px;"><a href="<?php echo base_url() . 'index.php/' . uri_string(); ?>/json"><img src="<?php echo base_url()?>theme-<?php echo $user_theme;?>/<?php echo $user_theme;?>-images/16_json.png" alt="Export as JSON" title="Export as JSON"/></a></li>
		<li style="float: right; position: relative; top:-1px; padding-right: 3px;"><a href="<?php echo base_url() . 'index.php/' . uri_string(); ?>/excel"><img src="<?php echo base_url()?>theme-<?php echo $user_theme;?>/<?php echo $user_theme;?>-images/16_excel.png" alt="Export as Excel" title="Export as Excel"/></a></li>
	<?php } ?>
</ul>
</div>
<br />
<script type="text/javascript">
	$(function() {
		$('#nav').droppy({speed: 60});
	});
</script>
