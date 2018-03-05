<?php
	include_once 'lib.php';
	session_start();
	if (! isset($_SESSION['username'])) {
		header('Location: index.php');
	}
	if (isset($_GET['name'])) {
		$conn = Database::getPDO();
		$query = sprintf("select username from usuarios where username like '%s%%';", htmlspecialchars(mysql_real_escape_string($_GET['name'])));
		$info = Database::query($query);

		$suggestions = '<div class="sug-users">';
		if ($info) {
			foreach ($info as $key) {
				if ($key['username'] == $_SESSION['username']) {
					continue;
				}
				$u = $key['username'];
				$suggestions .= sprintf('<a href="dashboard.php?create=%s" class="sender-msg">%s</a>',$u, $u);
			}
		} 
		echo $suggestions."</div>";
	}
?>