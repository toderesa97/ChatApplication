<?php

class Database {

	public static function getPDO() {
		$pdo = null;
		try {
			$pdo = new PDO("mysql:host=127.0.0.1;dbname=prueba", 'root', '');
		} catch(Exception $e) {
			$pdo = null;
		}
		return $pdo;
	}

	/* method fetchs directly all the query inserted by user */
	public static function check($user, $pass) {
		$pass = strtoupper(hash("sha256", $pass));

		$info = null;
		try {
			$conn = new PDO("mysql:host=127.0.0.1;dbname=prueba", 'root', '');
			$query = "select * from usuarios where username='$user';";
			$info = $conn->query($query);
			$conn = null;
			$info = $info->fetchAll(PDO::FETCH_ASSOC);	
		} catch (Exception $ex) {
			# this avoids displaying unnecessary information
		}


		if ($info) {
			foreach ($info as $key) {
				if ($key['username'] == $user && $key['password'] == $pass) {
					return true;
				} else {
					sleep(1);
				}
			}
		} else {
			sleep(0.5);
		}
		return false;
	}	
}

?>