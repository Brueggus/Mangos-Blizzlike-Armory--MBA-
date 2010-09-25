<?php
function GetCharacterPortrait($CharacterLevel, $CharacterGender, $CharacterRace, $CharacterClass)
{
	if($CharacterLevel <= 59)
		return "wow-default/".$CharacterGender."-".$CharacterRace."-".$CharacterClass.".gif";
	else if($CharacterLevel >= 60 && $CharacterLevel <= 69)
		return "wow/".$CharacterGender."-".$CharacterRace."-".$CharacterClass.".gif";
	else if($CharacterLevel >= 70 && $CharacterLevel <= 79)
		return "wow-70/".$CharacterGender."-".$CharacterRace."-".$CharacterClass.".gif";
	else if($CharacterLevel >= 80)
		return "wow-80/".$CharacterGender."-".$CharacterRace."-".$CharacterClass.".gif";
}
function GetFaction($CharacterRace)
{
	if($CharacterRace == 1 || $CharacterRace == 3 || $CharacterRace == 4 || $CharacterRace == 7 || $CharacterRace == 11)
		return "alliance";
	else
		return "horde";
}
function GetNameFromDB($Id, $Table)
{
	switchConnection("armory", REALM_NAME);
	$Query = execute_query("SELECT `name` FROM `".$Table."` WHERE `id`=".$Id." LIMIT 1");
	if($row = mysql_fetch_assoc($Query))
		return $row["name"];
	else
		return "";
}
function GetIcon($Type, $DisplayIconId)
{
	if($Type == "item")
		$Table = "dbc_itemdisplayinfo";
	else //$Type == "spell"
		$Table = "dbc_spellicon";
	if($icon = GetNameFromDB($DisplayIconId, $Table))
		return "images/icons/64x64/".$icon.".png";
	else
		return "images/icons/64x64/404.png";
}
function GetItemSource($item_id, $pvpreward = 0)
{
	global $lang;
	switchConnection("mangos", REALM_NAME);
	if(mysql_num_rows(execute_query("SELECT SQL_NO_CACHE `entry` FROM `quest_template` WHERE `SrcItemId` = ".$item_id." LIMIT 1")))
		return $lang["quest_item"];
	else if(mysql_num_rows(execute_query("SELECT SQL_NO_CACHE `entry` FROM `npc_vendor` WHERE `item` = ".$item_id." LIMIT 1")))
		return $pvpreward ? $lang["pvp_reward"] : $lang["vendor"];
	else if(mysql_num_rows(execute_query("SELECT SQL_NO_CACHE `entry` FROM `gameobject_loot_template` WHERE `item` = ".$item_id." LIMIT 1")))
		return $lang["chest_drop"];
	else if(mysql_num_rows(execute_query("SELECT SQL_NO_CACHE `entry` FROM `creature_loot_template` WHERE `item` = ".$item_id." LIMIT 1")))
		return $lang["drop"];
	else if(mysql_num_rows(execute_query("SELECT SQL_NO_CACHE `entry` FROM `quest_template` WHERE `RewChoiceItemId1` = ".$item_id." OR `RewChoiceItemId2` = ".$item_id."
	OR `RewChoiceItemId3` = ".$item_id." OR `RewChoiceItemId4` = ".$item_id." OR `RewChoiceItemId5` = ".$item_id." OR `RewChoiceItemId6` = ".$item_id."
	OR `RewItemId1` = ".$item_id." OR `RewItemId2` = ".$item_id." OR `RewItemId3` = ".$item_id." OR `RewItemId4` = ".$item_id." LIMIT 1")))
		return $lang["quest_reward"];
	else
		return $lang["created"];
}
function cache_item($itemid)
{
	global $config, $realms;
	//Get item data
	switchConnection("mangos", REALM_NAME);
	if($config["locales"])
	{
		$nameloc = "name_loc".$config["locales"];
		$itemData = mysql_fetch_assoc(execute_query("SELECT `name`, `".$nameloc."`, `Quality`, `displayid` FROM `item_template` LEFT JOIN `locales_item` ON `item_template`.`entry` = `locales_item`.`entry` WHERE `item_template`.`entry` = ".$itemid." LIMIT 1", "Error in item cache process for item ".$itemid));
		if($itemData[$nameloc])
			$itemData["name"] = $itemData[$nameloc];
	}
	else
		$itemData = mysql_fetch_assoc(execute_query("SELECT `name`, `Quality`, `displayid` FROM `item_template` WHERE `entry` = ".$itemid." LIMIT 1", "Error in item cache process for item ".$itemid));
	$db_fields = array(
	"item_id" => $itemid,
	"mangosdbkey" => $realms[REALM_NAME][2],
	"item_name" => $itemData["name"],
	"item_quality" => $itemData["Quality"],
	"item_icon" => GetIcon("item",$itemData["displayid"]),
	);
	return InsertCache($db_fields , "cache_item");
}
function cache_item_tooltip($itemid)
{
	global $realms;
	require_once "tooltipmgr.php";
	$item_tooltip = outputTooltip($itemid);
	$db_fields = array(
	"item_id" => $itemid,
	"mangosdbkey" => $realms[REALM_NAME][2],
	"item_html" => $item_tooltip[0],
	"item_info_html" => $item_tooltip[1],
	);
	return InsertCache($db_fields , "cache_item_tooltip");
}
function cache_item_char($itemid, $owner, $slot, $itemguid, $itemlist)
{
	global $realms;
	require_once "tooltipmgr.php";
	$db_fields = array(
	"item_guid" => $itemguid,
	"chardbkey" => $realms[REALM_NAME][1],
	"item_owner" => $owner,
	"item_slot" => $slot,
	"item_html" => outputTooltip($itemid, $itemguid, $itemlist),
	);
	return InsertCache($db_fields , "cache_item_char");
}
function cache_item_search($itemid)
{
	global $config, $realms;
	switchConnection("mangos", REALM_NAME);
	if($config["locales"])
	{
		$nameloc="name_loc".$config["locales"];
		$itemData = mysql_fetch_assoc(execute_query("SELECT `name`, `".$nameloc."`, `ItemLevel`, `Quality`, `ItemLevel`, `Flags` FROM `item_template` LEFT JOIN `locales_item` ON `item_template`.`entry` = `locales_item`.`entry` WHERE `item_template`.`entry` = ".$itemid." LIMIT 1", "Error in item cache process for item ".$itemid));
		if($itemData[$nameloc])
			$itemData["name"] = $itemData[$nameloc];
	}
	else
		$itemData = mysql_fetch_assoc(execute_query("SELECT `name`, `ItemLevel`, `Quality`, `ItemLevel`, `Flags` FROM `item_template` WHERE `entry` = ".$itemid." LIMIT 1", "Error in item cache process for item ".$itemid));
	if(($itemData["Flags"] & 32768) == 32768)
		$pvpreward = 1;
	else
		$pvpreward = 0;
	$db_fields = array(
	"item_id" => $itemid,
	"mangosdbkey" => $realms[REALM_NAME][2],
	"item_name" => $itemData["name"],
	"item_level" => $itemData["ItemLevel"],
	"item_source" => GetItemSource($itemid, $pvpreward),
	"item_relevance" => $itemData["Quality"]*25+$itemData["ItemLevel"],
	);
	return InsertCache($db_fields, "cache_item_search");
}
function InsertCache($db_fields, $db)
{
	// Insert
	switchConnection("armory", REALM_NAME);
	$querystring = "INSERT INTO `".$db."` (";
	foreach($db_fields as $field => $value)
		$querystring .= "`".$field."`,";
	// Chop the end of $querystring off
	$querystring = substr($querystring, 0, -1);
	$querystring .= ") VALUES (";
	foreach($db_fields as $field => $value)
		$querystring .= "'".str_replace("'", "\'", $value)."',";
	// Chop the end off again
	$querystring = substr($querystring, 0, -1);
	$querystring .= ")";
	execute_query($querystring, "Could not cache: ");
	return $db_fields; //return an associative array
}
// validate input - preventing SQL injection
function validate_string($string)
{
	$string = trim($string);
	// strips excess whitespace
	$string = preg_replace('/\s\s+/', " ", $string);
	if(preg_match('/[^[:alnum:]\sА-ПР-Яа-пр-я]/', $string))
		$string = "";
	return $string;
}
function exclude_GMs()
{
	global $config;
	$excludeGMs = "";
	if($config["ExcludeGMs"])
		$excludeGMs = " AND (`extra_flags` & 1) = 0";
	if(isset($_SESSION["GM"]))
		$excludeGMs = "";
	return $excludeGMs;
}
function microtime_float()
{
	list($utime, $time) = explode(" ", microtime());
	return ((float)$utime + (float)$time);
}
function ordinal_suffix($number)
{
	global $lang;
	if(!$number)
		return $lang["none"];
	switch($number % 10)
	{
		case 1: return $number.$lang["st"];
		case 2: return $number.$lang["nd"];
		case 3: return $number.$lang["rd"];
		default: return $number.$lang["th"];
	}
}
function startcontenttable($class="full-list")
{
	echo "<div class=\"list\">
	<div class=\"",$class,"\">
	<div class=\"tip\" style=\"clear: left;\">
	<table width=\"100\">
	<tr>
	<td class=\"tip-top-left\"></td><td class=\"tip-top\"></td><td class=\"tip-top-right\"></td>
	</tr>
	<tr>
	<td class=\"tip-left\"></td><td class=\"tip-bg\">";
}
function endcontenttable()
{
	echo "</td>
	<td class=\"tip-right\"></td>
	</tr>
	<tr>
	<td class=\"tip-bot-left\"></td><td class=\"tip-bot\"></td><td class=\"tip-bot-right\"></td>
	</tr>
	</table>
	</div>
	</div>
	</div>";
}
function showerror($errTitle, $errMessage)
{
	startcontenttable("team-side");
	echo "Error - ",$errTitle,"<br />",$errMessage;
	endcontenttable();
}
function guild_arenateam_tooltip_frame($type, $id, $name, $leader_name, $num_members)
{
	global $lang;
	if($type == "guild")
	{
		$type_name = $lang["guild"];
		$id_field = "guildid";
		$leader = $lang["leader"];
	}
	else//if($type == "team")
	{
		$type_name = $lang["arena_team"];
		$id_field = "arenateamid";
		$leader = $lang["captain"];
	}
	$guild_arenateam_tooltip = "<a href=\"index.php?searchType=".$type."info&".$id_field."=".$id."&realm=".REALM_NAME."\" onmouseover=\"showTip('";
	$guild_arenateam_tooltip .= "<span class=\'profile-tooltip-header\'>".$type_name." - ".$name."</span><br />";
	$guild_arenateam_tooltip .= "<span class=\'profile-tooltip-description\'>".$leader.": ".$leader_name."<br />".$lang["members"].": ".$num_members."</span>";
	$guild_arenateam_tooltip .= "')\" onmouseout=\"hideTip()\">".$name."</a>";
	return $guild_arenateam_tooltip;
}
function guild_tooltip($guildid)
{
	global $lang, $guildInfoTooltip;
	if(!$guildid)
		return $lang["none"];
	if(!isset($guildInfoTooltip[$guildid]))
	{
		switchConnection("characters", REALM_NAME);
		$glinkresults = mysql_fetch_assoc(execute_query("SELECT `name`, `leaderguid` FROM `guild` WHERE `guildid` = ".$guildid." LIMIT 1"));
		if($guildLeader = mysql_fetch_assoc(execute_query("SELECT `name` FROM `characters` WHERE `guid` = ".$glinkresults["leaderguid"]." LIMIT 1")))
			$guildLeaderName = $guildLeader["name"];
		else
			$guildLeaderName = $lang["unknown"];
		$guildMembers = mysql_result(execute_query("SELECT COUNT(*) FROM `guild_member` WHERE `guildid` = ".$guildid), 0);
		$guildInfoTooltip[$guildid] = guild_arenateam_tooltip_frame("guild", $guildid, $glinkresults["name"], $guildLeaderName, $guildMembers);
	}
	return $guildInfoTooltip[$guildid];
}
function initialize_realm()
{
	global $realms;
	if(isset($_SESSION["realm"]))
		define("REALM_NAME", $_SESSION["realm"]);
	else if(isset($_GET["realm"]) && isset($realms[$realm_name = stripslashes($_GET["realm"])]))
		define("REALM_NAME", $realm_name);
	else
		define("REALM_NAME", DefaultRealmName);
	switchConnection("armory", REALM_NAME);
	define("CLIENT", mysql_result(execute_query("SELECT `value` FROM `conf_client` LIMIT 1"), 0));
	define("LANGUAGE", mysql_result(execute_query("SELECT `value` FROM `conf_lang` LIMIT 1"), 0));
}
function emblem_color_convert($color)
{
	return str_pad(base_convert($color, 10, 16), 8, 0, STR_PAD_LEFT);
}
?>