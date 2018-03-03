<?php
	include_once 'lib.php';

	session_start();
	if (! isset($_SESSION['username'])) {
		header("Location: index.php");
	}
	$enableJTextField = "";
	if (isset($_GET['create'])) {
		$enableJTextField = "enabled";
	}

	$err = "";
	$query = "";
	if (isset($_POST['message']) && $_POST['message']!="") {
		try {
			$conn = Database::getPDO();
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$query = "insert into messages (sender, recipient, msg, time) values ('".$_SESSION['username']."','".$_GET['sender']."','".$_POST['message']."',now());";
			$conn->exec($query);
			$conn = null;
		} catch (Exception $ex) {
			$err = "Could not send message.";
		}
	}

	$messages = "";
	
	if (isset($_GET['sender'])) {
		$info = null;
		try {
			$conn = Database::getPDO();
			$query = "select * from messages where (sender='".$_SESSION['username']."' or recipient='".$_SESSION['username']."') and (sender='".$_GET['sender']."' or recipient='".$_GET['sender']."') order by time;";
			$info = $conn->query($query);
			$conn = null;
			$info = $info->fetchAll(PDO::FETCH_ASSOC);	
		} catch (Exception $ex) {
			# this avoids displaying unnecessary information
			
		}


		if ($info) {
			foreach ($info as $key) {
				// messages sent by sender (the user logged in) are colored in blue whereas the sent back by the recipient in grey.
				if ($key['recipient'] == $_SESSION['username']) { 
					$messages .= '<div class="row"><div class="col-lg-12 flex-right"><i class="ion-chevron-left"></i><p class="msg by-sender">'.$key['msg'].'</p></div></div>';
				} else {
					$messages .= '<div class="row"><div class="col-lg-12 flex-left"><p class="msg by-recipient">'.$key['msg'].'</p><i class="ion-chevron-right"></i></div></div>';
				}
			}
		} 
	}


?>

<!DOCTYPE html>
<html>
<head>
	<title>Dashboard</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
	<div class="container">
		<p>Welcome <?php echo $_SESSION['username']; ?> (<a href="logout.php">logout</a>)</p>
		<div class="row">
			<div class="col-lg-3 people">
				<div id="search" class="form-group">
					<input type="text" id="search-user" class="form-control" placeholder="Type user">
					<i title="new message" class="ion-person-add" id="new-conver"></i>
				</div>
				<div id="suggestion" class="display-none">
					<a href="#" class="sender-msg">Juani</a>
				</div>
				<div id="contacts">
					<?php
						$info = null;
						$info2 = null;
						try {
							$conn = Database::getPDO();
							$query = "select sender from messages where recipient='".$_SESSION['username']."' group by sender;";
							$info = $conn->query($query);
							$query = "select recipient from messages where sender='".$_SESSION['username']."' group by recipient;";
							$info2 = $conn->query($query);
							$conn = null;
							$info2 = $info2->fetchAll(PDO::FETCH_ASSOC);	
						} catch (Exception $ex) {
							# this avoids displaying unnecessary information
						}

						$userss = "";
						if ($info) {
							foreach ($info as $key) {
								echo '<a class="sender-msg" href="dashboard.php?sender='.$key['sender'].'">'.$key['sender'].'</a>';
								$userss .= $key['sender']."--";
							}
						} 
						if ($info2) {
							foreach ($info2 as $key) {
								if (strpos($userss, $key['recipient']) !== false) {
									continue;
								}
								echo '<a class="sender-msg" href="dashboard.php?sender='.$key['recipient'].'">'.$key['recipient'].'</a>';
							}
						} 

					?>
				</div>
			</div>
			<div class="col-lg-9 message-area">
				<div id="show-text">
					<?php if($messages != ""){ echo $messages;} ?>
				</div>
				<?php if($messages != ""): ?>
					<form action="dashboard.php?sender=<?php echo $_GET['sender'] ?>" method="POST">
						<input class="msg-box" type="text" name="message" placeholder="<?php echo 'Write a message to '.$_GET["sender"].''; ?>"><br>
						<input class="btn btn-primary" type="submit" value="Send">
					</form> 
				<?php endif; ?>
				<?php if($enableJTextField != ""): ?>
					<form action="dashboard.php?sender=<?php echo $_GET['create'] ?>" method="POST">
						<input class="msg-box" type="text" name="message" placeholder="<?php echo 'Write a message to '.$_GET["create"].''; ?>"><br>
						<input class="btn btn-primary" type="submit" value="Send">
					</form> 
				<?php endif; ?>
				<?php if ($err != ""): ?>
					<div class="alert alert-danger">
						<strong><i class="ion-alert-circled"></i></strong> <?php echo $err; ?>
					</div>
		      	<?php endif; ?>
			</div>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

	<script src="assets/js/lib.js"></script>
</body>
</html>