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
	
	Database::getPDO(); // this line must be executed to create the PDO instance. Singleton pattern.

	if (isset($_GET['erase'])) {
		// pending
	}

	/* creating and/or sending a message */
	if (isset($_POST['message']) && $_POST['message']!="") {
		/*$query = "insert into messages (sender, recipient, msg, time) values ('".$_SESSION['username']."','".$_GET['sender']."','".$_POST['message']."',now());";*/
		$s = $_GET['sender'];
		$u = $_SESSION['username'];
		$q = "select part1,part2,conversation from chats where conversation='con_".$s."_".$u."' or conversation='con_".$u."_".$s."'";
		$info = Database::query($q);
		if ($info) {
			// chat exist, hence, insertion
			foreach ($info as $key) {
				$q = "insert into ".$key['conversation']." (sender, recipient, msg, time) values ('".$u."','".$s."','".$_POST['message']."',now());";
				Database::exec($q);
				
			}
		} else {
			$table = "con_".$u."_".$s;
			$q = "create table ".$table." (sender varchar(60) not null, recipient varchar(60) not null, msg text not null, time timestamp);";
			Database::exec($q);
			$q = "insert into chats (part1, part2, conversation, consent_of_deletion_p1,consent_of_deletion_p2) values ('".$u."', '".$s."', '".$table."', '0', '0');";
			Database::exec($q);
			$q = "insert into ".$table." (sender, recipient, msg, time) values ('".$u."','".$s."','".$_POST['message']."',now());";
			Database::exec($q);
		}
	}

	/* retrieving messages given two parts */
	$messages = "";
	
	if (isset($_GET['sender'])) {
		$s = $_GET['sender'];
		$u = $_SESSION['username'];
		$q = "select conversation from chats where conversation='con_".$u."_".$s."' or conversation='con_".$s."_".$u."';";
		$info = Database::query($q);
		$table = "";
		if ($info) {
			foreach($info as $key) {
				$table = $key['conversation'];
			}
		}
		$q = "select * from ".$table." order by time;";
		$info = Database::query($q);

		if ($info) {
			foreach ($info as $key) {
				// messages sent by sender (the user logged in) are colored in blue whereas the sent back by the recipient in grey.
				if ($key['recipient'] == $_SESSION['username']) { 
					$messages .= '<div class="row"><div class="col-lg-12 flex-right"><i class="ion-chevron-left"></i><p class="msg by-sender">'.$key['msg'].'</p><p class="msg-time">'.$key['time'].'</p></div></div>';
				} else {
					$messages .= '<div class="row"><div class="col-lg-12 flex-left"><p class="msg-time">'.$key['time'].'</p><p class="msg by-recipient">'.$key['msg'].'</p><i class="ion-chevron-right"></i></div></div>';
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
						$p1 = $_SESSION['username'];
						
						$q = "select part1, part2 from chats where part1='".$p1."' or part2='".$p1."';";
						$info = Database::query($q);

						/* echo '<a class="sender-msg active" href="dashboard.php?sender='.$key['sender'].'">'.$key['sender'].'</a>';*/
						$active="";
						if (isset($_GET['sender'])) {
							$active = $_GET['sender'];
						}
						if ($info) {
							foreach ($info as $key) {
								if ($key['part1'] == $p1) {
									if ($active == $key['part2']) {
										echo '<a class="sender-msg active" href="dashboard.php?sender='.$key['part2'].'">'.$key['part2'].'</a>';
									} else {
										echo '<a class="sender-msg" href="dashboard.php?sender='.$key['part2'].'">'.$key['part2'].'</a>';
									}
								} else {
									if ($active == $key['part1']) {
										echo '<a class="sender-msg active" href="dashboard.php?sender='.$key['part1'].'">'.$key['part1'].'</a>';

									} else {
										echo '<a class="sender-msg" href="dashboard.php?sender='.$key['part1'].'">'.$key['part1'].'</a>';										
									}
								}
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
						<div id="chat-cong">
							<input class="btn btn-primary" type="submit" value="Send">
							<a href="dashboard.php?erase=<?php echo $_GET['sender'] ?>"><i class="ion-android-delete" title="Delete chat"></i></a>
						</div>
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