<?php
if(!defined("Armory"))
{
	header("Location: ../error.php");
	exit();
}
?>
<div class="parch-profile-banner" id="banner" style="margin-top: -7px!important;">
<h1><?php echo $lang["profile"] ?></h1>
</div>
<?php
//Professions and Defense Skill
$char_primary_prof = array();
$defense_skill = 0;
$all_primary_prof = array();
switchConnection("armory", REALM_NAME);
$get_all_primary_prof = execute_query("SELECT `id`, `name` FROM `dbc_skillline` WHERE `ref_skilllinecategory` = 11");
while($profession = mysql_fetch_assoc($get_all_primary_prof))
	$all_primary_prof[$profession["id"]] = $profession;
$statistic_data = explode(" ",$data["data"]);
for($i = $defines["SKILL_DATA"][CLIENT]; $i <= $defines["SKILL_DATA"][CLIENT]+384 ; $i += 3)
{
	$skill_id = ($statistic_data[$i] & 0x0000FFFF);
	if(isset($all_primary_prof[$skill_id]) || $skill_id == 95)
	{
		$skill_points = unpack("S", pack("L", $statistic_data[$i+1]));
		if($skill_id == 95)
			$defense_skill = $skill_points[1];
		else
			$char_primary_prof[] = array($skill_id, $all_primary_prof[$skill_id]["name"], $skill_points[1]);
	}
}
unset($statistic_data);
$char_primary_prof = array_slice($char_primary_prof, 0, 2);
$num_primary_prof = count($char_primary_prof);
//Items
$myinventory = array();
switchConnection("characters", REALM_NAME);
$invq = execute_query("SELECT `item_template`, `slot`, `item` FROM `character_inventory` WHERE  `guid` = ".$stat["guid"]." AND `bag` = 0  AND `slot` <= 18  ORDER BY `slot` LIMIT 18");
$numresults = mysql_num_rows($invq);
switchConnection("armory", REALM_NAME);
$itemlist = array();
if($numresults)
{
	$query_pls_gm = "SELECT * FROM `cache_item` WHERE `item_id` IN (";
	while($invd = mysql_fetch_assoc($invq))
	{
		$myinventory[$invd["slot"]] = $invd;
		$myinventory[$invd["slot"]]["icon"]="";
		$query_pls_gm .= $invd["item_template"].",";
		$itemlist[] = $invd["item_template"];
	}
	$query_pls_gm .= "0) AND `mangosdbkey` = ".$realms[REALM_NAME][2]." LIMIT 18";
	$item_cache = array();
	$item_spellstats_cache = array();
	$doquery_pls_gm = execute_query($query_pls_gm);
	while($result_pls_gm = mysql_fetch_assoc($doquery_pls_gm))
		$item_cache[$result_pls_gm["item_id"]] = $result_pls_gm;
}
execute_query("DELETE FROM `cache_item_char` WHERE `chardbkey` = ".$realms[REALM_NAME][1]." AND `item_owner` = ".$stat["guid"]." LIMIT 18");
$setArray = array();
for($i = 0; $i <= 18; $i ++)
{
	/* Get my id */
	if(isset($myinventory[$i]))
	{
		$thisId = $myinventory[$i]["item_template"];
		if(!isset($item_cache[$thisId]))
			$item_cache[$thisId] = cache_item($thisId);
		$char_item = cache_item_char($thisId, $stat["guid"], $i, $myinventory[$i]["item"], $itemlist);
		// Item Icon //
		$myinventory[$i]["icon"] = $item_cache[$thisId]["item_icon"];
	}
}
// WEAPON //
$MainMin = floor($stat["meele_main_hand_min_dmg"][1]);
$MainMax = floor($stat["meele_main_hand_max_dmg"][1]);
$MainAttSpe = number_format($stat["meele_main_hand_attack_time"][1]/1000, 2, ".", "");
if($MainAttSpe)
	$MainDPS = number_format((($MainMin+$MainMax)/2)/$MainAttSpe, 1, ".", "");
else
	$MainDPS = 0;
$OffMin = floor($stat["meele_off_hand_min_dmg"][1]);
$OffMax = floor($stat["meele_off_hand_max_dmg"][1]);
$OffAttSpe = number_format($stat["meele_off_hand_attack_time"][1]/1000, 2, ".", "");
if($OffAttSpe)
	$OffDPS = number_format((($OffMin+$OffMax)/2)/$OffAttSpe, 1, ".", "");
else
	$OffDPS = 0;
$RangedMin = floor($stat["ranged_min_dmg"][1]);
$RangedMax = floor($stat["ranged_max_dmg"][1]);
$RangedAttSpe = number_format($stat["ranged_attack_time"][1]/1000, 2, ".", "");
if($RangedAttSpe)
	$RangedDPS = number_format((($RangedMin+$RangedMax)/2)/$RangedAttSpe, 1, ".", "");
else
	$RangedDPS = 0;
// END WEAPON //
// Strength
switch($stat["class"])
{
	case 1: case 2: case 6: case 7: case 11: $attackpowerstr = $stat["strength_eff"]*2-20; break;
	case 3: case 4: case 5: case 8: case 9: $attackpowerstr = $stat["strength_eff"]-10; break;
}
if($stat["class"] == 1 || $stat["class"] == 2 || $stat["class"] == 7)
	$block_from_strength = floor($stat["strength_eff"]/20);
else
	$block_from_strength = -1;
//Agility
if($stat["class"] == 3 || $stat["class"] == 4)
	$attackpoweragi = $stat["agility_eff"]-10;
else
	$attackpoweragi = -1;
