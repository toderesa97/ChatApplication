<?php
	session_start();
	if (! isset($_SESSION['username'])) {
		header("Location: index.php");
	} else {
		unset($_SESSION['username']);
		header("Location: index.php");
	}
?>