<?php
session_start();

$db_file = 'databaze_malinovky.db';
$db = new SQLite3($db_file);
$results = $db->query('SELECT * FROM users');

if(isset($_SESSION['name']) && isset($_SESSION['password'])){
	$ifPassOkSession = false;

	while ($row = $results->fetchArray()) {
		if($row['login'] == $_SESSION['name'] && $row['password'] == sha1($_SESSION['password'])){
			$ifPassOkSession = true;
		}
	}

	if(!$ifPassOkSession){
		echo 'Špatná session, přihlašte se znovu';
		die();
	}
} else {
	echo 'Neexistující session';
	die();
}

if(isset($_POST['submit'])){
	$ifNazev = true;
	$_POST['name'] = htmlspecialchars($_POST['name']);
	$_POST['hash'] = htmlspecialchars($_POST['hash']);

	$results = $db->query('SELECT * FROM modules');
	while ($row = $results->fetchArray()) {
		if($row['nazev'] == $_POST['name']){
			$ifNazev = false;
		}
	}

	if($ifNazev == true){
		$insertNew = $db->exec('INSERT INTO modules (nazev, last, temp, hash) VALUES ("'. $_POST['name'] .'", "'. (time() + 3600) .'", "0", "'. $_POST['hash'] .'")');

		$insertNewHash = $db->exec('INSERT INTO allowedHashes (hash) VALUES ("'. $_POST['hash'] .'")');

		if($insertNew && $insertNewHash){
			echo '<script>alert("Modul byl úspěšně přidán");</script>';
		} else {
			echo '<script>alert("Upsiček, něco se pokazilo...");</script>';
		}
	} else {
		echo '<script>alert("Název již existuje");</script>';
	}
}

if(isset($_POST['logout'])){
	unset($_SESSION['name']);
	unset($_SESSION['password']);

	session_destroy();
	header('Location: index.php');
}

if(isset($_GET['delete'])){
	$deleteExec = $db->exec('DELETE FROM modules WHERE id = "'. $_GET['delete'] .'"');
	
	if($deleteExec){
		echo '<script>alert("Smazáno!");</script>';
	} else {
		echo '<script>alert("Upsiček, něco se pokazilo při mazání...");</script>';
	}

	header('Location: login_after.php');
}