$critConstant = 0;
switch($stat["class"])
{
	case 4: case 3: $critConstant = 40; break;
	case 11: case 8: case 2: case 5: case 7: $critConstant = 25; break;
	case 9: $critConstant = 24.69; break;
	case 1: case 6: $critConstant = 33; break;
}
$critchanceagi = round($stat["agility_eff"]/$critConstant, 2);
$armoragi = $stat["agility_eff"]*2;
//Stamina
$increasehealthsta = $stat["stamina_eff"]*10-180;
//Intelect
if($stat["class"] != 1 && $stat["class"] != 4 && $stat["class"] != 6)
{
	$increasemanaint = 20+($stat["intellect_eff"]-20)*15;
	$spellcritConstant = 0;
	if($stat["level"] <= 60)
	{
		switch($stat["class"])
		{
			case 2: $spellcritConstant = 54; break;
			case 5: $spellcritConstant = 59.2; break;
			case 9: $spellcritConstant = 60.6; break;
			case 11: $spellcritConstant = 60; break;
			default: $spellcritConstant = 59.5;
		}
		$spellcritConstant = $spellcritConstant*($stat["level"]/60);
	}
	else if($stat["level"] <= 70)
	{
		switch($stat["class"])
		{
			case 2: $spellcritConstant = 80.05; break;
			case 9: $spellcritConstant = 81.92; break;
			default: $spellcritConstant = 80;
		}
		$spellcritConstant = $spellcritConstant*($stat["level"]/70);
	}
	else
	{
		$spellcritConstant = 166.6667;
		$spellcritConstant = $spellcritConstant*($stat["level"]/80);
	}
	$critchanceint = $stat["intellect_eff"]/$spellcritConstant;
	switch($stat["class"])
	{
		case 2: $critchanceint += 3.336; break;
		case 3: $critchanceint += 3.6; break;
		case 5: $critchanceint += 1.24; break;
		case 7: $critchanceint += 2.2; break;
		case 8: $critchanceint += 0.91; break;
		case 9: $critchanceint += 1.701; break;
		case 11: $critchanceint += 1.85; break;
	}
	$critchanceint = round($critchanceint, 2);
}
else
{
	$increasemanaint = -1;
	$critchanceint = -1;
}
//Spirit
$HPRegenFromSpirit = HPRegenFromSpirit($stat["level"], $stat["class"], $stat["spirit_eff"], $stat["spirit_base"]);
if($stat["class"] != 1 && $stat["class"] != 4 && $stat["class"] != 6)
	$MPRegenFromSpirit = MPRegenFromSpirit($stat["level"], $stat["class"], $stat["spirit_eff"], $stat["intellect_eff"]);
else
	$MPRegenFromSpirit = -1;
//Armor
$Reducedphysicaldamage = stathandler_getdamagereduction($stat["level"], $stat["armor_eff"]);
if($stat["class"] == 3 || $stat["class"] == 9)
	$PetArmorBonus = floor($stat["armor_eff"]/2.857);
else
	$PetArmorBonus = -1;
//Resistances 3.0.9
if(CLIENT)
{
	switch($stat["race"])
	{
		case 4: case 6: $stat["nature_res"] += 10; break;
		case 3: $stat["frost_res"] += 10; break;
		case 5: case 11: $stat["shadow_res"] += 10; break;
		case 7: $stat["arcane_res"] += 10; break;
		case 10: $stat["fire_res"] += 5;
			$stat["nature_res"] += 5;
			$stat["frost_res"] += 5;
			$stat["shadow_res"] += 5;
			$stat["arcane_res"] += 5; break;
	}
}
//Melee Attack Power
$increasedmgmeleatkpow = round($stat["melee_ap"]/14, 1);
//Ranged Attack Power
$increasedmgrngatpow = round($stat["ranged_ap"]/14, 1);
if($stat["class"] == 3) // what about the warlocks pet?
{
	$increasepetatpower = ceil($stat["ranged_ap"]*0.222);
	$increasepetspedmg = ceil($stat["ranged_ap"]*0.1287);
}
else
{
	$increasepetatpower = -1;
	$increasepetspedmg = -1;
}
$RatingBases = array(
"resilience" => 25,
"expertise" => 2.5,
"crit" => 14,
"hit" => 10,
"haste" => 10,
"spell_crit" => 14,
"spell_hit" => 8,
"spell_haste" => 10,
"dodge" => 12,
"block" => 5,
"parry" => 15,
"defense" => 1.5
);
//Crit increase percent from rating
$crit_increase_percent = stathandler_getratingformula("crit", $stat["melee_crit_rating"], $stat["level"]);
//Hit increase percent from rating
$hit_increase_percent = stathandler_getratingformula("hit", $stat["melee_hit_rating"], $stat["level"]);
//Ranged Crit increase percent from rating
$rangedcrit_increase_percent = stathandler_getratingformula("crit", $stat["ranged_crit_rating"], $stat["level"]);
//Ranged Hit increase percent from rating
$rangedhit_increase_percent = stathandler_getratingformula("hit", $stat["ranged_hit_rating"], $stat["level"]);
//Spell Hit increase percent from rating
$spellhit_increase_percent = stathandler_getratingformula("spell_hit", $stat["spell_hit_rating"], $stat["level"]);
//Dodge increase percent from rating
$dodge_increase_percent = stathandler_getratingformula("dodge", $stat["dodge_rating"], $stat["level"]);
//Block increase percent from rating
$block_increase_percent = stathandler_getratingformula("block", $stat["block_rating"], $stat["level"]);
//Parry increase percent from rating
$parry_increase_percent = stathandler_getratingformula("parry", $stat["parry_rating"], $stat["level"]);
//Melee haste increase percent from rating
$melee_haste_increase_percent = stathandler_getratingformula("haste", $stat["melee_haste_rating"], $stat["level"]);
//Ranged haste increase percent from rating
$ranged_haste_increase_percent = stathandler_getratingformula("haste", $stat["ranged_haste_rating"], $stat["level"]);
//Spell haste increase percent from rating
$spell_haste_increase_percent = stathandler_getratingformula("spell_haste", $stat["spell_haste_rating"], $stat["level"]);
//Crit hit chance reduction from resilience
$ResiliencehitPercent = stathandler_getratingformula("resilience", $stat["resilience_rating"], $stat["level"]);
//Crit damage reduction from resilience
$ResiliencedamagePercent = stathandler_getratingformula("resilience", $stat["resilience_rating"]*2, $stat["level"]);
//Defense increase from rating
$defense_increase = stathandler_getratingformula("defense", $stat["defense_rating"], $stat["level"], 0);
//Expertise from expertise rating
$Expertiseadditional = stathandler_getratingformula("expertise", $stat["expertise_rating"], $stat["level"], 0);
//Defense Increase percent
$defense_increase_percent = round($defense_increase*0.04, 2);
//Block Percent
$stat["block_percent"] = round($stat["block_percent"][1], 2);
//Dodge Percent
$stat["dodge_percent"] = round($stat["dodge_percent"][1], 2);
//Parry Percent
$stat["parry_percent"] = round($stat["parry_percent"][1], 2);
//Crit Percent
$stat["crit_percent"] = round($stat["crit_percent"][1], 2);
//Range Crit Percent
$stat["ranged_crit_percent"] = round($stat["ranged_crit_percent"][1], 2);
//Spell Crit Percents
for($i = 1; $i < 7; $i ++)
	$stat["spell_crit_percent_".$i] = round($stat["spell_crit_percent_".$i][1], 2);
