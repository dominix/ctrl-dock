<?php $sortcolumn = 3; 
# check to see if user_access_level for this group is > 7
$manual_edit = 'n';
if ( isset($user_access_level) and $user_access_level > '9' ) {
		# check to see if "system_id" is present in report
		if ( isset($query[0]->system_id) ){
			# enable group manual editing column
			$manual_edit = 'y';
		}
}

if ($manual_edit == 'y') {
	echo "<div style=\"float:left; width:100%;\">\n";
	$attributes = array('id' => 'change_form', 'name' => 'change_form');
	echo form_open('main/edit_systems', $attributes) . "\n"; 
	echo "<input type=\"hidden\" name=\"group_id\" value=\"" . $group_id . "\" />\n";
}




$columns = $column;
unset($column);






echo "<table cellspacing=\"1\" class=\"tablesorter\">\n";
echo "\t<thead>\n";
echo "\t\t<tr>\n";
foreach($columns as $column) {
	if ($column->column_type > '') {
		if ($column->column_align == 'right') {
			$style = 'padding-right: 20px;';
		} else {
			$style = '';
		}
		if ($column->column_name=="Tags") {$column->column_name="";}
		echo "\t\t\t<th style=\"text-align: $column->column_align; $style\">" . $column->column_name . "</th>\n";
	}
}

if ( ($manual_edit == 'y') and ($system_id = "set") ){
		//echo "<th align=\"center\" class=\"{sorter: false}\"><button onClick=\"document.change_form.submit();\">Edit</button>";
		//echo "<input type=\"checkbox\" id=\"system_id_0\" name=\"system_id_0\" onchange=\"check_all_systems();\"/></th>";
}

echo "\t\t</tr>\n";
echo "\t</thead>\n";
echo "\t<tbody>\n";
$i = 0;
foreach($query as $row) {
	$i++;
	echo "\t\t<tr>\n";
	foreach($columns as $column) {
		$column_variable_name = $column->column_variable;
		$column_variable_name_sec = $column->column_secondary;
		$column_variable_name_ter = $column->column_ternary;
		$column_link = $column->column_link;
		$column_align = $column->column_align;
		$column_type = $column->column_type;
		if ($column_align == '') { $column_align = 'left'; }
		if (!property_exists($row, 'system_id')) { $row->system_id = $i; }
		if (!isset($row->system_id)) { $row->system_id = $i; }
		if (($column_variable_name == 'hostname') and ($row->$column_variable_name == '')) {
			$row->hostname = "-";
		}

		switch($column_type) {	
			case "":
				break;

				case "link":
					if ($row->$column_variable_name == '') { $row->$column_variable_name = '-'; }
					if ($column_variable_name_sec == 'system_id' or $column_variable_name_sec == 'linked_sys') {
						$column_link = str_replace('$group_id', $group_id, $column_link);
						echo "\t\t\t<td align=\"$column_align\"><a class=\"SystemPopupTrigger\" rel=\"" . $row->$column_variable_name_sec . "\" href=\"" . site_url()  . $column_link . $row->$column_variable_name_sec . "\">" . $row->$column_variable_name . "</a></td>\n";
					} else {
						$column_link = str_replace('$group_id', $group_id, $column_link);
						echo "\t\t\t<td align=\"$column_align\"><a href=\"" . site_url() . $column_link . $row->$column_variable_name_sec . "\">" . htmlentities($row->$column_variable_name, ENT_QUOTES, "UTF-8") . "</a></td>\n";
					}
					break;

			case "text":
				switch($column_variable_name)
				{
				case "tag":
					echo "\t\t\t<td align=\"center\"><a href='../../../../ezasset/del_system.php?id=$row->system_id'>Delete</a></td>\n";
				break;

				default:
					if (isset($row->$column_variable_name)) {
						$output = $row->$column_variable_name;
						if (is_numeric($output) and (strpos($column_variable_name, "serial") === false) and (strpos($column_variable_name, "model") === false)) { 
							echo "\t\t\t<td align=\"right\"><span style=\"display: none;\">" . mb_substr("0000000000" . $output, -10) . "</span><span id=\"" . $column_variable_name . "-" . $i . "\" onMouseOver=\"show_modifier('" . $column_variable_name . "','" . $i . "');\"  >" . $output . "</span><span id=\"" . $row->$column_variable_name . "-" . $i . "\">&nbsp;&nbsp;&nbsp;</span></td>\n";
						} else {
							if ($row->$column_variable_name == ''){ $row->$column_variable_name = ' '; }
							echo "\t\t\t<td align=\"$column_align\"><span id=\"" . $column_variable_name . "-" . $i . "\" onMouseOver=\"show_modifier('" . $column_variable_name . "','" . $i . "');\"  >" . htmlentities($row->$column_variable_name, ENT_QUOTES, "UTF-8") . "</span><span id=\"" . $row->$column_variable_name . "-" . $i . "\">&nbsp;&nbsp;&nbsp;</span></td>\n";
						}
					} else {
						echo "\t\t\t<td></td>\n";
					}
					break;
				}
				break;

			case "image":
				if ($row->$column_variable_name == "") { $row->$column_variable_name = "unknown"; }
				if ($column_align == '') {$column_align = 'center';}
				if ($column->column_name == 'Icon') {
					echo "\t\t\t<td style=\"text-align: center;\"><img src=\"" . str_replace("index.php", "", site_url()) . "theme-tango/tango-images/16_" . strtolower(str_replace(" ", "_", $row->$column_variable_name)) . ".png\" style='border-width:0px;' title=\"" . $row->$column_variable_name_sec . "\" alt=\"" . $row->$column_variable_name_sec . "\" /></td>\n";
				}
				if ($column->column_name == 'Picture') {
					echo "\t\t\t<td style=\"text-align: center;\"><img src=\"" . str_replace("index.php", "", site_url()) . "device_images/" . $row->$column_variable_name . ".jpg\" style='border-width:0px; height:100px' title=\"" . $row->$column_variable_name_sec . "\" alt=\"" . $row->$column_variable_name_sec . "\" /></td>\n";
				}
				break;

			case "ip_address":
				echo "\t\t\t<td style=\"text-align: $column_align;\"><span style=\"display: none;\">" . $row->man_ip_address . "&nbsp;</span>" . ip_address_from_db($row->man_ip_address) . "</td>\n";
				break;

			case "multi":
				echo "\t\t\t<td style=\"text-align: $column_align;\">" . str_replace(",  ", ",<br />", $row->$column_variable_name) . "</td>\n";
				break;
				
			case "timestamp":
				echo "\t\t\t<td style=\"text-align: $column_align;\">" . $row->$column_variable_name . "</td>\n";
				break;
			
			case "url":
				$href = '';
				if ($column_variable_name_ter > '') {
					$image = base_url() . "theme-tango/tango-images/16_" . $column_variable_name_ter . ".png";
				} else {
					$image = base_url() . "theme-tango/tango-images/16_browser.png";
				}
				
				if (isset($row->$column_variable_name)) { 
					$href = str_replace("&", "&amp;", str_replace("&amp;", "&", $row->$column_variable_name));
				}
				if (($column_variable_name == '') && ($column_link > '')) {
					$href = htmlentities($column_link, ENT_QUOTES, "UTF-8");
				}
				if ($column_variable_name_sec > '') {
					$href .= htmlentities($row->$column_variable_name_sec, ENT_QUOTES, "UTF-8");
				}
				$href = str_replace(" ", "%20", $href);
				if ($href > '') {
					echo "\t\t\t<td style=\"text-align: $column_align;\"><a href=\"" . $href . "\"><img src=\"" . $image . "\" border=\"0\" title=\"\" alt=\"\" /></a></td>";
				} else {
					echo "\t\t\t<td style=\"text-align: $column_align;\"></td>\n";
				}
				break;
				
			#default:
			#	echo "\t\t\t<td align=\"$column_align\">" . $row->$column_variable_name . "</td>\n";
			#	break;
		}
	}
	
	if ( $manual_edit == 'y') { 
		//echo "\t\t\t<td align=\"center\"><input type=\"checkbox\" id=\"system_id_" . $row->system_id . "\" name=\"system_id_" . $row->system_id . "\" /></td>\n";
	}
	echo "\n\t\t</tr>\n";
}
echo "\t</tbody>\n";
echo "</table>\n";
if ($manual_edit == 'y') {
	echo "</form>\n";
	echo "</div>\n";
}
?>

