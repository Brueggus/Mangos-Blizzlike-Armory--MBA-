<?php
if(!defined("Armory"))
{
	header("Location: ../error.php");
	exit();
}
$skill_ihl = array(
6 => "weaponskills",
7 => "classskills",
8 => "armorproficiencies",
9 => "secondaryskills",
10 => "languages",
11 => "professions",
);
switchConnection("armory", REALM_NAME);
$get_all_skill_categories = execute_query("SELECT `id`, `name` FROM `dbc_skilllinecategory` WHERE `id` > 5 AND `id` < 12 ORDER BY `display_order`");
while($skill_category = mysql_fetch_assoc($get_all_skill_categories))
	$all_skill_categories[$skill_category["id"]] = $skill_category["name"];
$temp_out = array();
foreach($all_skill_categories as $key => $value)
{
	$temp_out[$key] = array("<div class=\"inner-cont\">
<table class=\"iht\">
<tr>
<td class=\"ihl\"><span class=\"skill-".$skill_ihl[$key]."\">
<p>".$value."</p>
</span></td><td class=\"ihrc\"></td>
</tr>
</table>
<table>
<tr>
<td class=\"il\"></td><td class=\"ibg\">
<div class=\"profile-wrapper\">",0);
}
?>
<div class="parch-profile-banner" id="banner" style="margin-top: -7px!important;">
<h1><?php echo $lang["skills"] ?></h1>
</div>
<?php
$statistic_data = explode(" ",$data["data"]);
for($i = $defines["SKILL_DATA"][CLIENT]; $i <= $defines["SKILL_DATA"][CLIENT]+384 ; $i += 3)
{
	$skill_id = ($statistic_data[$i] & 0x0000FFFF);
	$get_skill_info = execute_query("SELECT * FROM `dbc_skillline` WHERE  `id` = ".$skill_id." LIMIT 1");
	if(($skill_info = mysql_fetch_assoc($get_skill_info)) && isset($all_skill_categories[$skill_info["ref_skilllinecategory"]]))
	{
		$skill_points = unpack("S", pack("L", $statistic_data[$i+1]));
		switch($skill_info["ref_skilllinecategory"])
		{
			case 7: case 8: $skill_data = ""; $skill_data_percent = 100; break;
			case 10: $skill_data = $skill_points[1]."/".$skill_points[1]; $skill_data_percent = 100; break;
			case 6: $max_skill = $stat["level"] * 5; $skill_data = $skill_points[1]."/".$max_skill;
				$skill_data_percent = 100*$skill_points[1]/$max_skill; break;
			default: $max_skill = get_prof_max($skill_points[1]); $skill_data = $skill_points[1]."/".$max_skill;
				$skill_data_percent = 100*$skill_points[1]/$max_skill;
		}
		$temp_out[$skill_info["ref_skilllinecategory"]][0] .= "<div class=\"rep7\">
<div class=\"rep-lbg\">
<div class=\"rep-lr\">
<div class=\"rep-ll\">
<ul>
<li class=\"faction-name\">
<span onMouseOut=\"hideTip();\" onMouseOver=\" showTip('".$skill_info["description"]."');\">
<img class=\"socketImg\" src=\"".GetIcon("spell", $skill_info["ref_spellicon"])."\">
<a>".$skill_info["name"]."</a></span>
</li>
<li class=\"faction-bar skill-length\">
<a class=\"skill-data\">".$skill_data."</a>
<div class=\"bar-color\" style=\" width: ".$skill_data_percent."%\"></div>
</li>
<li class=\"skill-level\">
<p class=\"rep-icon\"></p>
</li>
</ul>
</div>
</div>
</div>
</div>";
		$temp_out[$skill_info["ref_skilllinecategory"]][1] = 1;
	}
}
unset($statistic_data);
foreach($temp_out as $out)
{
	if($out[1])
		echo $out[0],"</div>
</td><td class=\"ir\"></td>
</tr>
<tr>
<td class=\"ibl\"></td><td class=\"ib\"></td><td class=\"ibr\"></td>
</tr>
</table>
</div>";
}
?>
</div>
</div>
</div>
</div>
</div>