//Chance from expertise
$Expertiseprecent = round($stat["expertise"]*0.25, 2);
//Temporal Hack for showing offhand stats
$show_offhand = 0;
if(isset($myinventory[16]))
{
	switchConnection("mangos", REALM_NAME);
	if(mysql_num_rows(execute_query("SELECT `entry` FROM `item_template` WHERE `entry` = ".$myinventory[16]["item_template"]." AND `class` = 2 LIMIT 1")))
		$show_offhand = 1;
}
?>
<script src="js/character/functions.js" type="text/javascript"></script><script type="text/javascript">

function strengthObject() {
	this.base = "<?php echo $stat["strength_base"] ?>";
	this.effective = "<?php echo $stat["strength_eff"] ?>";
	this.block = "<?php echo $block_from_strength ?>";
	this.attack = "<?php echo $attackpowerstr ?>";

	this.diff = this.effective - this.base;
}

function agilityObject() {
	this.base = "<?php echo $stat["agility_base"] ?>";
	this.effective = "<?php echo $stat["agility_eff"] ?>";
	this.critHitPercent = "<?php echo $critchanceagi ?>";
	this.attack = "<?php echo $attackpoweragi ?>";
	this.armor = "<?php echo $armoragi ?>";

	this.diff = this.effective - this.base;
}

function staminaObject(base, effective, health, petBonus) {
	this.base = "<?php echo $stat["stamina_base"] ?>";
	this.effective = "<?php echo $stat["stamina_eff"] ?>";
	this.health = "<?php echo $increasehealthsta ?>";
	this.petBonus = "-1";

	this.diff = this.effective - this.base;
}

function intellectObject() {
	this.base = "<?php echo $stat["intellect_base"] ?>";
	this.effective = "<?php echo $stat["intellect_eff"] ?>";
	this.mana = "<?php echo $increasemanaint ?>";
	this.critHitPercent = "<?php echo $critchanceint ?>";
	this.petBonus = "-1";

	this.diff = this.effective - this.base;
}

function spiritObject() {
	this.base = "<?php echo $stat["spirit_base"] ?>";
	this.effective = "<?php echo $stat["spirit_eff"] ?>";
	this.healthRegen = "<?php echo $HPRegenFromSpirit ?>";
	this.manaRegen = "<?php echo $MPRegenFromSpirit ?>";

	this.diff = this.effective - this.base;
}

function armorObject() {
	this.base = "<?php echo $stat["armor_eff"] ?>";
	this.effective = "<?php echo $stat["armor_eff"] ?>";
	this.reductionPercent = "<?php echo $Reducedphysicaldamage ?>";
	this.petBonus = "<?php echo $PetArmorBonus ?>";

	this.diff = this.effective - this.base;
}

function resistancesObject() {
	this.arcane = new resistArcaneObject("<?php echo $stat["arcane_res"] ?>", "0");
	this.nature = new resistNatureObject("<?php echo $stat["nature_res"] ?>", "0");
	this.fire = new resistFireObject("<?php echo $stat["fire_res"] ?>", "0");
	this.frost = new resistFrostObject("<?php echo $stat["frost_res"] ?>", "0");
	this.shadow = new resistShadowObject("<?php echo $stat["shadow_res"] ?>", "0");
}

function meleeMainHandWeaponSkillObject() {
	this.value = "<?php echo $stat["expertise"] ?>";
	this.rating = "<?php echo $stat["expertise_rating"] ?>";
	this.additional = "<?php echo $Expertiseadditional ?>";
	this.percent = "<?php echo $Expertiseprecent ?>";
}

function meleeOffHandWeaponSkillObject() {
	this.value = "<?php echo $show_offhand ?>";
	this.rating = "0";
}


function meleeMainHandDamageObject() {
	this.speed = "<?php echo $MainAttSpe ?>";
	this.min = "<?php echo $MainMin ?>";
	this.max = "<?php echo $MainMax ?>";
	this.percent = "0";
	this.dps = "<?php echo $MainDPS ?>";

	if (this.percent > 0)
		this.effectiveColor = "class = 'mod'";
	else if (this.percent < 0)
		this.effectiveColor = "class = 'moddown'";

}

function meleeOffHandDamageObject() {
	this.speed = "<?php echo $OffAttSpe ?>";
	this.min = "<?php echo $OffMin ?>";
	this.max = "<?php echo $OffMax ?>";
	this.percent = "0";
	this.dps = "<?php echo $OffDPS ?>";
}


function meleeMainHandSpeedObject() {
	this.value = "<?php echo $MainAttSpe ?>";
	this.hasteRating = "<?php echo $stat["melee_haste_rating"] ?>";
	this.hastePercent = "<?php echo $melee_haste_increase_percent ?>";
}

