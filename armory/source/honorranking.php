<?php
if(!defined("Armory"))
{
	header("Location: ../error.php");
	exit();
}
if(isset($_GET["sortBy"]))
{
	$orderField = $_GET["sortBy"];
	if($orderField <> "honor" && $orderField <> "kills")
		$orderField = "kills";
}
else
	$orderField = "kills";
?>
<script src="js/arena-ladder-ajax.js" type="text/javascript"></script>
<?php
startcontenttable();
?>
<div class="profile-wrapper">
<blockquote>
<b class="iarenateams">
<h4>
<a href="index.php?searchType=honor">Honor Rankings</a>
</h4>
<h3><?php echo "Honor Top ",$config["PvPTop"],": ",REALM_NAME ?></h3>
</b>
</blockquote>
<div class="generic-wrapper">
<div class="generic-right">
<div class="genericHeader ath">
<div class="arena-list">
<em class="d-rlm">
<h3>
<img src="images/icons/icon-realm.gif"><span><?php echo $lang["realms"] ?>:</span>
</h3>
<select id="filter" onchange="javascript: { if (this.value) arenaLadderPageInstance.followLink(this.value); }">
<option selected value="#"></option>
<?php
foreach($realms as $key => $data)
	echo "<option value=\"index.php?searchType=honor&realm=",$key,"&sortBy=",$orderField,"\">",$key,"</option>";
?>
</select></em><em class="d-srt">
<h3>
<img src="images/icons/icon-sort.gif"><span>Sort:</span>
</h3>
<select id="sort" onchange="javascript: { if (this.value) arenaLadderPageInstance.followLink(this.value); }">
<option selected value="#"></option>
<option value="index.php?searchType=honor&realm=<?php echo REALM_NAME ?>&sortBy=kills"><?php echo $lang["kills"] ?></option>
<option value="index.php?searchType=honor&realm=<?php echo REALM_NAME ?>&sortBy=honor"><?php echo $lang["honor"] ?></option>
</select></em>
</div>
</div>
</div>
</div>
<div class="data" style="clear: both;">
<table class="data-table">
<tr class="masthead">
<td>
<div>
<p></p>
</div>
</td><td width="5%"><a class="noLink"><?php echo $lang["pos"] ?></a></td>
<td width="25%"><a class="noLink"><?php echo $lang["char_name"] ?></a></td>
<td width="9%" align="center"><a class="noLink"><?php echo $lang["level"] ?></a></td>
<td width="6%" align="right"><a class="noLink"><?php echo $lang["race"] ?></a></td>
<td width="6%" align="left"><a class="noLink"><?php echo $lang["class"] ?></a></td>
<td width="9%" align="center"><a class="noLink"><?php echo $lang["faction"] ?></a></td>
<td width="20%"><a class="noLink"><?php echo $lang["guild"] ?></a></td>
<td width="10%" align="center"><a href="index.php?searchType=honor&realm=<?php echo REALM_NAME ?>&sortBy=kills"><?php echo $lang["kills"] ?></a></td>
<td width="10%" align="center"><a href="index.php?searchType=honor&realm=<?php echo REALM_NAME ?>&sortBy=honor"><?php echo $lang["honor"] ?></a></td><td align="right">
<div>
<b></b>
</div>
</td>
</tr>
<?php
// Query //
if($orderField == "kills")
	$tablefield = $defines["KILLS"][CLIENT];
else//if($orderField == "honor")
	$tablefield = $defines["HONOR"][CLIENT];
switchConnection("characters", REALM_NAME);
$pvpquery = execute_query("SELECT `guid`, `data`, `name`, `race`, `class` FROM `characters`
WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`data`, ' ', ".($tablefield+1)."), ' ', -1) AS UNSIGNED) > 0".exclude_GMs().
" ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`data`, ' ', ".($tablefield+1)."), ' ', -1) AS UNSIGNED) DESC LIMIT ".$config['PvPTop']);
$counter = 0;
while($char = mysql_fetch_assoc($pvpquery))
{
	$counter++;
	$char_data = explode(" ",$char["data"]);
	$char["kills"] = $char_data[$defines["KILLS"][CLIENT]];
	$char["honor"] = $char_data[$defines["HONOR"][CLIENT]];
	$char["level"] = $char_data[$defines["LEVEL"][CLIENT]];
	$char_gender = dechex($char_data[$defines["GENDER"][CLIENT]]);
	unset($char_data);
	$char_gender = str_pad($char_gender,8, 0, STR_PAD_LEFT);
	$char["gender"] = $char_gender{3};
	switchConnection("characters", REALM_NAME);
	$gquery = mysql_fetch_assoc(execute_query("SELECT `guildid` FROM `guild_member` WHERE `guid` = ".$char["guid"]." LIMIT 1"));
	$char["guildid"] = $gquery ? $gquery["guildid"] : 0;
	$char["faction"] = GetFaction($char["race"]);
?>
<tr>
<td>
<div>
<p></p>
</div>
</td><td><q><i><span class="veryplain"><?php echo $counter ?></span></i></q></td>
<td><q><a href="index.php?searchType=profile&character=<?php echo $char["name"]."&realm=".REALM_NAME ?>" onmouseover="showTip('<?php echo $lang["char_link"] ?>')" onmouseout="hideTip()"><?php echo $char["name"] ?></a></q></td>
<td align="center"><q><i><span class="veryplain"><?php echo $char["level"] ?></span></i></q></td>
<td align="right"><q><img src="images/icons/race/<?php echo $char["race"],"-",$char["gender"] ?>.gif" onmouseover="showTip('<?php echo GetNameFromDB($char["race"], "dbc_chrraces") ?>')" onmouseout="hideTip()"></q></td>
<td align="left"><q><img src="images/icons/class/<?php echo $char["class"] ?>.gif" onmouseover="showTip('<?php echo GetNameFromDB($char["class"], "dbc_chrclasses") ?>')" onmouseout="hideTip()"></q></td>
<td align="center"><q><img width="20" height="20" src="images/icon-<?php echo $char["faction"] ?>.gif" onMouseOver="showTip('<?php echo $lang[$char["faction"]] ?>')" onmouseout="hideTip()"></q></td>
<td><q><?php echo guild_tooltip($char["guildid"]) ?></q></td>
<td align="center"><q><i><span class="veryplain"><?php echo $char["kills"] ?></span></i></q></td>
<td align="center"><q><i><span class="veryplain"><?php echo $char["honor"] ?></span></i></q></td><td align="right">
<div>
<b></b>
</div>
</td>
</tr>
<?php
}
?>
</table></div>
<?php
endcontenttable();
?>