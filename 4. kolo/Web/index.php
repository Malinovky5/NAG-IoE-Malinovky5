<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="">
	<meta name="author" content="Malinovky5">
	<meta name="keywords" content="javascript, Malinovky5, sšinfotech">
	<title>Malinovky5</title>
	
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<div class="row" style="padding-top: 20px; padding-bottom: 20px;">
		<div class="col-md-4" style="padding-left: 25px;"><img src="design/images/logo_malinovky.png" width="300"></div>
		<form method="POST">
			<div class="col-md-3 col-md-offset-5" style="padding-right: 25px;"><div class="form-group">
          <input type="text" class="form-control" name="login" placeholder="Přihlašovací jméno">
        </div>
		<div class="input-group" style="min-width: 200px">
		  <input type="password" class="form-control" placeholder="Heslo" name="password">
		  <span class="input-group-btn">
			<input type="submit" class="btn btn-default" value="Přihlásit" name="send" />
		  </span>
		  <?php
			session_start();
			$db_file = 'databaze_malinovky.db';
			$db = new SQLite3($db_file);
			$results = $db->query('SELECT * FROM users');

			if(isset($_POST['send'])){
		
				$ifPassOk = false;

				while ($row = $results->fetchArray()) {
					if($row['login'] == $_POST['login'] && $row['password'] == sha1($_POST['password'])){
						$ifPassOk = true;
					}
				}

				if($ifPassOk){
					$_SESSION['name'] = $_POST['login'];
					$_SESSION['password'] = $_POST['password'];

					header('Location: login_after.php');
				} else {
					echo '<script>alert("Špatné jméno nebo heslo!");</script>';
				}
			}
			?>
		</form>
		
		</div>
	</div>
	</div>
	<hr />
	<div style="padding-top: 20px" class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
				<div class="panel panel-default">
					<div class="panel-body">
						<h2>Tabulka modulů</h2>
						<table class="table">
						  <thead class="thead-inverse">
							<tr>
							  <th>Název modulu</th>
							  <th>Poslední aktualizace</th>
							  <th>Stav modulu</th>
							  <th>Teplota udávaná modulem</th>
							  <th>Graf vývoje teploty</th>
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
							//echo $currentTime;

						  	while ($row = $results->fetchArray()) {
						  		while ($rowCheck = $result_hash->fetchArray()) {
						  			if($rowCheck['hash'] == $row['hash']){
						  				$insertNewGraph = $db->exec('INSERT INTO graph_modules (hash, temp) VALUES ("'. $row['hash'] .'", "'. $row['temp'] .'")');

						  				echo '<tr>';
						  				echo '<th style="font-size: 17px;">' . $row['nazev'] . '</th>';
						  				echo '<td>' . gmdate("d. m. Y H:i:s", $row['last']) . '</td>';

						  				if($currentTime - $row['last'] < 360){
											// online
						  					echo '<td style="color: green;">Online</td>';
						  				} else {
						  					echo '<td style="color: red;">Offline</td>';
						  				}

						  				echo '<td>' . $row['temp'] . '&deg;</td>';
						  				echo '<td><a href="graph.php?hash='. $row['hash'] .'" target="_blank">Otevřít</a>';

						  				if($row['is_main'] == 1){
						  					echo '<td> <img src="https://cdn3.iconfinder.com/data/icons/musthave/128/Check.png" style="width: 20px; height: 20px;" /> </td>';

						  					file_get_contents('https://ioe.zcu.cz/esp.php?id=HASHPROCISCO&temperature='. $row['temp'] .' ');
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
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="copyright"><hr />Vojtěch Kožuch, David Pavelka, Dan Krůl</div>
</body>
</html>