function meleeOffHandSpeedObject() {
	this.value = "<?php echo $OffAttSpe ?>";
	this.hasteRating = "<?php echo $stat["melee_haste_rating"] ?>";
	this.hastePercent = "<?php echo $melee_haste_increase_percent ?>";
}

function meleePowerObject() {
	this.base = "<?php echo $stat["melee_ap_base"] ?>";
	this.effective = "<?php echo $stat["melee_ap"] ?>";
	this.increasedDps = "<?php echo $increasedmgmeleatkpow ?>";

	this.diff = this.effective - this.base;
}

function meleeHitRatingObject() {
	this.value = "<?php echo $stat["melee_hit_rating"] ?>";
	this.increasedHitPercent = "<?php echo $hit_increase_percent ?>";
}

function meleeCritChanceObject() {
	this.percent = "<?php echo $stat["crit_percent"] ?>";
	this.rating = "<?php echo $stat["melee_crit_rating"] ?>";
	this.plusPercent = "<?php echo $crit_increase_percent ?>";
}

function rangedWeaponSkillObject() {
	this.value = 0;
	this.rating = 0;
}

function rangedDamageObject() {
	this.speed = <?php echo $RangedAttSpe ?>;
	this.min = <?php echo $RangedMin ?>;
	this.max = <?php echo $RangedMax ?>;
	this.dps = <?php echo $RangedDPS ?>;
	this.percent = 0;

	if (this.percent > 0)
		this.effectiveColor = "class = 'mod'";
	else if (this.percent < 0)
		this.effectiveColor = "class = 'moddown'";

}

function rangedSpeedObject() {
	this.value = <?php echo $RangedAttSpe ?>;
	this.hasteRating = <?php echo $stat["ranged_haste_rating"] ?>;
	this.hastePercent = <?php echo $ranged_haste_increase_percent ?>;
}

function rangedPowerObject() {
	this.base = <?php echo $stat["ranged_ap_base"] ?>;
	this.effective = <?php echo $stat["ranged_ap"] ?>;
	this.increasedDps = <?php echo $increasedmgrngatpow ?>;
	this.petAttack = <?php echo $increasepetatpower ?>;
	this.petSpell = <?php echo $increasepetspedmg ?>;

	this.diff = this.effective - this.base;
}

function rangedHitRatingObject() {
	this.value = <?php echo $stat["ranged_hit_rating"] ?>;
	this.increasedHitPercent = <?php echo $rangedhit_increase_percent ?>;
}

function rangedCritChanceObject() {
	this.percent = <?php echo $stat["ranged_crit_percent"] ?>;
	this.rating = <?php echo $stat["ranged_crit_rating"] ?>;
	this.plusPercent = <?php echo $rangedcrit_increase_percent ?>;
}

function spellBonusDamageObject() {
	this.holy = <?php echo $stat["spell_damage_1"] ?>;
	this.arcane = <?php echo $stat["spell_damage_2"] ?>;
	this.fire = <?php echo $stat["spell_damage_3"] ?>;
	this.nature = <?php echo $stat["spell_damage_4"] ?>;
	this.frost = <?php echo $stat["spell_damage_5"] ?>;
	this.shadow = <?php echo $stat["spell_damage_6"] ?>;
	this.petBonusAttack = -1;
	this.petBonusDamage = -1;
	this.petBonusFromType = "";

	this.value = this.holy;
	if (this.value > this.arcane)
		this.value = this.arcane;
	if (this.value > this.fire)
		this.value = this.fire;
	if (this.value > this.nature)
		this.value = this.nature;
	if (this.value > this.frost)
		this.value = this.frost;
	if (this.value > this.shadow)
		this.value = this.shadow;
}

function spellBonusHealingObject() {
	this.value = <?php echo $stat["spell_healing"] ?>;
}

function spellHitRatingObject() {
	this.value = <?php echo $stat["spell_hit_rating"] ?>;
	this.increasedHitPercent = <?php echo $spellhit_increase_percent ?>;
}

function spellCritChanceObject() {
	this.rating = <?php echo $stat["spell_crit_rating"] ?>;
	this.holy = <?php echo $stat["spell_crit_percent_1"] ?>;
	this.fire = <?php echo $stat["spell_crit_percent_2"] ?>;
	this.nature = <?php echo $stat["spell_crit_percent_3"] ?>;
	this.frost = <?php echo $stat["spell_crit_percent_4"] ?>;
	this.shadow = <?php echo $stat["spell_crit_percent_5"] ?>;
	this.arcane = <?php echo $stat["spell_crit_percent_6"] ?>;

	this.percent = this.holy;
	if (this.percent > this.arcane)
		this.percent = this.arcane;
	if (this.percent > this.fire)
		this.percent = this.fire;
	if (this.percent > this.nature)
		this.percent = this.nature;
	if (this.percent > this.frost)
		this.percent = this.frost;
	if (this.percent > this.shadow)
		this.percent = this.shadow;

}

function spellHasteRatingObject() {
	this.value = <?php echo $stat["spell_haste_rating"] ?>;
	this.increasedHastePercent = <?php echo $spell_haste_increase_percent ?>;
}

function spellManaRegenObject() {
	this.casting = <?php echo round(5*$stat["mana_regen_interrupt"][1]) ?>;
	this.notCasting = <?php echo round(5*$stat["mana_regen"][1]) ?>;
}

function defensesArmorObject() {
	this.base = <?php echo $stat["armor_eff"] ?>;
	this.effective = <?php echo $stat["armor_eff"] ?>;
	this.percent = 0.00;
	this.petBonus = 0;

	this.diff = this.effective - this.base;
}

function defensesDefenseObject() {
	this.rating = <?php echo $stat["defense_rating"] ?>;
	this.plusDefense = <?php echo $defense_increase ?>;
	this.increasePercent = <?php echo $defense_increase_percent ?>;
	this.decreasePercent = <?php echo $defense_increase_percent ?>;
	this.value = <?php echo $defense_skill ?> + this.plusDefense;
}

