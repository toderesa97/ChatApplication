<?php

class Database {

	private static $pdo = null;
	public static function getPDO() {
		try {
			if (Database::$pdo == null) {
				Database::$pdo = new PDO("mysql:host=127.0.0.1;dbname=prueba", 'root', '');
			} else {
				return Database::$pdo;
			}
		} catch(Exception $e) {
			Database::$pdo = null;
		}
		return Database::$pdo;
	}

	/* method fetchs directly all the query inserted by user */
	public static function check($user, $pass) {
		$pass = strtoupper(hash("sha256", $pass));

		$info = null;
		try {
			$conn = Database::getPDO();
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