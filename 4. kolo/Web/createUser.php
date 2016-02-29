<?php
	$db_file = 'databaze_malinovky.db';
	$db = new SQLite3($db_file);

	$prikaz = $db->exec("INSERT INTO users VALUES (". $_GET['login'] .",". sha1($_GET['password']) .")");
	if($prikaz){
		echo 'true';
	} else {
		echo 'false';
	}
?>