function defensesDodgeObject() {
	this.percent = <?php echo $stat["dodge_percent"] ?>;
	this.rating = <?php echo $stat["dodge_rating"] ?>;
	this.increasePercent = <?php echo $dodge_increase_percent ?>;
}

function defensesParryObject() {
	this.percent = <?php echo $stat["parry_percent"] ?>;
	this.rating = <?php echo $stat["parry_rating"] ?>;
	this.increasePercent = <?php echo $parry_increase_percent ?>;
}

function defensesBlockObject() {
	this.percent = <?php echo $stat["block_percent"] ?>;
	this.rating = <?php echo $stat["block_rating"] ?>;
	this.increasePercent = <?php echo $block_increase_percent ?>;
}

function defensesResilienceObject() {
	this.value = <?php echo $stat["resilience_rating"] ?>;
	this.hitPercent = <?php echo $ResiliencehitPercent ?>;
	this.damagePercent = <?php echo $ResiliencedamagePercent ?>;
}


var theCharacter = new characterObject();

<!--var theCharUrl = "r=Doomhammer&n=Super";-->

</script><script src="js/<?php echo LANGUAGE ?>/character-sheet.js" type="text/javascript"></script>
<div class="profile-master" style="height: 500px;">
<div class="stack1">
<img class="ieimg" height="1" src="images/pixel.gif" width="1"><div class="items-left">
<ul>
<?php
$player_items_order = array(0, 1, 2, 14, 4, 3, 18, 8);
for($i = 0; $i <= 7; $i ++)
{
	echo "<li>";
	if(isset($myinventory[$player_items_order[$i]]))
		echo "<img id=\"slot",$player_items_order[$i],"x\" src=\"",$myinventory[$player_items_order[$i]]["icon"],"\"><a class=\"thisTip\" href=\"index.php?searchType=iteminfo&item=",$myinventory[$player_items_order[$i]]["item_template"],"&realm=",REALM_NAME,"\" id=\"slotOver",$player_items_order[$i],"x\" onMouseOut=\"hideTip();\" onmouseover=\"showTip(textLoading); showTooltip(",$myinventory[$player_items_order[$i]]["item"],", ",$realms[REALM_NAME][1],", 1)\"></a>";
	echo "<div id=\"flyOver",$player_items_order[$i],"x\" onMouseOut=\"javascript: mouseOutArrow('",$player_items_order[$i],"');\" onMouseOver=\"javascript: mouseOverUpgradeBox('",$player_items_order[$i],"');\" style=\"visibility: hidden;\">
	</div>
	</li>";
}
?>
</ul>
</div>
<div class="buffs">
<ul>
<?php
switchConnection("characters", REALM_NAME);
$CharacterAuraQuery = execute_query("SELECT `spell` FROM `character_aura` WHERE `guid` = ".$stat["guid"]." AND `effect_index` = 0");
if(mysql_num_rows($CharacterAuraQuery))
{
	require_once "configuration/tooltipmgr.php";
	$aura_i = 0;
	while($CharacterAura = mysql_fetch_assoc($CharacterAuraQuery))
	{
		$aura_i ++;
		switchConnection("armory", REALM_NAME);
		$Spell = mysql_fetch_assoc(execute_query("SELECT * FROM `dbc_spell` WHERE `id` = ".$CharacterAura["spell"]." LIMIT 1"));
	?>
<script type="text/javascript">
	buffArray[<?php echo $aura_i ?>] = "<span class='tooltipContentSpecial tooltipTitle'><?php echo addslashes($Spell["name"]) ?></span>\
	<?php echo spell_parsedata($Spell, "buff") ?>";
</script>
<li>
<img class="ci" height="21" onMouseOut="hideTip()" onMouseOver="showTip(buffArray[<?php echo $aura_i ?>]);" src="<?php echo GetIcon("spell", $Spell["ref_spellicon"]) ?>" width="21"></li>
<?php
	}
}
?>
</ul>
</div>
<div class="debuffs">
<ul></ul>
</div>
<div class="items-right">
<ul>
<?php
$player_items_order = array(9, 5, 6, 7, 10, 11, 12, 13);
for($i = 0; $i <= 7; $i ++)
{
	echo "<li>";
	if(isset($myinventory[$player_items_order[$i]]))
		echo "<img id=\"slot",$player_items_order[$i],"x\" src=\"",$myinventory[$player_items_order[$i]]["icon"],"\"><a class=\"thisTip\" href=\"index.php?searchType=iteminfo&item=",$myinventory[$player_items_order[$i]]["item_template"],"&realm=",REALM_NAME,"\" id=\"slotOver",$player_items_order[$i],"x\" onMouseOut=\"hideTip();\" onmouseover=\"showTip(textLoading); showTooltip(",$myinventory[$player_items_order[$i]]["item"],", ",$realms[REALM_NAME][1],", 1)\"></a>";
	echo "<div id=\"flyOver",$player_items_order[$i],"x\" onMouseOut=\"javascript: mouseOutArrow('",$player_items_order[$i],"');\" onMouseOver=\"javascript: mouseOverUpgradeBox('",$player_items_order[$i],"');\" style=\"visibility: hidden;\">
	</div>
	</li>";
}
?>
</ul>
</div>
<div class="spec">
<img class="ieimg" height="1" src="images/pixel.gif" width="1"><em class="ptl"></em><em class="ptr"></em><em class="pbl"></em><em class="pbr"></em>
<h4><?php echo $lang["talent_specialization"] ?>:</h4>
<div class="spec-wrapper">
<div style="position:absolute; left:15px;">
<img id="talentSpecImage"></div>
<h4>
<!--<a href="character-talents.php?character=<?php echo $stat["name"]; ?>">-->
<div id="replaceTalentSpecText"></div>
</a>
</h4>
<span>
<?php
for($i = 0; $i < 3; $i ++)
{
	if($i)
		echo " / ";
	echo talentCounting($stat["guid"], getTabOrBuild($stat["class"], "tab", $i));
}
?>
</span>
</div>
<span style="display:none;">start</span><script type="text/javascript">
	var talentsTreeArray = new Array;
