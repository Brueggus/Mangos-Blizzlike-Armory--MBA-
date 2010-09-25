<?php
if(!defined("Armory") || !CLIENT)
{
	header("Location: ../error.php");
	exit();
}
function category_frame($category)
{
	return "<h2>".$category."</h2><table style=\"width: 100%;\">
<tr><th colspan=\"5\" align=\"left\" style=\"font-size:14px; color:black; background-color: #f6b620;\">".$category."</th></tr>";
}
function subcategory_frame($subcategory)
{
	return "<table style=\"width: 100%;\">
<tr><th colspan=\"5\" align=\"left\" style=\"font-size:11px; color:white; background-color: #222b3a;\">".$subcategory."</th></tr>";
}
switchConnection("armory", REALM_NAME);
$getcategory = execute_query("SELECT `id`, `name` FROM `dbc_achievement_category` WHERE `ref_achievement_category` = -1");
while($category = mysql_fetch_assoc($getcategory))
{
	$temp_out[$category["id"]] = array(category_frame($category["name"]), 0);
	switchConnection("armory", REALM_NAME);
	$getsubcategory = execute_query("SELECT `id`, `name` FROM `dbc_achievement_category` WHERE `ref_achievement_category` = ".$category["id"]);
	while($subcategory = mysql_fetch_assoc($getsubcategory))
		$temp_out[$subcategory["id"]] = array(subcategory_frame($subcategory["name"]), 0);
}
switchConnection("characters", REALM_NAME);
$getcharachi = execute_query("SELECT `achievement`, `date` FROM `character_achievement` WHERE `guid` = ".$stat["guid"]);
while($char_achi = mysql_fetch_assoc($getcharachi))
{
	switchConnection("armory", REALM_NAME);
	$achi = mysql_fetch_assoc(execute_query("SELECT * FROM `dbc_achievement` WHERE `id` = ".$char_achi["achievement"]." LIMIT 1"));
	$achi["icon"] = GetIcon("spell", $achi["ref_spellicon"]);
	$temp_out[$achi["ref_achievement_category"]][0] .="<tr>
<td width=\"10%\" height=\"58\" align=\"center\" style=\"border-bottom:1px solid #222b3a;\"><div style=\"background:url('".$achi["icon"]."') center no-repeat;\"><img src=\"images/achievements/fst_iconframe.png\" border=\"0\" /></div></td>
<td align=\"left\" style=\"border-bottom:1px solid #222b3a;\">".$achi["name"]."<br /><span style=\"font-size:9px\">".$achi["description"]."</span></td>
<td width=\"10%\" align=\"center\" style=\"border-bottom:1px solid #222b3a;\">".date("d/m/Y", $char_achi["date"])."</td>
<td width=\"10%\" align=\"center\" style=\"background:url('images/achievements/point_shield.png') center no-repeat; border-bottom:1px solid #222b3a\">".$achi["points"]."</td>
</tr>";
	$temp_out[$achi["ref_achievement_category"]][1] = 1;
}
?>
<br />
<br />
<?php
foreach($temp_out as $cat_data)
{
	if($cat_data[1])
		echo $cat_data[0],"</table>";
}
?>