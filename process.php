<?php
	if (isset($_GET['name'])) {
		// generate the code here!
		$info = null;
		try {
			$conn = new PDO("mysql:host=127.0.0.1;dbname=prueba", 'root', '');
			$query = "select * from usuarios where username like '".$_GET['name']."%';";
			$info = $conn->query($query);
			$conn = null;
			$info = $info->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $ex) {
			# this avoids displaying unnecessary information
		}

		$suggestions = "";
		if ($info) {
			foreach ($info as $key) {
				$suggestions .= $key['username']."-"; // so far this point the thing is working!
			}
		} 
		echo $suggestions;
	}
?>