<?php
for($i = 0; $i < 3; $i ++)
	echo "talentsTreeArray[",$i,"] = [",$i+1,", ",talentCounting($stat["guid"], getTabOrBuild($stat["class"], "tab", $i)),", \"",getTabOrBuild($stat["class"], "build", $i),"\"];\n";
?>
</script>
</div>
<div class="resists">
<em class="ptl"></em><em class="ptr"></em><em class="pbl"></em><em class="pbr"></em>
<h4><?php echo $lang["resistances"] ?>:</h4>
<ul>
<li class="arcane" onMouseOut="hideTip();" onMouseOver="showTip(theText.resistances.arcane.tooltip);">
<b><?php echo $stat["arcane_res"] ?></b><span id="spanResistArcane"><?php echo $stat["arcane_res"] ?></span>
<h4>
<a><script type="text/javascript">document.write(textArcane)</script></a>
</h4>
</li>
<li class="fire" onMouseOut="hideTip();" onMouseOver="showTip(theText.resistances.fire.tooltip);">
<b><?php echo $stat["fire_res"] ?></b><span id="spanResistFire"><?php echo $stat["fire_res"] ?></span>
<h4>
<a><script type="text/javascript">document.write(textFire)</script></a>
</h4>
</li>
<li class="nature" onMouseOut="hideTip();" onMouseOver="showTip(theText.resistances.nature.tooltip);">
<b><?php echo $stat["nature_res"] ?></b><span id="spanResistNature"><?php echo $stat["nature_res"] ?></span>
<h4>
<a><script type="text/javascript">document.write(textNature)</script></a>
</h4>
</li>
<li class="frost" onMouseOut="hideTip();" onMouseOver="showTip(theText.resistances.frost.tooltip);">
<b><?php echo $stat["frost_res"] ?></b><span id="spanResistFrost"><?php echo$stat["frost_res"] ?></span>
<h4>
<a><script type="text/javascript">document.write(textFrost)</script></a>
</h4>
</li>
<li class="shadow" onMouseOut="hideTip();" onMouseOver="showTip(theText.resistances.shadow.tooltip);">
<b><?php echo $stat["shadow_res"] ?></b><span id="spanResistShadow"><?php echo $stat["shadow_res"] ?></span>
<h4>
<a><script type="text/javascript">document.write(textShadow)</script></a>
</h4>
</li>
</ul>
</div>
<div class="profs">
<em class="ptl"></em><em class="ptr"></em><em class="pbl"></em><em class="pbr"></em>
<h4><?php echo $lang["primary_prof"] ?>:</h4>
<?php
foreach($char_primary_prof as $data)
{
	$max_skill = get_prof_max($data[2]);
?>
<div class="prof1">
<div class="profImage">
<img src="images/icons/professions/<?php echo $data[0] ?>.gif"></div>
<h4><a href="index.php?searchType=profile&charPage=skills&character=<?php echo $stat["name"],"&realm=",REALM_NAME ?>"><?php echo $data[1] ?></a></h4>
<div class="bar-container" onMouseOut="hideTip();" onMouseOver="showTip('<?php echo $SkillRankName[$max_skill] ?>');">
<img class="ieimg" height="1" src="images/pixel.gif" width="1"><b style=" width: <?php echo 100*$data[2]/$max_skill ?>%"></b><span><?php echo $data[2],"/",$max_skill ?></span>
</div>
</div>
<?php
}
if($num_primary_prof < 2)
{
	$repeat = $num_primary_prof == 1 ? 1 : 2;
	for($i = 0; $i < $repeat; $i ++)
	{
?>
<div class="prof1">
<div class="profImage">
<img src="images/icons/professions/None.gif"></div>
<h4><?php echo $lang["none"] ?></h4>
<div class="bar-container">
<img class="ieimg" height="1" src="images/pixel.gif" width="1"><b style=" width: 0%"></b><span>0 / 0</span>
</div>
</div>
<?php
	}
}
?>
</div>
</div>
<div class="stack2">
<em class="ptl"></em><em class="ptr"></em><em class="pbl"></em><em class="pbr"></em>
<div class="health-stat">
<h4><?php echo $lang["health"] ?>:</h4>
<p>
<span><?php echo $stat["hp"] ?></span>
</p>
</div>
<?php
switch($stat["class"])
{
	case 1: $barType = "rage"; $mypower = $stat["rage"]; $bartext = $lang["rage"]; break;
	case 4: $barType = "energy"; $mypower = $stat["energy"]; $bartext = $lang["energy"]; break;
	case 6: $barType = "runic"; $mypower = $stat["energy"]; $bartext = $lang["runic"]; break;
	default: $barType = "mana"; $mypower = $stat["hero_mana"]; $bartext = $lang["mana"];
}
?>
<div class="<?php echo $barType ?>-stat">
<h4><?php echo $bartext ?></h4>
<p>
<span><?php echo $mypower ?></span>
</p>
</div>
</div>
<div class="stack3">
<script type="text/javascript">
	var varOverLeft = 0;