if(isset($_POST['submit_main_module'])){
	$set_main = $db->exec('UPDATE modules SET is_main="0"');
	$set_main_module = $db->exec('UPDATE modules SET is_main="1" WHERE id="'. $_POST['select_module'] .'" ');
	
	if($set_main && $set_main_module){
		echo '<script>alert("Hlavní modul nastaven");</script>';
	} else {
		echo '<script>alert("Upsiček, něco se pokazilo při nastavování hlavního modulu...");</script>';
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta name="author" content="Malinovky5">
	<meta name="keywords" content="javascript, canvas, library, TinyCanvas, Malinovky5, sšinfotech">
	<title>Meteostanice SŠINFOTECH</title>
	
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
	<script src="js/bootstrap.js" type="text/javascript"></script>
</head>
<body>
	<form method="POST">
		<div class="row" style="padding-top: 20px; padding-bottom: 20px;">
			<div class="col-md-1" style="padding-left: 25px;"><img src="design/images/logo_malinovky.png" width="210" height="80"></div>
			<div class="col-md-1 col-md-offset-10">
				<div class="input-group" style="min-width: 200px">
					<span class="input-group-btn">
						<button class="btn btn-default" type="submit" name="logout" style="border-radius: 5px; margin-top: 40px;"><span class="glyphicon glyphicon-log-out" aria-hidden="true" style="padding-right: 5px;"></span>Odhlásit se</button>
					</span>
				</div>

			</div>
		</div>
	</div>
	<hr />

	<div class="input-group input-group-lg col-md-4 col-md-offset-4" style="padding-bottom: 20px;">
		<span class="input-group-addon" id="sizing-addon1">Název modulu</span>
		<input type="text" name="name" class="form-control" placeholder="Zadejte název modulu" aria-describedby="sizing-addon1">
	</div>
	<div class="input-group input-group-lg col-md-4 col-md-offset-4">
		<span class="input-group-addon" id="sizing-addon1">Hash klíč modulu</span>
		<input type="text" class="form-control" name="hash" value="<?php echo md5(uniqid(rand(), true)); ?>" aria-describedby="sizing-addon1" readonly>
	</div>
	<div class="input-group input-group-lg col-md-4 col-md-offset-4" style="padding-top: 20px;">
		<div class="btn-group btn-group-lg" role="group" aria-label="...">
			<button type="submit" name="submit" class="btn btn-default"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="padding-right: 5px;"></span>Vytvořit</button>
			<button type="button" class="btn btn-default"><span class="glyphicon glyphicon-log-out" aria-hidden="true" style="padding-right: 5px;"></span>Zrušit</button>
		</div>
	</div>
</form>

<div class="row" style="margin-top: 1em">
	<div class="col-md-10 col-md-offset-1">
		<div class="panel panel-default">
			<div class="panel-body">
				<h2>Tabulka modulů</h2>
				<form method="POST">
				<table class="table">
					<thead class="thead-inverse">
						<tr>
							<th>Upravit</th>
							<th>Smazat</th>
							<th>Název modulu</th>
							<th>Poslední aktualizace</th>
							<th>Stav modulu</th>
							<th>Teplota udávaná modulem</th>
							<th>Graf vývoje teploty</th>
							<th>Hash klíče modulu</th>
							<th>Hlavní modul</th>
						</tr>
					</thead>
					<tbody>

						<?php 
						date_default_timezone_set("Europe/Prague");
						$db_file = 'databaze_malinovky.db';
						$db = new SQLite3($db_file);
						$results = $db->query('SELECT * FROM modules ORDER BY id');	
						$result_hash = $db->query('SELECT * FROM allowedHashes');
						$currentTime = time() + 3600;

						while ($row = $results->fetchArray()) {
							while ($rowCheck = $result_hash->fetchArray()) {
								if($rowCheck['hash'] == $row['hash']){
									echo '<tr>';
									echo '<td><a target="_blank" href="change.php?id='. $row['id'] .'"><span class="glyphicon glyphicon-pencil" aria-hidden="true" style="padding-left: 17px;"></span></a></td>';
									echo '<td><a href="login_after.php?delete='. $row['id'] .'"><span class="glyphicon glyphicon-remove" aria-hidden="true" style="padding-left: 17px;"></span></a></td>';
									echo '<td style="font-size: 17px;">' . $row['nazev'] . '</td>';
									echo '<td>' . gmdate("d. m. Y H:i:s", $row['last']) . '</td>';

									if($currentTime - $row['last'] < 360){
										// online
										echo '<td style="color: green;">Online</td>';
									} else {
										echo '<td style="color: red;">Offline</td>';
									}

									echo '<td>' . $row['temp'] . '&deg;</td>';
									echo '<td><a href="graph.php?hash='. $row['hash'] .'" target="_blank">Otevřít</a>';
									echo '<td>' . $row['hash'] . '</td>';	

									if($row['is_main'] == 1){
										echo '<td> <img src="https://cdn3.iconfinder.com/data/icons/musthave/128/Check.png" style="width: 20px; height: 20px;" /> </td>';
									} else {
										echo '<td> <img src="http://icons.iconarchive.com/icons/kyo-tux/phuzion/256/Sign-Error-icon.png" style="width: 20px; height: 20px;" /> </td>';
									}	

									echo '</tr>';
								}
							}

						}
						?>
					</tbody>
				</table>
				<?php
				$db_file = 'databaze_malinovky.db';
				$db = new SQLite3($db_file);
				$results = $db->query('SELECT * FROM modules ORDER BY is_main DESC');	
				$result_hash = $db->query('SELECT * FROM allowedHashes');
				
				echo '<select name="select_module">';

				while ($row = $results->fetchArray()) {

					while ($rowCheck = $result_hash->fetchArray()) {
						if($rowCheck['hash'] == $row['hash']){
							echo '<option value="'. $row['id'] .'">'. $row['nazev'] .'</option>';	
						}
					}
				}

				echo '</select>';

				?>
				<button type="submit" name="submit_main_module" class="btn btn-default"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="padding-right: 5px;"></span>Nastavit hlavní modul</button>
				</form>

			</div>
		</div>
	</div>
</div>
</div>


<div class="copyright"><hr />Vojtěch Kožuch, David Pavelka, Dan Krůl</div>
<script type="text/javascript" src="js/chart.js"></script>
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>