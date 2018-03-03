<?php
	include_once 'lib.php';

	session_start();
	
	Database::getPDO();

	if (isset($_SESSION['username'])) {
		header("Location: dashboard.php");
	}
	$msg = "";
	$username = "";
	$pass = "";
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = htmlspecialchars($_POST['username']);
		$pass = htmlspecialchars($_POST['password']);
		$logged = Database::check($username, $pass);
		if ($logged) {
			$_SESSION['username'] = $_POST['username'];
			header("Location: dashboard.php");
		} else {
			$msg = "Could not verify your identity";
		}
	}
	
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-lg-3"></div>
			<div class="col-lg-6">
				<form class="login_s" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
					<p>Insert your credentials to access site</p>
					<div class="form-group">
						<label for="usr">Username:</label>
						<input type="text" class="form-control" id="usr" name="username">
					</div>
					<div class="form-group">
						<label for="pwd">Password:</label>
						<input type="password" class="form-control" id="pwd" name="password">
					</div>
					<button type="submit" name="submit" class="btn btn-primary">Go!</button>
		      		<?php if ($msg != ""): ?>
						<div class="alert alert-danger">
							<strong><i class="ion-alert-circled"></i></strong> <?php echo $msg; ?>
						</div>
		      		<?php endif; ?>
		      		</div>  				
				
				</form>
			</div>
			<div class="col-lg-3"></div>
		</div>
		
	</div>

	
</body>
</html>