<script type="text/javascript">
oa_cell_id = "";
oa_cell_value = "";
var x = new Array(<?php echo count($query); ?>);

function show_modifier(oa_attribute, system_id)
{
	oa_new_cell_id = oa_attribute + "-" + system_id;
	if (oa_cell_id == oa_new_cell_id) {
		
	} else {
		if (oa_cell_id > "") {
			document.getElementById(oa_cell_id).innerHTML = oa_cell_value;
		}
		oa_cell_id = oa_attribute + "-" + system_id;
		oa_cell_value = document.getElementById(oa_cell_id).innerHTML;
		oa_cell_icon = " <a class='ModifierPopupTrigger' rel='" + oa_attribute + "___" + document.getElementById(oa_cell_id).innerHTML +"' href='#'>***<\/a>";
		oa_cell_text = oa_cell_value + oa_cell_icon;
		document.getElementById(oa_cell_id).innerHTML = oa_cell_text;
	}
}

function check_all_systems()
{
	if (document.getElementById("system_id_0").checked == true) 
	{
		<?php
		foreach($query as $key):
			if (isset($key->system_id)) {
				echo "\tdocument.getElementById(\"system_id_" . $key->system_id . "\").checked = true;\n";
			}
		endforeach;
		?>
	} else {
		<?php
		foreach($query as $key):
			if (isset($key->system_id)) {
				echo "\tdocument.getElementById(\"system_id_" . $key->system_id . "\").checked = false;\n";
			}
		endforeach;
		?>
	}
}
</script>

<?php
function replace_amp($string)
{
	$replaced_amp = str_replace("&amp;", "&", $string);
	$replaced_amp = str_replace("&", "&amp;", $replaced_amp);
	return $replaced_amp;
}
?>

<div style="display: none;" id="example" title="Edit Systems Manual Data"></div>
