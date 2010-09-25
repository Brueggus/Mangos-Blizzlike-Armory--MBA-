<?php
function assign_stats($data)
{
	global $defines;
	$statistic_data = explode(" ",$data["data"]);
	switchConnection("mangos", REALM_NAME);
	$stat = array();
	$results = mysql_fetch_assoc(execute_query("SELECT `str`, `agi`, `sta`, `inte`, `spi` FROM `player_levelstats` WHERE `race` = ".$data["race"]." AND `class` = ".$data["class"]." AND `level` = ".$statistic_data[$defines["LEVEL"][CLIENT]]." LIMIT 1"));
	$gender = dechex($statistic_data[$defines["GENDER"][CLIENT]]);
	$gender = str_pad($gender, 8, 0, STR_PAD_LEFT);
	$stat["strength_eff"] = $statistic_data[$defines["STRENGTH"][CLIENT]];
	$stat["strength_base"] = $results["str"];
	$stat["agility_eff"] = $statistic_data[$defines["AGILITY"][CLIENT]];
	$stat["agility_base"] = $results["agi"];
	$stat["stamina_eff"] = $statistic_data[$defines["STAMINA"][CLIENT]];
	$stat["stamina_base"] = $results["sta"];
	$stat["intellect_eff"] = $statistic_data[$defines["INTELLECT"][CLIENT]];
	$stat["intellect_base"] = $results["inte"];
	$stat["spirit_eff"] = $statistic_data[$defines["SPIRIT"][CLIENT]];
	$stat["spirit_base"] = $results["spi"];
	$stat["hp"] = $statistic_data[$defines["HP"][CLIENT]];
	$stat["hero_mana"] = $statistic_data[$defines["MANA"][CLIENT]];
	$stat["rage"] = $statistic_data[$defines["RAGE"][CLIENT]]/10;
	$stat["energy"] = $statistic_data[$defines["ENERGY"][CLIENT]];
	$stat["armor_eff"] = $statistic_data[$defines["ARMOR"][CLIENT]];
	//$stat["holy_res"] = $statistic_data[$defines["HOLY_RES"][CLIENT]];
	$stat["fire_res"] = $statistic_data[$defines["FIRE_RES"][CLIENT]];
	$stat["nature_res"] = $statistic_data[$defines["NATURE_RES"][CLIENT]];
	$stat["frost_res"] = $statistic_data[$defines["FROST_RES"][CLIENT]];
	$stat["shadow_res"] = $statistic_data[$defines["SHADOW_RES"][CLIENT]];
	$stat["arcane_res"] = $statistic_data[$defines["ARCANE_RES"][CLIENT]];
	$stat["level"] = $statistic_data[$defines["LEVEL"][CLIENT]];
	//$stat["guild"] = $statistic_data[$defines["GUILD"][CLIENT]];
	//$stat["guildrank"] = $statistic_data[$defines["GUILD_RANK"][CLIENT]];
	$stat["kills"] = $statistic_data[$defines["KILLS"][CLIENT]];
	$stat["honor"] = $statistic_data[$defines["HONOR"][CLIENT]];
	$stat["arenapoints"] = $statistic_data[$defines["ARENAPOINTS"][CLIENT]];
	$stat["gender"] = $gender{3};
	$stat["race"] = $data["race"];
	$stat["class"] = $data["class"];
	$stat["name"] = $data["name"];
	$stat["guid"] = $data["guid"];
	$stat["melee_ap_base"] = $statistic_data[$defines["MELEE_AP_BASE"][CLIENT]];
	$stat["melee_ap_bonus"] = $statistic_data[$defines["MELEE_AP_BONUS"][CLIENT]];
	$stat["melee_ap"] = $stat["melee_ap_base"] + $stat["melee_ap_bonus"];
	$stat["ranged_ap_base"] = $statistic_data[$defines["RANGED_AP_BASE"][CLIENT]];
	$stat["ranged_ap_bonus"] = $statistic_data[$defines["RANGED_AP_BONUS"][CLIENT]];
	$stat["ranged_ap"] = $stat["ranged_ap_base"] + $stat["ranged_ap_bonus"];
	$stat["block_percent"] = unpack("f", pack("L", $statistic_data[$defines["BLOCK_PERCENTAGE"][CLIENT]]));
	$stat["dodge_percent"] = unpack("f", pack("L", $statistic_data[$defines["DODGE_PERCENTAGE"][CLIENT]]));
	$stat["parry_percent"] = unpack("f", pack("L", $statistic_data[$defines["PARRY_PERCENTAGE"][CLIENT]]));
	$stat["crit_percent"] = unpack("f", pack("L", $statistic_data[$defines["CRIT_PERCENTAGE"][CLIENT]]));
	$stat["ranged_crit_percent"] = unpack("f", pack("L", $statistic_data[$defines["RANGED_CRIT_PERCENTAGE"][CLIENT]]));
	for($i = 1; $i < 7; $i ++)
		$stat["spell_crit_percent_".$i] = unpack("f", pack("L", $statistic_data[$defines["SPELL_CRIT_PERCENTAGE"][CLIENT]+$i]));
	for($i = 1; $i < 7; $i ++)
		$stat["spell_damage_".$i] = $statistic_data[$defines["SPELL_DAMAGE"][CLIENT]+$i];
	$stat["spell_healing"] = $statistic_data[$defines["SPELL_HEALING"][CLIENT]];
	$stat["expertise"] = $statistic_data[$defines["EXPERTISE"][CLIENT]];
	$stat["defense_rating"] = $statistic_data[$defines["DEFENSE_RATING"][CLIENT]];
	$stat["dodge_rating"] = $statistic_data[$defines["DODGE_RATING"][CLIENT]];
	$stat["parry_rating"] = $statistic_data[$defines["PARRY_RATING"][CLIENT]];
	$stat["block_rating"] = $statistic_data[$defines["BLOCK_RATING"][CLIENT]];
	$stat["melee_hit_rating"] = $statistic_data[$defines["MELEE_HIT_RATING"][CLIENT]];
	$stat["ranged_hit_rating"] = $statistic_data[$defines["RANGED_HIT_RATING"][CLIENT]];
	$stat["spell_hit_rating"] = $statistic_data[$defines["SPELL_HIT_RATING"][CLIENT]];
	$stat["melee_crit_rating"] = $statistic_data[$defines["MELEE_CRIT_RATING"][CLIENT]];
	$stat["ranged_crit_rating"] = $statistic_data[$defines["RANGED_CRIT_RATING"][CLIENT]];
	$stat["spell_crit_rating"] = $statistic_data[$defines["SPELL_CRIT_RATING"][CLIENT]];
	$stat["resilience_rating"] = $statistic_data[$defines["RESILIENCE_RATING"][CLIENT]];
	$stat["melee_haste_rating"] = $statistic_data[$defines["MELEE_HASTE_RATING"][CLIENT]];
	$stat["ranged_haste_rating"] = $statistic_data[$defines["RANGED_HASTE_RATING"][CLIENT]];
	$stat["spell_haste_rating"] = $statistic_data[$defines["SPELL_HASTE_RATING"][CLIENT]];
	$stat["expertise_rating"] = $statistic_data[$defines["EXPERTISE_RATING"][CLIENT]];
	$stat["meele_main_hand_min_dmg"] = unpack("f", pack("L", $statistic_data[$defines["MEELE_MAIN_HAND_MIN_DAMAGE"][CLIENT]]));
	$stat["meele_main_hand_max_dmg"] = unpack("f", pack("L", $statistic_data[$defines["MEELE_MAIN_HAND_MAX_DAMAGE"][CLIENT]]));
	$stat["meele_main_hand_attack_time"] = unpack("f", pack("L", $statistic_data[$defines["MEELE_MAIN_HAND_ATTACK_TIME"][CLIENT]]));
	$stat["meele_off_hand_min_dmg"] = unpack("f", pack("L", $statistic_data[$defines["MEELE_OFF_HAND_MIN_DAMAGE"][CLIENT]]));
	$stat["meele_off_hand_max_dmg"] = unpack("f", pack("L", $statistic_data[$defines["MEELE_OFF_HAND_MAX_DAMAGE"][CLIENT]]));
	$stat["meele_off_hand_attack_time"] = unpack("f", pack("L", $statistic_data[$defines["MEELE_OFF_HAND_ATTACK_TIME"][CLIENT]]));
	$stat["ranged_attack_time"] = unpack("f", pack("L", $statistic_data[$defines["RANGED_ATTACK_TIME"][CLIENT]]));
	$stat["ranged_min_dmg"] = unpack("f", pack("L", $statistic_data[$defines["RANGED_MIN_DAMAGE"][CLIENT]]));
	$stat["ranged_max_dmg"] = unpack("f", pack("L", $statistic_data[$defines["RANGED_MAX_DAMAGE"][CLIENT]]));
	$stat["mana_regen"] = unpack("f", pack("L", $statistic_data[$defines["MANA_REGEN"][CLIENT]]));
	$stat["mana_regen_interrupt"] = unpack("f", pack("L", $statistic_data[$defines["MANA_REGEN_INTERRUPT"][CLIENT]]));
	return $stat;
}
function stathandler_getratingformula($Type, $Stat, $CharacterLevel, $RoundPoints = 2)
{
	global $RatingBases;
	$Base = $RatingBases[$Type];
	if($CharacterLevel <= 34 && ($Type == "dodge" || $Type == "block" || $Type == "parry" || $Type == "defense"))
		$RequiredPercent = $Base/2;
	else if($CharacterLevel <= 10)
		$RequiredPercent = $Base/26;
	else if($CharacterLevel <= 60)
		$RequiredPercent = $Base*($CharacterLevel-8)/52;
	else if($CharacterLevel >= 61 && $CharacterLevel <= 70)
		$RequiredPercent = $Base*82/(262-3*$CharacterLevel);
	else
		$RequiredPercent = ($Base*82/52)*pow(131/63, ($CharacterLevel-70)/10);
	return round($Stat/$RequiredPercent, $RoundPoints);
}
// Damage Reduction from Armor //
function stathandler_getdamagereduction($cLevel, $cArmor)
{
	// From WoWWiki: Level 1-60 %Reduction = (Armor/(Armor+400+85*Enemy_Level))*100 //
	// From WoWWiki: 60+ %Reduction = (Armor/(Armor-22167.5+467.5*Enemy_Level))*100 //
	if($cLevel <= 60)
		return round(($cArmor/($cArmor+400+85*$cLevel))*100, 2);
	else
		return round(($cArmor/($cArmor-22167.5+467.5*$cLevel))*100, 2);
}
//talent counting
function talentCounting($guid, $tab)
{
	$pt = 0;
	switchConnection("characters", REALM_NAME);
	$resSpell = execute_query("SELECT `spell` FROM `character_spell` WHERE `guid` = ".$guid." AND `disabled` = 0");
	if(mysql_num_rows($resSpell))
	{
		while($getSpell = mysql_fetch_assoc($resSpell))
			$spells[] = $getSpell["spell"];
		switchConnection("armory", REALM_NAME);
		$resTal = execute_query("SELECT `rank1`, `rank2`, `rank3`, `rank4`, `rank5` FROM `dbc_talent` WHERE `ref_talenttab` = ".$tab);
		while($row = mysql_fetch_assoc($resTal))
			$ranks[] = $row;
		foreach($ranks as $key => $val)
		{
			foreach($spells as $k => $v)
			{
				if(in_array($v, $val))
				{
					switch(array_search($v, $val))
					{
						case "rank1": $pt += 1; break;
						case "rank2": $pt += 2; break;
						case "rank3": $pt += 3; break;
						case "rank4": $pt += 4; break;
						case "rank5": $pt += 5; break;
					}
				}
			}
		}
	}
	return $pt;
}
//get a tab from TalentTab
function getTabOrBuild($class, $type, $tabnum)
{
	if($type == "tab")
		$field = "id";
	else //$type == "build"
		$field = "name";
	switchConnection("armory", REALM_NAME);
	return mysql_result(execute_query("SELECT `".$field."` FROM `dbc_talenttab` WHERE `refmask_chrclasses` = ".pow(2,($class-1))." AND `tab_number` = ".$tabnum." LIMIT 1"), 0);
}
function HPRegenFromSpirit($level, $class, $spirit_eff, $spirit_base)
{
	if($level > 100)
		$level = 100;
	$ratio_index = (($class-1)*100 + $level-1) + 1;
	switchConnection("armory", REALM_NAME);
	$baseRatio = mysql_result(execute_query("SELECT `ratio` FROM `dbc_gtoctregenhp` WHERE `id` = ".$ratio_index." LIMIT 1"), 0);
	$moreRatio = mysql_result(execute_query("SELECT `ratio` FROM `dbc_gtregenhpperspt` WHERE `id` = ".$ratio_index." LIMIT 1"), 0);
	if($spirit_base > 50)
		$spirit_base = 50;
	$moreSpirit = $spirit_eff - $spirit_base;
	return floor($spirit_base * $baseRatio + $moreSpirit * $moreRatio);
}
function MPRegenFromSpirit($level, $class, $spirit_eff, $intelect)
{
	if($level > 100)
		$level = 100;
	$ratio_index = (($class-1)*100 + $level-1) + 1;
	switchConnection("armory", REALM_NAME);
	$moreRatio = mysql_result(execute_query("SELECT `ratio` FROM `dbc_gtregenmpperspt` WHERE `id` = ".$ratio_index." LIMIT 1"), 0);
	return floor(5 * sqrt($intelect) * $spirit_eff * $moreRatio);
}
function get_prof_max($skill_points)
{
	return ($skill_points < 76?75:($skill_points < 151?150:($skill_points < 226?225:($skill_points < 301?300:($skill_points < 376?375:($skill_points < 451?450:460))))));
}
?>