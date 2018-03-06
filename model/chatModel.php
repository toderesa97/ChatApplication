<?php

include_once 'lib.php';

class ChatManagement {

	public static function delete_chat($contact) {
		
		$e = htmlspecialchars(mysql_real_escape_string($contact));
		$u = $_SESSION['username'];
		$q = sprintf("select * from chats where (part1='%s' and part2='%s') or (part1='%s' and part2='%s');",$e, $u, $u, $e);
		$info = Database::query($q);
		$conver = "";
		if ($info) {
			
			foreach($info as $key) { 
				$conver = $key['conversation']; 
				if ($key['part1'] == $u) {
					$q ="update chats set consent_of_deletion_p1='1' where conversation='$conver';";
					Database::exec($q);
				} else { // $key['part1'] == $e (started by the other part)
					$q = "update chats set consent_of_deletion_p2='1' where conversation='$conver';";
					Database::exec($q);
				}
				
			}
		}
		$info = Database::query("select * from chats where conversation='$conver';");
		if ($info) {
			foreach($info as $key) {		
				if ($key['consent_of_deletion_p2']=='1' && $key['consent_of_deletion_p1']=='1') {
					$q = sprintf("delete from chats where conversation='%s'", $key['conversation']);
					Database::exec($q);
					Database::exec(sprintf("drop table %s", $key['conversation']));
				}
			}
		}
		
	}

	/**
	* This method retrieves the users that are maintaining a chat with the logged user.
	* $active represent a selected user in a conversation.
	* 
	*/
	public static function get_contacts($active) {
		$logged = $_SESSION['username'];
		$q = sprintf("select part1, part2 from chats where (part1='%s' or part2='%s');", $logged, $logged);
		$info = Database::query($q);
		
		if (Database::exists($active)) {

		} else {
			$active = "";
		}
		
		$out = "";
		if ($info) {
			foreach($info as $key) {
				if ($key['part1'] == $logged) {
					if ($key['part2'] == $active) {
						$out .= sprintf('<a class="sender-msg active" href="dashboard.php?sender=%s">%s</a>', $key['part2'], $key['part2']);
					} else {
						$out .= sprintf('<a class="sender-msg" href="dashboard.php?sender=%s">%s</a>', $key['part2'], $key['part2']);
					}
				} else { // $key['part2'] == $logged 
					if ($key['part1'] == $active) {
						$out .= sprintf('<a class="sender-msg active" href="dashboard.php?sender=%s">%s</a>', $key['part1'], $key['part1']);
					} else {
						$out .= sprintf('<a class="sender-msg" href="dashboard.php?sender=%s">%s</a>', $key['part1'], $key['part1']);
					}
				}
			}
			return array(true, $out);
		} else {
			return array(false, "No chats are open.");
		}
	}

	// return $err
	public static function send_message($message, $contact) {
		$s = htmlspecialchars(mysql_real_escape_string($contact));
		$u = $_SESSION['username'];
		$q = sprintf("select * from chats where conversation='con_%s_%s' or conversation='con_%s_%s'", $s, $u, $u, $s);
		$info = Database::query($q);
		$err = "";
		if ($info) {
			// chat exist, hence, insertion
			foreach ($info as $key) {
				if ($key['consent_of_deletion_p2'] == '1' || $key['consent_of_deletion_p1'] == '1') {
					// do nothing.
					$err = "Pending a deletion request.";
				} else {
					$q = "insert into ".$key['conversation']." (sender, recipient, msg, time) values ('".$u."','".$s."','".mysql_real_escape_string(htmlspecialchars($_POST['message']))."',now());";
					Database::exec($q);
				}
				
			}
		} else { // the chat does not exist and must be created.
			$table = sprintf("con_%s_%s", $u, $s);
			$q = "create table $table (sender varchar(60) not null, recipient varchar(60) not null, msg text not null, time timestamp);";
			Database::exec($q);
			$q = "insert into chats (part1, part2, conversation, consent_of_deletion_p1,consent_of_deletion_p2) values ('$u', '$s', '$table', '0', '0');";
			Database::exec($q);
			$q = sprintf("insert into $table (sender, recipient, msg, time) values ('$u','$s','%s',now());", mysql_real_escape_string(htmlspecialchars($_POST['message'])));
			Database::exec($q);
		}
		return $err;
	}

	public static function get_messages_with($contact) {
		$s = mysql_real_escape_string(htmlspecialchars($contact));
		if (! Database::exists($s)) {
			return array(false, "Contact does not exist");
		}
		$u = $_SESSION['username'];
		$q = sprintf("select * from chats where conversation='con_%s_%s' or conversation='con_%s_%s';", $u, $s, $s, $u);
		$info = Database::query($q);
		$table = "";
		$deletion_req = "";
		if ($info) {
			foreach($info as $key) {
				$table = $key['conversation'];
				
				if ($key['consent_of_deletion_p1'] == '1' || $key['consent_of_deletion_p2'] == '1') {
					if ($key['part1'] == $u && $key['consent_of_deletion_p1'] == '1') {
						$deletion_req = "You have requested a deletion.";
					}
					if ($key['part2'] == $u && $key['consent_of_deletion_p2'] == '1') {
						$deletion_req = "You have requested a deletion.";
					}
					if ($key['part1'] == $s && $key['consent_of_deletion_p1'] == '1') {
						$deletion_req = sprintf("%s has requested a deletion.", $key['part1']);
					}
					if ($key['part2'] == $s && $key['consent_of_deletion_p2'] == '1') {
						$deletion_req = sprintf("%s has requested a deletion.", $key['part2']);
					}	
				}
				
			}
		}
		
		$q = "select * from ".$table." order by time;";
		$info = Database::query($q);
		$messages = "";
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
		return empty($deletion_req) ? array($messages, "") : array($messages, $deletion_req);
	}

	public static function cancel_deletion_with($contact) {
		$s = $contact;
		$u = $_SESSION['username'];
		$q = "select * from chats where conversation='con_".$u."_".$s."' or conversation='con_".$s."_".$u."';";
		$info = Database::query($q);
		if ($info) {
			foreach($info as $key) {
				$table = $key['conversation'];
			}
		}

		$q = "select * from chats where conversation='$table';";
		$info = Database::query($q);

		if ($info) {
			foreach($info as $key) {
				if (($key['consent_of_deletion_p1']=='1' && $key['part1']==$u) || ($key['consent_of_deletion_p2']=='1' && $key['part2']==$u)) {
					// the logged user cancels a deletion request.
					$q = "update chats set consent_of_deletion_p1='0',consent_of_deletion_p2='0' where conversation='$table';";
					Database::exec($q);
				}
				if (($key['consent_of_deletion_p1']=='1' && $key['part1']==$s) || ($key['consent_of_deletion_p2']=='1' && $key['part2']==$s)) {
					// the logged user cancels a deletion request.
					$q = "update chats set consent_of_deletion_p1='0',consent_of_deletion_p2='0' where conversation='$table';";
					Database::exec($q);
				}
			}
		}
	}

}


?>