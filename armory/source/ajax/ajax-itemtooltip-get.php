<?php
if(isset($_GET["item"]) && isset($_GET["dbkey"]) && isset($_GET["owned"]))
{
	require "../../configuration/mysql.php";
	$itemidorguid = (int) $_GET["item"];
	$dbkey = (int) $_GET["dbkey"];
	$owned = (int) $_GET["owned"];
	$realm = DefaultRealmName;
	foreach($realms as $key => $value)
		if($dbkey == $value[$owned])
			$realm = $key;
	switchConnection("armory", $realm);
	if($owned == 2)
		$query = execute_query("SELECT `item_html` FROM `cache_item_tooltip` WHERE `item_id` = ".$itemidorguid."  AND `mangosdbkey` = ".$dbkey." LIMIT 1");
	else // if($owned == 1)
		$query = execute_query("SELECT `item_html` FROM `cache_item_char` WHERE `item_guid` = ".$itemidorguid." AND `chardbkey` = ".$dbkey." LIMIT 1");
	if($row = mysql_fetch_assoc($query))
		echo $row["item_html"];
	else
		echo "<span class=\"profile-tooltip-description\">Error: Not Found</span>";
}
else
	echo "<span class=\"profile-tooltip-description\">Error: Get lost</span>";
?>