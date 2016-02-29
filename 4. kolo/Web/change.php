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

$db_file = 'databaze_malinovky.db';
$db = new SQLite3($db_file);

if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$results = $db->query('SELECT * FROM modules');
	$last_name = '';	

	while ($row = $results->fetchArray()) {
		if($_GET['id'] == $row['id']){
			$last_name = $row['nazev'];
		}
	}
}

if(isset($_POST['submit'])){
	$set_new_name = $db->exec('UPDATE modules SET nazev="'. $_POST['new_name'] .'" WHERE id="'. $id .'" ');
	
	if($set_new_name){
		echo '<script>alert("Název byl změněn!");</script>';
		echo "<script>window.close();</script>";
	} else {
		echo '<script>alert("Upsiček, něco se pokazilo...");</script>';
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
		<div class="row" style="padding-top: 20px; padding-bottom: 20px;">
			<div class="col-md-1" style="padding-left: 25px;"><img src="design/images/logo_malinovky.png" width="210" height="80"></div>
			<div class="col-md-1 col-md-offset-10">
				<div class="input-group" style="min-width: 200px">
					<span class="input-group-btn">
						<a href="login_after.php"><button class="btn btn-default" type="submit" name="logout" style="border-radius: 5px; margin-top: 40px;"><span class="glyphicon glyphicon-log-out" aria-hidden="true" style="padding-right: 5px;"></span>Zpět</button></a>
					</span>
				</div>

			</div>
		</div>
	</div>
	<hr />

	<form method="POST">

	<div class="input-group input-group-lg col-md-4 col-md-offset-4" style="padding-bottom: 20px;">
		<span class="input-group-addon" id="sizing-addon1">Nový název modulu</span>
		<input type="text" name="new_name" class="form-control" placeholder="<?php echo $last_name ?>" aria-describedby="sizing-addon1">
	</div>
	<div class="input-group input-group-lg col-md-4 col-md-offset-4" style="padding-top: 20px;">
		<div class="btn-group btn-group-lg" role="group" aria-label="...">
			<button type="submit" name="submit" class="btn btn-default"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="padding-right: 5px;"></span>Uložit</button>
		</div>
	</div>
	</form>
</div>


<div class="copyright"><hr />Vojtěch Kožuch, David Pavelka, Dan Krůl</div>
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>