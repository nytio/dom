<?php
include("conectadb.php");
include("ip-addresses.php");

header('Access-Control-Allow-Origin: *');
$callback = isset($_GET['callback']) ? preg_replace('/[^a-z0-9$_]/si', '', $_GET['callback']) : false;
header('Content-Type: ' . ($callback ? 'application/javascript' : 'application/json') . ';charset=UTF-8');
if(!ob_start('ob_gzhandler')) ob_start();
$json = array();
if (isset ( $_REQUEST ['tg'] ) && $_REQUEST ['tg'] != "" && isset ( $_REQUEST ['nv'] ) && $_REQUEST ['nv'] != "") {
	if ($_REQUEST ['tg'] == 'yhteys') {

		$voca = 'aeiouyAEUY68';
		$cons = 'bcdfghjklmnpqrstvwxzBDGHJLMNPQRSTVWXZ234579';
		$contra = '';
		$alt = time() % 2;
		for ($i = 0; $i < 7 + (rand() % 13); $i++) {
			if ($alt == 1) {
				$contra .= $cons[(rand() % strlen($cons))];
				$alt = 0;
			} else {
				$contra .= $voca[(rand() % strlen($voca))];
				$alt = 1;
			}
		}

		$sql = "INSERT INTO log_visitas(ip, nav, sd) VALUES ('".get_user_ip_address().",".$_REQUEST ['nv']."', '" . $_SERVER ['HTTP_USER_AGENT'] . "', MD5('$contra')) RETURNING id;";
		$result = pg_query ( $dbconn, $sql );
		if (! $result) {
			$json ['suc'] = 'virhe';
			exit ();
		} else {
			$reg = pg_fetch_assoc ( $result );
			$json ['suc'] = 'oikea';
			$json ['idu'] = $reg ['id'];
			$json ['sdu'] = $contra;
		}
	} else {
		$json ['suc'] = 'väärä parametri';
	}
} elseif (isset ( $_REQUEST ['tg'] ) && $_REQUEST ['tg'] != "" && isset ( $_REQUEST ['id'] ) && $_REQUEST ['id'] != "" && isset ( $_REQUEST ['sd'] ) && $_REQUEST ['sd'] != "") {
	if ($_REQUEST ['tg'] == 'yhteys') {
		// Verifica contraseñas
		$sql = "SELECT count(*) FROM log_visitas WHERE id = ".$_REQUEST["id"]." AND sd = MD5('".$_REQUEST["sd"]."');";
		$result = pg_query($dbconn, $sql);
		$reg = pg_fetch_assoc($result);
		if (!$reg['count']) {
			$json ['suc'] = 'virhe';
			exit ();
		} else {
			$sql = "INSERT INTO log_uso(idu, ip, nav) VALUES (".$_REQUEST ['id'].", '".get_user_ip_address()."', '" . $_SERVER ['HTTP_USER_AGENT'] . "');";
			$result = pg_query ( $dbconn, $sql );
			$json ['suc'] = 'oikea';
		}
	} else {
		$json ['suc'] = 'väärä parametri';
	}
} else {
	$json ['suc'] = 'puuttuvat parametrit';
}

$json = json_encode($json);
echo $callback ? "$callback($json)" : $json;