<?php

class Database {

	private static $pdo = null;

	public static function getPDO() {
		try {
			if (Database::$pdo == null) {
				Database::$pdo = new PDO("mysql:host=127.0.0.1;dbname=prueba", 'root', '');
			} 
		} catch(Exception $e) {
			Database::$pdo = null;
		}
	}

	/* method fetchs directly all the query inserted by user */
	public static function check($user, $pass) {
		$pass = strtoupper(hash("sha256", $pass));
		$query = "select * from usuarios where username='$user';";
		$info = Database::query($query);

		if ($info) {
			foreach ($info as $key) {
				if ($key['username'] == $user && $key['password'] == $pass) {
					$q = sprintf("update usuarios set last_act=now(), is_online='1' where username='%s'", $user);
					Database::exec($q);
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

	public static function exists($user) {
		$user = htmlspecialchars(mysql_real_escape_string($user));
		$query = "select * from usuarios where username='$user';";
		$info = Database::query($query);

		if ($info) {
			return true;
		} 
		return false;
	}

	public static function query($query) {
		if (Database::$pdo == null) {
			return null;
		}
		$info = Database::$pdo->query($query);
		if ($info) {
			return $info->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return null;
		}
	}

	public static function exec($query) {
		if (Database::$pdo == null) {
			return;
		}
		Database::$pdo->exec($query);
	}
}

?>