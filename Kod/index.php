<?php
	session_start();
	$_SESSION["accessToken"] = md5(uniqid(mt_rand(), true));
?>
<!DOCTYPE html>
<html lang="sv" >
	<head>
	    <meta charset="UTF-8" />
		<title>FoodGen</title>
		<meta name="viewport" content="user-scalable=yes,width=device-width,initial-scale=0.5" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<link href='http://fonts.googleapis.com/css?family=Dosis:300,400' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Zeyada' rel='stylesheet' type='text/css'>
		<link href="css/bootstrap/bootstrap.min.css" type="text/css" rel="stylesheet" />
		<link href="css/stylesheet.css" type="text/css" rel="stylesheet" />
	</head>
	<body>
		<input type="hidden" id="accessToken" value="<?php echo $_SESSION["accessToken"]; ?>"/>
		<div id="fb-root"> </div>
		<div id="container" class="container">
			<div id="content"> </div>
			<div id="footer"><p>Skapad av Janina Bergström 2014</p></div>
			<div class="navbar navbar-default navbar-fixed-top">
				<div class="navbar-header">
					<a href="#" id="brand" class="navbar-brand">FoodGen</a>
				</div>
				<div class="navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><input type="button" value="Slumpa recept" class="btn" id="random-button" /></li>
						<li>
							<div id="fb"><div class="fb-login-button" data-width="250" data-max-rows="1" data-show-faces="true"> </div></div>
							<a href="#" id="profile-button">Visa profil</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="js/jquery-min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/init.js"></script>
		
	</body>
</html>
