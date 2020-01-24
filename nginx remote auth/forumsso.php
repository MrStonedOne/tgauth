<?php
	http_response_code(500);
	require_once("forumsso.php");
	require_once("../tgdb/config.php");
	//returns string with $config['sql']['tableprefex'] prepended to it
	function fmttable($table) {
		global $tgdbconfig;
		if (isset($tgdbconfig['sql']['tableprefix']) && !empty($tgdbconfig['sql']['tableprefix']))
			return $tgdbconfig['sql']['tableprefix'].$table;
		
		return $table;
	}
	function esc ($text) {
		global $mysqli;
		return $mysqli->real_escape_string($text);
	}	
	function keytockey ($key, $keepmysqlwildcards = true) {
		if ($keepmysqlwildcards)
			return strtolower(preg_replace('/[^a-zA-Z0-9@%]/', '', $key));
		else 
			return strtolower(preg_replace('/[^a-zA-Z0-9@]/', '', $key));
	}
	
	$mysqli = get_new_mysqli();

	if ($mysqli->connect_errno) {
		error_log("auth connection error");
		die ("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
	}
	function get_new_mysqli() {
		global $tgdbconfig;
		static $tgdbpassword;
		//To lower the window that any exploit that exposes global variables has to get sql database information, we remove the password and store it statically in this function. (of course, an exploit could exist that exposes static vars, but thats rarely a target)
		if (isset($tgdbconfig['sql']['password'])) {
			$tgdbpassword = $tgdbconfig['sql']['password'];
			unset($tgdbconfig['sql']['password']);
		}
		$mysqli = new \mysqli();
		$mysqli->real_connect($tgdbconfig['sql']['addr'], $tgdbconfig['sql']['user'], $tgdbpassword, $tgdbconfig['sql']['db'], (is_int($tgdbconfig['sql']['port']) ? $tgdbconfig['sql']['port'] : null), (!is_int($tgdbconfig['sql']['port']) ? $tgdbconfig['sql']['port'] : null));
		return $mysqli;
	}
	
	$sql = "SELECT `pf_byond_username` FROM `".PROFILE_FIELDS_DATA_TABLE."` WHERE user_id = ".$userid;
	$result = $db->sql_query($sql);
	$key = $db->sql_fetchfield('pf_byond_username');
	$db->sql_freeresult($result);
	if (!$key) {
		http_response_code(401);
		error_log("auth no linked key");
		die();
	}

	$ckey = keytockey($key, false);
	//alright, we have their ckey now. 
	if (in_array($ckey,$tgdbconfig['authsettings']['blockedckeys'])) {
		error_log("auth blocked ckey");
		http_response_code(403);
		die();
	}
	if (in_array($ckey,$tgdbconfig['authsettings']['whitelistedckeys'])) {
		http_response_code(200);
		die();
	}
	
	$ttl = 168;
	
	if (isset($config['authsettings']['validitytime']) && (int)$config['authsettings']['validitytime'])
		$ttl = (int)$config['authsettings']['validitytime'];
	$res = $mysqli->query("SELECT a.rank as lastadminrank, p.lastseen FROM `".fmttable("admin")."` AS `a` INNER JOIN `".fmttable("player")."` AS `p` ON a.ckey = p.ckey WHERE a.ckey = '".esc($ckey)."'");
	//$res = $mysqli->query("SELECT lastadminrank, lastseen FROM `".fmttable("player")."` WHERE ckey = '".esc($ckey)."'");
	
	if (!$res) {
		error_log("auth admin check query failed");
		die();
	}
	if (!$row = $res->fetch_assoc()) {
		error_log("auth admin check query returned no results");
		die();
	}
	$rank = $row['lastadminrank'];

	if (in_array(strtolower($rank), $tgdbconfig['authsettings']['excludedranks'])) {
		error_log("auth excluded rank");
		http_response_code(403);
		die();
	}
	
	$lastseen = new DateTime($row['lastseen']);
	if ($lastseen < (New DateTime())->sub(new DateInterval('PT'.$ttl.'H'))) {
		http_response_code(401);
		error_log("auth too old");
		die();
	}
	
	http_response_code(200);

?>