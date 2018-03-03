<?php
	include_once 'lib.php';
	session_start();
	if (isset($_GET['name'])) {
		// generate the code here!
		$info = null;
		try {
			$conn = Database::getPDO();
			$query = "select * from usuarios where username like '".$_GET['name']."%';";
			$info = $conn->query($query);
			$conn = null;
			$info = $info->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $ex) {
			# this avoids displaying unnecessary information
		}

		$suggestions = '<div class="sug-users">';
		if ($info) {
			foreach ($info as $key) {
				if ($key['username'] == $_SESSION['username']) {
					continue;
				}
				$suggestions .= '<a href="dashboard.php?create='.$key['username'].'" class="sender-msg">'.$key['username'].'</a>';
			}
		} 
		echo $suggestions."</div>";
	}
?>