</script>
<div class="dropdown1" onMouseOut="javascript: varOverLeft = 0;" onMouseOver="javascript: varOverLeft = 1;">
<a class="profile-stats" href="javascript: document.formDropdownLeft.dummyLeft.focus();" id="displayLeft"><script type="text/javascript">document.write(baseStatsDisplay)</script></a>
</div>
<div style="position: relative;">
<div style="position: absolute;">
<form id="formDropdownLeft" name="formDropdownLeft" style="height: 0px;">
<input id="dummyLeft" onBlur="javascript: if(!varOverLeft) document.getElementById('dropdownHiddenLeft').style.display='none';" onFocus="javascript: dropdownMenuToggle('dropdownHiddenLeft');" size="2" style="position: relative; left: -5000px;" type="button">
</form>
</div>
</div>
<div class="drop-stats" id="dropdownHiddenLeft" onMouseOut="javascript: varOverLeft = 0;" onMouseOver="javascript: varOverLeft = 1;" style="display: none; z-index: 99999;">
<div class="tooltip">
<table>
<tr>
<td class="tl"></td><td class="t"></td><td class="tr"></td>
</tr>
<tr>
<td class="l"></td><td class="bg">
<ul>
<li>
<a href="#" onClick="changeStats('Left', replaceStringBaseStats, 'BaseStats', baseStatsDisplay); return false;"><script type="text/javascript">document.write(baseStatsDisplay)</script><img class="checkmark" id="checkLeftBaseStats" src="images/icon-check.gif" style="visibility: visible;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Left', replaceStringMelee, 'Melee', meleeDisplay); return false;"><script type="text/javascript">document.write(meleeDisplay)</script><img class="checkmark" id="checkLeftMelee" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Left', replaceStringRanged, 'Ranged', rangedDisplay); return false;"><script type="text/javascript">document.write(rangedDisplay)</script><img class="checkmark" id="checkLeftRanged" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Left', replaceStringSpell, 'Spell', spellDisplay); return false;"><script type="text/javascript">document.write(spellDisplay)</script><img class="checkmark" id="checkLeftSpell" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Left', replaceStringDefenses, 'Defenses', defensesDisplay); return false;"><script type="text/javascript">document.write(defensesDisplay)</script><img class="checkmark" id="checkLeftDefenses" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
</ul>
</td><td class="r"></td>
</tr>
<tr>
<td class="bl"></td><td class="b"></td><td class="br"></td>
</tr>
</table>
</div>
</div>
<script type="text/javascript">
	var varOverRight = 0;
</script>
<div class="dropdown2" onMouseOut="javascript: varOverRight = 0;" onMouseOver="javascript: varOverRight = 1;">
<a class="profile-stats" href="javascript: document.formDropdownRight.dummyRight.focus();" id="displayRight"><script type="text/javascript">document.write(baseStatsDisplay)</script></a>
</div>
<div style="position: relative;">
<div style="position: absolute;">
<form id="formDropdownRight" name="formDropdownRight" style="height: 0px;">
<input id="dummyRight" onBlur="javascript: if(!varOverRight) document.getElementById('dropdownHiddenRight').style.display='none';" onFocus="javascript: dropdownMenuToggle('dropdownHiddenRight');" size="2" style="position: relative; left: -5000px;" type="button">
</form>
</div>
</div>
<div class="drop-stats" id="dropdownHiddenRight" onMouseOut="javascript: varOverRight = 0;" onMouseOver="javascript: varOverRight = 1;" style="display: none; z-index: 9999999; left: 190px;">
<div class="tooltip">
<table>
<tr>
<td class="tl"></td><td class="t"></td><td class="tr"></td>
</tr>
<tr>
<td class="l"></td><td class="bg">
<ul>
<li>
<a href="#" onClick="changeStats('Right', replaceStringBaseStats, 'BaseStats', baseStatsDisplay); return false;"><script type="text/javascript">document.write(baseStatsDisplay)</script><img class="checkmark" id="checkRightBaseStats" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Right', replaceStringMelee, 'Melee', meleeDisplay); return false;"><script type="text/javascript">document.write(meleeDisplay)</script><img class="checkmark" id="checkRightMelee" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Right', replaceStringRanged, 'Ranged', rangedDisplay); return false;"><script type="text/javascript">document.write(rangedDisplay)</script><img class="checkmark" id="checkRightRanged" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Right', replaceStringSpell, 'Spell', spellDisplay); return false;"><script type="text/javascript">document.write(spellDisplay)</script><img class="checkmark" id="checkRightSpell" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
<li>
<a href="#" onClick="changeStats('Right', replaceStringDefenses, 'Defenses', defensesDisplay); return false;"><script type="text/javascript">document.write(defensesDisplay)</script><img class="checkmark" id="checkRightDefenses" src="images/icon-check.gif" style="visibility: hidden;"></a>
</li>
</ul>
</td><td class="r"></td>
</tr>
<tr>
<td class="bl"></td><td class="b"></td><td class="br"></td>
</tr>
</table>
</div>
</div>
<div class="stats1">
<em class="ptl"></em><em class="ptr"></em><em class="pbl"></em><em class="pbr"></em>
<div class="character-stats">
<div id="replaceStatsLeft"></div>
</div>
</div>
<div class="stats2">
<em class="ptl"></em><em class="ptr"></em><em class="pbl"></em><em class="pbr"></em>
<div class="character-stats">
<div id="replaceStatsRight"></div>
<script src="js/character/textObjects.js" type="text/javascript"></script>
</div>
</div>
</div>
<div class="stack4">
<div class="items-bot">
<ul>
<?php
$player_items_order = array(15, 16, 17);
for($i = 0; $i <= 2; $i ++)
{
	echo "<li>";
	if(isset($myinventory[$player_items_order[$i]]))
		echo "<img id=\"slot",$player_items_order[$i],"x\" src=\"",$myinventory[$player_items_order[$i]]["icon"],"\"><a class=\"thisTip\" href=\"index.php?searchType=iteminfo&item=",$myinventory[$player_items_order[$i]]["item_template"],"&realm=",REALM_NAME,"\" id=\"slotOver",$player_items_order[$i],"x\" onMouseOut=\"hideTip();\" onmouseover=\"showTip(textLoading); showTooltip(",$myinventory[$player_items_order[$i]]["item"],", ",$realms[REALM_NAME][1],", 1)\"></a>";
	echo "<div id=\"flyOver",$player_items_order[$i],"x\" onMouseOut=\"javascript: mouseOutArrow('",$player_items_order[$i],"');\" onMouseOver=\"javascript: mouseOverUpgradeBox('",$player_items_order[$i],"');\" style=\"visibility: hidden;\">
	</div>
	</li>";
}
?>
</ul>
</div>
<script type="text/javascript">
	var items = new itemsObject();
