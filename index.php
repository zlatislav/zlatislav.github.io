<?php
	error_reporting(E_ALL);

	$EGN_WEIGHTS = array(2,4,8,5,10,9,7,3,6);

	                                       /* Отделени номера */
	$EGN_REGIONS["Благоевград"]       = 43;  /* от 000 до 043 */ 
	$EGN_REGIONS["Бургас"]            = 93;  /* от 044 до 093 */ 
	$EGN_REGIONS["Варна"]             = 139; /* от 094 до 139 */ 
	$EGN_REGIONS["Велико Търново"]    = 169; /* от 140 до 169 */ 
	$EGN_REGIONS["Видин"]             = 183; /* от 170 до 183 */ 
	$EGN_REGIONS["Враца"]             = 217; /* от 184 до 217 */ 
	$EGN_REGIONS["Габрово"]           = 233; /* от 218 до 233 */ 
	$EGN_REGIONS["Кърджали"]          = 281; /* от 234 до 281 */ 
	$EGN_REGIONS["Кюстендил"]         = 301; /* от 282 до 301 */ 
	$EGN_REGIONS["Ловеч"]             = 319; /* от 302 до 319 */ 
	$EGN_REGIONS["Монтана"]           = 341; /* от 320 до 341 */ 
	$EGN_REGIONS["Пазарджик"]         = 377; /* от 342 до 377 */ 
	$EGN_REGIONS["Перник"]            = 395; /* от 378 до 395 */ 
	$EGN_REGIONS["Плевен"]            = 435; /* от 396 до 435 */ 
	$EGN_REGIONS["Пловдив"]           = 501; /* от 436 до 501 */ 
	$EGN_REGIONS["Разград"]           = 527; /* от 502 до 527 */ 
	$EGN_REGIONS["Русе"]              = 555; /* от 528 до 555 */ 
	$EGN_REGIONS["Силистра"]          = 575; /* от 556 до 575 */ 
	$EGN_REGIONS["Сливен"]            = 601; /* от 576 до 601 */ 
	$EGN_REGIONS["Смолян"]            = 623; /* от 602 до 623 */ 
	$EGN_REGIONS["София - град"]      = 721; /* от 624 до 721 */ 
	$EGN_REGIONS["София - окръг"]     = 751; /* от 722 до 751 */ 
	$EGN_REGIONS["Стара Загора"]      = 789; /* от 752 до 789 */ 
	$EGN_REGIONS["Добрич (Толбухин)"] = 821; /* от 790 до 821 */ 
	$EGN_REGIONS["Търговище"]         = 843; /* от 822 до 843 */ 
	$EGN_REGIONS["Хасково"]           = 871; /* от 844 до 871 */ 
	$EGN_REGIONS["Шумен"]             = 903; /* от 872 до 903 */ 
	$EGN_REGIONS["Ямбол"]             = 925; /* от 904 до 925 */ 
	$EGN_REGIONS["Друг/Неизвестен"]   = 999; /* от 926 до 999 - Такъв регион понякога се ползва при
	                                                            родени преди 1900, за родени в чужбина
	                                                            или ако в даден регион се родят повече
	                                                            деца от предвиденото. Доколкото ми е
	                                                            известно няма правило при ползването
	                                                            на 926 - 999 */ 
	asort($EGN_REGIONS);

	$MONTHS_BG = array(1=>"януари",2=>"февруари",3=>"март",4=>"април",
	                   5=>"май",6=>"юни",7=>"юли",8=>"август",9=>"септември",
	                   10 => "октомври",11=>"ноември",12=>"декември");

	$EGN_REGIONS_LAST_NUM  = array();
	$EGN_REGIONS_FIRST_NUM = array();
	$first_region_num = 0;
	foreach ($EGN_REGIONS as $region => $last_region_num) {
		$EGN_REGIONS_FIRST_NUM[$first_region_num] = $last_region_num;
		$EGN_REGIONS_LAST_NUM[$last_region_num] = $first_region_num;
		$first_region_num = $last_region_num+1;
	}

	/* Check if EGN is valid */
	/* See: http://www.grao.bg/esgraon.html */
	function egn_valid($egn) {
		global $EGN_WEIGHTS;
		if (strlen($egn) != 10)
			return false;
		$year = substr($egn,0,2);
		$mon  = substr($egn,2,2);
		$day  = substr($egn,4,2);
		if ($mon > 40) {
			if (!checkdate($mon-40, $day, $year+2000)) return false;
		} else
		if ($mon > 20) {
			if (!checkdate($mon-20, $day, $year+1800)) return false;
		} else {
			if (!checkdate($mon, $day, $year+1900)) return false;
		}
		$checksum = substr($egn,9,1);
		$egnsum = 0;
		for ($i=0;$i<9;$i++)
			$egnsum += substr($egn,$i,1) * $EGN_WEIGHTS[$i];
		$valid_checksum = $egnsum % 11;
		if ($valid_checksum == 10)
			$valid_checksum = 0;
		if ($checksum == $valid_checksum)
			return true;
	}

	/* Return array with EGN info */
	function egn_parse($egn) {
		global $EGN_REGIONS;
		global $MONTHS_BG;
		if (!egn_valid($egn))
			return false;
		$ret = array();
		$ret["year"]  = substr($egn,0,2);
		$ret["month"] = substr($egn,2,2);
		$ret["day"]   = substr($egn,4,2);
		if ($ret["month"] > 40) {
			$ret["month"] -= 40;
			$ret["year"]  += 2000;
		} else
		if ($ret["month"] > 20) {
			$ret["month"] -= 20;
			$ret["year"]  += 1800;
		} else {
			$ret["year"]  += 1900;
		}
		$ret["birthday_text"] = (int)$ret["day"]." ".$MONTHS_BG[(int)$ret["month"]]." ".$ret["year"]." г.";
		$region = substr($egn,6,3);
		$ret["region_num"] = $region;
		$ret["sex"] = substr($egn,8,1) % 2;
		$ret["sex_text"] = "жена";
		if (!$ret["sex"])
			$ret["sex_text"] = "мъж";
		$first_region_num = 0;
		foreach ($EGN_REGIONS as $region_name => $last_region_num) {
			if ($region >= $first_region_num && $region <= $last_region_num) {
				$ret["region_text"] = $region_name;
				break;
			}
			$first_region_num = $last_region_num+1;
		}
		if (substr($egn,8,1) % 2 != 0)
			$region--;
		$ret["birthnumber"] = ($region - $first_region_num) / 2 + 1;
		return $ret;
	}

	/* Return text with EGN info */
	function egn_info($egn) {
		if (!egn_valid($egn)) {
			return "<b>" . htmlspecialchars($egn) ."</b> невалиден ЕГН";
		}
		$data = egn_parse($egn);
		$ret  = "<b>".htmlspecialchars($egn)."</b> е ЕГН на <b>{$data['sex_text']}</b>, ";
		$ret .= "роден".($data["sex"]?"а":"")." на <b>{$data['birthday_text']}</b> в ";
		$ret .= "регион <b>{$data['region_text']}</b> ";
		if ($data["birthnumber"]-1) {
			$ret .= "като преди ".($data["sex"]?"нея":"него")." ";
			if ($data["birthnumber"]-1 > 1) {
				$ret .= "в този ден и регион са се родили <b>".($data["birthnumber"]-1)."</b>";
				$ret .= $data["sex"]?" момичета":" момчета";
			} else {
				$ret .= "в този ден и регион се е родило <b>1</b>";
				$ret .= $data["sex"]?" момиче":" момче";
			}
		} else {
			$ret .= "като е ".($data["sex"]?"била":"бил")." ";
			$ret .= "<b>първото ".($data["sex"]?" момиче":" момче")."</b> ";
			$ret .= "родено в този ден и регион";
		}
		return $ret;
	}

	/* Generate EGN. When parameter is 0 || false it is randomized */
	function egn_generate($day=0,$mon=0,$year=0,$sex=0,$region=false) {
		global $EGN_WEIGHTS;
		global $EGN_REGIONS_FIRST_NUM;

		$day = $day  > 0 ? min($day, 31) : ($day < 0 ? 0 : $day);
		$mon = $mon  > 0 ? min($mon, 12) : ($mon < 0 ? 0 : $mon);
		$year= $year > 1799 ? min($year, 2099) : ($year == 0 ? $year : 1800);
		$region = isset($EGN_REGIONS_FIRST_NUM[$region]) ? $region : false;

		$iter = 0;
		do {
			$gday  = $day  ? $day  : rand(1,31);
			$gmon  = $mon  ? $mon  : rand(1,12);
			$gyear = $year ? $year : rand(1900,2010);
			$iter++;
		} while (!checkdate($gmon, $gday, $gyear) && $iter < 3);
		$cent = $gyear-($gyear % 100);
		if ($iter > 3)
			return false;
		/* Fixes for other centuries */
		switch ($cent) {
			case 1800: $gmon += 20; break;
			case 2000: $gmon += 40; break;
		}
		/* Generate region/sex */
		if ($region === false)
			$gregion = rand(0,999);
		else
			$gregion = rand($region,$EGN_REGIONS_FIRST_NUM[$region]);
		/* Make it odd */
		if ($sex == 1 && ($gregion % 2 != 0))
			$gregion--;
		/* Make it even */
		if ($sex == 2 && ($gregion % 2 == 0))
			$gregion++;
		/* Create EGN */
		$egn = str_pad($gyear-$cent, 2, "0", STR_PAD_LEFT) .
		       str_pad($gmon, 2, "0", STR_PAD_LEFT) .
		       str_pad($gday, 2, "0", STR_PAD_LEFT) .
		       str_pad($gregion, 3, "0", STR_PAD_LEFT);
		/* Calculate checksum */
		$egnsum = 0;
		for ($i=0;$i<9;$i++)
			$egnsum += substr($egn,$i,1) * $EGN_WEIGHTS[$i];
		$valid_checksum = $egnsum % 11;
		if ($valid_checksum == 10)
			$valid_checksum = 0;
		$egn .= $valid_checksum;

		return $egn;
	}



	/* ********************************************************************* */
	/* *************************** Example usage *************************** */
	/* ********************************************************************* */

	if (isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] == "/view/" ) {
		show_source("egn.php");
		exit;
	}
	if (isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] == "/get/" ) {
		header("Content-Type: text/plain; charset=UTF-8");
		header("Content-Disposition: attachment; filename=egn.php.txt");
		readfile("egn.php");
		exit;
	}

	header("Content-type: text/html; charset=UTF-8");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                      "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
	<title>Информация, проверка и генератор за единни граждански номера (ЕГН)</title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="author" content="Zlatislav Zlatev">
	<style type="text/css">
	<!--
		body    { background: #A3CEE7; font: 11pt Verdana,Arial,Helvetica,sans-serif; }
		a       { color: #2020E0; text-decoration: underline; padding:3px; border:1px solid #A3CEE7; }
		a:hover { background:#cdcdff; text-decoration: underline; border:1px solid blue; }
		h2      { margin:0px; }
		h3      { margin-bottom:0px; }
		.result { background:#be93d7; padding:4px; }
		li.result { background:#be93d7; }
		form    { margin:0px; }
	-->
	</style>
</head>

<body>

<?php
	$egn = "";
	if (isset($_GET["egn"]))
		$egn = preg_replace("/[^0-9]/","",@$_GET["egn"]);
	if (@$_GET["a"] == "check") {
		print "<p class=\"result\"><b><em>Информация за ЕГН:</em></b> ";
		print egn_info($egn);
		print "</p>\n";
	}
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="a" value="check">
ЕГН <input type="text" size="10" maxlength="10" name="egn" value="<?=htmlspecialchars($egn)?>"><input type="submit" value=" Информация за ЕГН ">
</form>



<h3>Генериране на ЕГН по заявка</h3>
<p>Ако вашият ЕГН не се появи в списъка на генерираните, не се стряскайте!
Генераторът вади на случаен принцип номера, които отговарят на зададените
критерии. За всеки регион на ден са предвидени повече от едно раждания,
а на всяко родено дете трябва да се даде валидно ЕГН. Ако държите да видите
своят номер в списъка, просто увеличете броят на генерираните номера.</p>

<?php
	$day    = 0;
	$mon    = 0;
	$year   = 0;
	$sex    = 0;
	$region = 0;
	$numegn = 0;

	if (isset($_GET["d"]))
		$day    = (int)preg_replace("/[^0-9]/","",@$_GET["d"]);

	if (isset($_GET["m"]))
		$mon    = (int)preg_replace("/[^0-9]/","",@$_GET["m"]);

	if (isset($_GET["y"]))
		$year   = (int)preg_replace("/[^0-9]/","",@$_GET["y"]);

	if (isset($_GET["s"]))
		$sex    = (int)preg_replace("/[^0-9]/","",@$_GET["s"]);

	if (isset($_GET["r"]))
		$region = (int)preg_replace("/[^0-9]/","",@$_GET["r"]);

	if (isset($_GET["n"]))
		$numegn = (int)preg_replace("/[^0-9]/","",@$_GET["n"]);

	$numegn = min(max(1,$numegn),99);
	if (!isset($_GET["n"]))
		$numegn = 5;
	$day = $day  > 0 ? min($day, 31) : ($day < 0 ? 0 : $day);
	$mon = $mon  > 0 ? min($mon, 12) : ($mon < 0 ? 0 : $mon);
	$year= $year > 1799 ? min($year, 2099) : ($year == 0 ? $year : 1800);
	$use_region = !isset($EGN_REGIONS_LAST_NUM[$region]) ? false : $EGN_REGIONS_LAST_NUM[$region];

	if (@$_GET["a"] == "gen") {
		print "<p class=\"result\">";
		if (!($use_region === false)) {
			$max = $region-$EGN_REGIONS_LAST_NUM[$region]+1;
			$max2 = $max / 2;
			print "В регион <b>".array_search($region, $EGN_REGIONS)."</b> са предвидени номера за ";
			print "<b>{$max}</b> раждания в един ден (<b>{$max2}</b> момчета и <b>{$max2}</b> момичета).<br />";
		} else {
			print "Изберете регион, за да видите колко раждания са предвидени за него.<br />";
		}
		print "<b><em>Генерирани ЕГН:</em></b></p>\n";
		print "</p>\n";
		print "<ol>\n";
		$results = array();
		for ($i=0;$i<$numegn;$i++) {
			$iter = 0;
			do {
				$result = egn_generate($day,$mon,$year,$sex,$use_region);
			} while ($iter++ < 10 && isset($results[$result]));
			$results[$result] = 1;
		}
		ksort($results);
		foreach ($results as $result => $tmp) {
			print "<li class=\"result\">" . egn_info($result) . "</li>\n";
		}
		print "</ol>\n";
	}
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="a" value="gen">
<table>
<tr>
	<td valign="top" align="right"><b>Пол</b></td>
	<td><label><input type="radio" name="s" value="0"<?=$sex==0?" checked":""?>> Случаен</label>
<label><input type="radio" name="s" value="1"<?=$sex==1?" checked":""?>> Мъж</label>
<label><input type="radio" name="s" value="2"<?=$sex==2?" checked":""?>> Жена</label>
</td>
</tr>
<tr>
	<td valign="top" align="right"><b>Дата на<br />раждане</b></td>
	<td>
<table>
<tr>
	<td align="right"><label for="d">ден</label></td>
	<td><input type="text" size="2" maxlength="2" id="d" name="d" value="<?=htmlspecialchars($day)?>"></td>
	<td><em><label for="d">0 = случаен, валидни стойност 0,1-31</label></em></td>
</tr>
<tr>
	<td align="right"><label for="m">месец</label></td>
	<td><input type="text" size="2" maxlength="2" id="m" name="m" value="<?=htmlspecialchars($mon)?>"></td>
	<td><em><label for="m">0 = случаен, валидни стойност 0,1-12</label></em></td>
</tr>
<tr>
	<td align="right"><label for="y">година</td>
	<td><input type="text" size="4" maxlength="4"id="y"  name="y" value="<?=htmlspecialchars($year)?>"></td>
	<td><em><label for="y">0 = случаен, валидни стойност 0,1800-2099</label></em></td>
</tr>
</table>
	</td>
</tr>
<tr>
	<td valign="top" align="right"><b><label for="n">Генерирай</label></b></td>
	<td><input type="text" size="2" maxlength="2" id="n" name="n" value="<?=htmlspecialchars($numegn)?>"><label for="n"> номера, <em>валидни стойност 1-99</label></em></td>
</tr>
<tr>
	<td valign="top" align="right"><b><label for="r">Регион</label></b></td>
	<td><select id="r" name="r"><option value="0">-- Случаен --</option>
<?php
	foreach ($EGN_REGIONS as $region_name => $region_num) {
		$current = ($region == $region_num?" selected":"");
		print "<option value=\"{$region_num}\"{$current}>{$region_name}</option>\n";
	}
?></select> <input type="submit" value=" Генерирай ЕГН "></td>
</tr>
</table>
</form>



<h3>Пет случайно генерирани ЕГН</h3>
<ul><?php
	$results = array();
	for ($i=0;$i<5;$i++) {
		$iter = 0;
		do {
			$result = egn_generate();
		} while ($iter++ < 10 && isset($results[$result]));
		$results[$result] = 1;
	}
	ksort($results);
	foreach ($results as $result => $tmp) {
		print "<li>" . egn_info($result) . "</li>\n";
	}
?></ul>
</body>
</html>
