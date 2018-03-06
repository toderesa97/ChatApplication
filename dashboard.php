<?php
	include_once 'lib.php';
	include_once 'model/chatModel.php';

	Database::getPDO();
	session_start();

	if (! isset($_SESSION['username'])) {
		header("Location: index.php");
	}
	$enableJTextField = "";
	if (isset($_GET['create'])) {
		$enableJTextField = "enabled";
	}

	if (isset($_GET['sender'])) {
		$GLOBALS['sender'] = $_GET['sender'];
	} else {
		$GLOBALS['sender'] = "";
	}

	if(isset($_GET['cancel'])) {
		$GLOBALS['cancel'] = $_GET['cancel'];
	} else {
		$GLOBALS['cancel'] = "";
	}
	
	if (isset($_GET['erase'])) {
		ChatManagement::delete_chat($_GET['erase']);
	}

	$err = "";
	
	if (isset($_POST['message']) &&  !empty($_POST['message']) && Database::exists($GLOBALS['sender'])) {
		$err = ChatManagement::send_message($_POST['message'], $GLOBALS['sender']);
	} else {
		if (isset($_POST['message'])) {
			if (empty($_POST['message'])) {
				$err .= "\nCannot send empty message.";
			}
		}
	}

	$messages = "";
	$deletion_req = "";
	$is_online = "";
	$last_act = "";
	if (! empty($GLOBALS['sender'])) {
		$out = ChatManagement::get_messages_with($GLOBALS['sender']);
		$messages = $out[0];
		$deletion_req = $out[1];
		if (! $messages) {

		} else {
			$is_online = $out[2];
			$last_act = $out[3];
		}
	} 
	
	
	if (! Database::exists($GLOBALS['cancel'])) {
	} else {		
		ChatManagement::cancel_deletion_with($GLOBALS['cancel']);
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
						$contacts = ChatManagement::get_contacts($GLOBALS['sender']);
						if (! empty($contacts[1])) {
							echo $contacts[1];
						}
					?>
				</div>
			</div>
			<div class="col-lg-9 message-area">
				<div id="show-text">
					<?php
						if ($is_online == "0") {
							echo sprintf('<p style="text-align: center;">Last seen %s</p>', $last_act);
						} else if ($is_online == "1") {
							echo '<p style="text-align: center;">Online</p>';
						}
					?>
					<?php if($messages != ""){ echo $messages;} ?>
					
				</div>
				<?php if($messages != ""): ?>
					<form action="dashboard.php?sender=<?php echo $_GET['sender'] ?>" method="POST">
						<?php if ($deletion_req == ""): ?>
							<input class="msg-box" type="text" name="message" placeholder="<?php echo 'Write a message to '.$_GET["sender"].''; ?>"><br>
							<div id="chat-cong">
								<input class="btn btn-primary" type="submit" value="Send">&nbsp;
								
								<input id="delete-btn" type="button" class="btn btn-danger" data-toggle="modal" data-target="exampleModal" title="Delete chat" value="Delete" data-recp="<?php echo $_GET['sender'] ?>">
								
							</div>
						<?php endif; ?>
						<?php if ($deletion_req != ""): ?>
							<input class="msg-box" type="text" name="message" placeholder="<?php echo $deletion_req; ?>" disabled><br>
							<div id="chat-cong">
								<input id="delete-btn" type="button" class="btn btn-danger" data-toggle="modal" data-target="exampleModal" title="Delete chat" value="Delete" data-recp="<?php echo $_GET['sender'] ?>">&nbsp;
								<a href="dashboard.php?cancel=<?php echo $_GET['sender'] ?>"> Cancel request</a>
							</div>
						<?php endif; ?>
						
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

	<!-- Modal -->
	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel"></h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        By clicking Yes it does not mean that you are deleting the conversation. The other part may have to accept the request of deletion.
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">I mistook!</button>
	        
	        <a href="#" id="sub-btn-del"><button type="submit" class="btn btn-warning" >Send request of deletion</button></a>
	        
	      </div>
	    </div>
	  </div>
	</div>
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

	<script src="assets/js/lib.js"></script>
</body>
</html>