</script>
</div>
<div class="lastModified">
<span>Last Updated:</span>&nbsp;<strong>Today</strong>
</div>
</div>
<?php
// Arena Teams
$arenatypes = array(2, 3, 5);
switchConnection("characters", REALM_NAME);
foreach($arenatypes as $type)
{
	$teamquery = execute_query("SELECT atm.`arenateamid`, `personal_rating`, `rating`, `rank`, `name`
	FROM `arena_team_member` AS atm, `arena_team` AS at, `arena_team_stats` AS ats
	 WHERE atm.`arenateamid` = at.`arenateamid` AND atm.`arenateamid` = ats.`arenateamid` AND `guid` = ".$stat["guid"]." AND `type` = ".$type." LIMIT 1");
	if($team_info = mysql_fetch_assoc($teamquery))
	{
		$teams[$type]["personal_rating"] = $team_info["personal_rating"];
		$teams[$type]["rating"] = $team_info["rating"];
		$teams[$type]["rank"] = $team_info["rank"];
		$teams[$type]["name"] = $team_info["name"];
		$teams[$type]["link"] = "index.php?searchType=teaminfo&arenateamid=".$team_info["arenateamid"]."&realm=".REALM_NAME;
	}
	else
	{
		$teams[$type]["personal_rating"] = 0;
		$teams[$type]["rating"] = 0;
		$teams[$type]["rank"] = 0;
		$teams[$type]["name"] = $lang["no_arena_team"];
		$teams[$type]["link"] = "#";
	}
}
?>
<div class="bonus-stats" style="">
<table class="deco-frame">
<thead>
<tr>
<td class="sl"></td><td class="ct st"></td><td class="sr"></td>
</tr>
</thead>
<tbody>
<tr>
<td class="sl"><b><em class="port"></em></b></td><td class="ct">
<table>
<tr>
<td class="s-top-left"></td><td class="s-top"></td><td class="s-top-right"></td>
</tr>
<tr>
<td class="s-left">
<div class="shim stable"></div>
</td><td class="s-bg">
<div class="bonus-stats-content">
<div>
<em class="b-title"></em>
<div class="achievements external">
<div id="divAchievementArenaTeams">
<h2>
<a href="index.php?searchType=profile&charPage=arenateams&character=<?php echo $stat["name"],"&realm=",REALM_NAME ?>" style="float:right; position:relative;"><?php echo $lang["more_arena_teams"] ?></a><?php echo $lang["arena"] ?></h2>
<ul class="badges-pvp">
<?php
foreach($arenatypes as $key => $type)
{
if($key == 1)
	$key = "";
?>
<li>
<div class="arena-team-faded">
<h4><?php echo $type,"v",$type ?></h4>
<em><span><?php echo $teams[$type]["name"] ?></span></em>
<div class="icon" id="icon<?php echo $type,"v",$type ?>team" onClick="javascript: window.location.href = &quot;<?php echo $teams[$type]["link"] ?>&quot;" onMouseOut="hideTip();" onMouseOver="showTip(tooltip<?php echo $type,"v",$type ?>team);" style="cursor: pointer;">
<img border="0" class="p" id="badgeBorder<?php echo $type,"v",$type ?>team" src="images/pixel.gif"><div class="rank-num" id="arenarank<?php echo $key ?>">
<div id="arenarank<?php echo $key ?>" style="display:none;"></div>
<script type="text/javascript">
		var flashId="arenarank<?php echo $key ?>";
		if ((is_safari && flashId=="flashback") || (is_linux && flashId=="flashback")){//kill the searchbox flash for safari or linux
		   document.getElementById("searchFlash").innerHTML = '<div class="search-noflash"></div>';
		}else
			printFlash("arenarank<?php echo $key ?>", "images/rank.swf", "transparent", "", "", "100", "40", "best", "", "rankNum=<?php echo ordinal_suffix($teams[$type]["rank"]) ?>", "")
		
		</script>
</div>
</div>
</div>
</li>
<?php
}
?>
</ul>
<ul class="badges-pvp personalrating">
<?php
foreach($arenatypes as $type)
{
?>
<li>
<div>
<em><span><?php echo $lang["personal_rating"],": ",$teams[$type]["personal_rating"] ?></span></em>
</div>
</li>
<?php
}
?>
</ul>
</div>
<script type="text/javascript">

  var arenaTeamArray = new Array;
<?php
foreach($arenatypes as $type)
{
?>
  
      arenaTeamArray[<?php echo $type ?>] = ["<?php echo $teams[$type]["name"] ?>", "", "<?php echo $teams[$type]["rank"] ?>", 0, <?php echo $teams[$type]["rating"] ?>];
<?php
}
?>
  </script><script src="js/character/arenaTooltips.js" type="text/javascript"></script>
<h2>PvP</h2>
<h3><?php echo $lang["honorable_kills"] ?>: <strong><?php echo $stat["kills"] ?></strong>
<br /><?php echo $lang["honor_points"] ?>: <strong><?php echo $stat["honor"] ?></strong>
<br /><?php echo $lang["arena_points"] ?>: <strong><?php echo $stat["arenapoints"] ?></strong>
</h3>
<div style="clear:both;"></div>
</div>
</div>
</div>
</td><td class="s-right">
<div class="shim stable"></div>
</td>
</tr>
<tr>
<td class="s-bot-left"></td><td class="s-bot"></td><td class="s-bot-right"></td>
</tr>
</table>
</td><td class="sr"><b><em class="star"></em></b></td>
</tr>
</tbody>
<tfoot>
<tr>
<td class="sl"></td><td align="center" class="ct sb"><b><em class="foot"></em></b></td><td class="sr"></td>
</tr>
</tfoot>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>