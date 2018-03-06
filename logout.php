<?php
	include('lib.php');
	session_start();
	Database::getPDO();
	if (! isset($_SESSION['username'])) {
		header("Location: index.php");
	} else {
		$q = sprintf("update usuarios set last_act=now(), is_online='0' where username='%s'", $_SESSION['username']);
		Database::exec($q);
		unset($_SESSION['username']);
		header("Location: index.php");
	}
?>