<?php
	$db_file = 'databaze_malinovky.db';
	$db = new SQLite3($db_file);
	$result_hash = $db->query('SELECT * FROM allowedHashes');
	$currentTime = time() + 3600;

	while ($rowCheck = $result_hash->fetchArray()) {
		if($rowCheck['hash'] == $_GET['hashKey']){
			//echo 'Je tam';
			$prikaz = $db->exec("UPDATE modules SET temp='". $_GET['temp'] ."', last='". $currentTime ."' WHERE hash='". $_GET['hashKey'] ."' ");
			if($prikaz){
				echo 'true';
			} else {
				echo 'false';
			}
		}
	}
?>