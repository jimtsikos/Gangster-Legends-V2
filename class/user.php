<?php

	class user {
		
		public $id, $info, $name, $db, $loggedin = false;
		
		// Pass the ID to the class
		function __construct($id = FALSE, $name = FALSE) {

            global $db;
			
			$this->db = $db;
			
			if (isset($id) || isset($name)) {
				$this->id = $id;
				$this->name = $name;
				$this->getInfo();	
				
				if ((is_array($this->info) && isset($_SESSION['userID'])) && $this->info->U_id == $_SESSION['userID']) {
					while ($this->checkRank()){}
				}
			}

            if ($_SESSION['userID'] == $this->id) {
                $this->loggedin = true;
            }

		}	
		
		// This function will return all the information for the user
		public function getInfo($return = false) {
			
			if (!empty($this->name)) {
				$userInfo = $this->db->prepare("SELECT * FROM users LEFT OUTER JOIN userStats ON (U_id = US_id) WHERE U_name = :userName");
				$userInfo->bindParam(':userName', $this->name);
			} else {
				$userInfo = $this->db->prepare("SELECT * FROM users LEFT OUTER JOIN userStats ON (U_id = US_id) WHERE U_id = :userID");
				$userInfo->bindParam(':userID', $this->id);
			}
			
			$userInfo->execute();
            
			$this->info = $userInfo->fetchObject();
            
            if (isset($user->info->U_name) || isset($user->info->U_id)) {
                $this->id = $this->info->U_id;
                $this->name = $this->info->U_name;
            }
			
            if ($return) {
				return $this->info;
			}
			
		}
		
		public function encrypt($var) {
			
			return sha1($var);
				
		}
		
		public function makeUser($username, $email, $password, $userLevel = 1, $userStatus = 1) {
			
			$check = $this->db->prepare("SELECT U_id FROM users WHERE U_name = :username OR (U_email = :email AND U_status = 1)");
			$check->bindParam(':username', $username);	
			$check->bindParam(':email', $email);	
			$check->execute();
			$checkInfo = $check->fetchObject();
			
			if (isset($checkInfo->U_id)) {
				
				return 'Username or EMail are in use!';
				
			} else {
				
				$addUser = $this->db->prepare("INSERT INTO users (U_name, U_email, U_password, U_userLevel, U_status) 
							VALUES (:username, :email, :password, :userLevel, :userStatus)");
				$addUser->bindParam(':username', $username);
				$addUser->bindParam(':email', $email);
				$addUser->bindParam(':password', $this->encrypt($password));
				$addUser->bindParam(':userLevel', $userLevel);
				$addUser->bindParam(':userStatus', $userStatus);
				$addUser->execute();
				
				$this->db->query("INSERT INTO userStats (US_id) VALUES (".$this->db->lastInsertId().")");
				
				return 'success';
				
			}
			
		}
		
		public function getNotificationCount($id, $type = 'all') {
				
			global $page;
			
			if ($type == 'all') {
				
				$notifications = $this->db->prepare("SELECT COUNT(M_id)+(SELECT COUNT(N_id) FROM notifications WHERE N_uid = :user1 AND N_read = 0) as count FROM mail WHERE M_uid = :user2 AND M_read = 0");
				$notifications->bindParam(':user1', $id);
				$notifications->bindParam(':user2', $id);
				$notifications->execute();
				$result = $notifications->fetchObject();
				
				if ($result->count == 0) {
					$page->addToTemplate('all_notifications', ' '); 
				} else {
					$page->addToTemplate('all_notifications', ' ('.$result->count.')'); 
				}
				return $result->count;
				
			} else if ($type == 'mail') {
				
				$notifications = $this->db->prepare("SELECT COUNT(M_id) as count FROM mail WHERE M_uid = :user1 AND M_read = 0");
				$notifications->bindParam(':user1', $id);
				$notifications->execute();
				$result = $notifications->fetchObject();
				
				if ($result->count == 0) {
					$page->addToTemplate('mail', ' '); 
					$page->addToTemplate('mail_class', ' '); 
				} else {
					$page->addToTemplate('mail', ' ('.$result->count.')'); 
					$page->addToTemplate('mail_class', 'new'); 
				}
				
				return $result->count;
				
			} else if ($type == 'notifications') {
				
				$notifications = $this->db->prepare("SELECT COUNT(N_id) as count FROM notifications WHERE N_uid = :user1 AND N_read = 0");
				$notifications->bindParam(':user1', $id);
				$notifications->execute();
				$result = $notifications->fetchObject();
				
				if ($result->count == 0) {
					$page->addToTemplate('notifications', ' '); 
					$page->addToTemplate('notifications_class', ' '); 
				} else {
					$page->addToTemplate('notifications', ' ('.$result->count.')'); 
					$page->addToTemplate('notifications_class', 'new'); 
				}
				
				return $result->count;
				
			}
		}
		
		public function bindVarsToTemplate() {
			
			global $page;
			$this->getNotificationCount($this->info->U_id); 
			$this->getNotificationCount($this->info->U_id, 'mail'); 
			$this->getNotificationCount($this->info->U_id, 'notifications'); 
			$page->addToTemplate('money', '$'.number_format($this->info->US_money));
			$page->addToTemplate('bullets', number_format($this->info->US_bullets));
			$page->addToTemplate('backfire', number_format($this->info->US_backfire));
			$page->addToTemplate('credits', $this->info->US_credits);
			$page->addToTemplate('health', $this->info->US_health.'%');
			$page->addToTemplate('location', $this->getLocation());
			$page->addToTemplate('username', $this->info->U_name);

            if ($this->info->U_userLevel > 1) {
                $adminLink = '<a href="?page=admin">Admin</a><br />';
            } else {
                $adminLink = "";
            }
			$page->addToTemplate('adminLink', $adminLink);
			
			if (($this->getTimer("crime")-time()) > 0) {
				$page->addToTemplate('crime_timer', ($this->getTimer("crime")-time()));
			} else {
				$page->addToTemplate('crime_timer', '0');
			}
			
			if (($this->getTimer("theft")-time()) > 0) {
				$page->addToTemplate('theft_timer', ($this->getTimer("theft")-time()));
			} else {
				$page->addToTemplate('theft_timer', '0');
			}
			
			if (($this->getTimer("chase")-time()) > 0) {
				$page->addToTemplate('chase_timer', ($this->getTimer("chase")-time()));
			} else {
				$page->addToTemplate('chase_timer', '0');
			}
			
			if (($this->getTimer("jail")-time()) > 0) {
				$page->addToTemplate('jail_timer', ($this->getTimer("jail")-time()));
			} else {
				$page->addToTemplate('jail_timer', '0');
			}
			
			if (($this->getTimer("bullets")-time()) > 0) {
				$page->addToTemplate('bullet_timer', ($this->getTimer("bullets")-time()));
			} else {
				$page->addToTemplate('bullet_timer', '0');
			}
			
			if (($this->getTimer("travel")-time()) > 0) {
				$page->addToTemplate('travel_timer', ($this->getTimer("travel")-time()));
			} else {
				$page->addToTemplate('travel_timer', '0');
			}
			
			$rank = $this->getRank();
			$gang = $this->getGang();
			$weapon = $this->getWeapon();
			
			$expperc = round(
				( 
					(
						@$this->info->US_exp/$rank->R_exp 
					)*100 
				)
			, 2);
			
			$page->addToTemplate('rank', $rank->R_name);
			@$page->addToTemplate('exp_perc', '('.$expperc.'%)');
			$page->addToTemplate('gang', $gang->G_name);
			$page->addToTemplate('weapon', $weapon->W_name);
			
		}
		
		public function getRank() {
			
			$query = $this->db->prepare("SELECT * FROM ranks WHERE R_id = :rank");
			$query->bindParam(":rank", $this->info->US_rank);
			$query->execute();
			$result = $query->fetchObject();
			
			return $result;
			
		}
		
		public function getGang() {
			
			$query = $this->db->prepare("SELECT * FROM gangs WHERE G_id = :gang");
			$query->bindParam(":gang", $this->info->US_gang);
			$query->execute();
			$result = $query->fetchObject();
			
			return $result;
			
		}
		
		public function getWeapon() {
			
			$query = $this->db->prepare("SELECT * FROM weapons WHERE W_id = :weapon");
			$query->bindParam(":weapon", $this->info->US_weapon);
			$query->execute();
			$result = $query->fetchObject();
			
			return $result;
			
		}
		
		public function checkRank() {

            if ($this->loggedin) {
			
                $rank = $this->getRank();

                if ($rank->R_exp < $this->info->US_exp || $rank->R_exp == $this->info->US_exp) {

                    $this->db->query("UPDATE userStats SET US_money = US_money + ".$rank->R_cashReward.", US_bullets = US_bullets + ".$rank->R_bulletReward.", US_rank = US_rank + 1, US_exp = ".($this->info->US_exp - $rank->R_exp)." WHERE US_id = ".$this->info->US_id);

                    $this->info->US_exp = ($this->info->US_exp - $rank->R_exp);
                    $this->info->US_rank++;
                    $this->info->US_bullets = $this->info->US_bullets + $rank->R_bulletReward;
                    $this->info->US_money = $this->info->US_money + $rank->R_cashReward;

                    return true;

                } else {

                    return false;

                }

            }
		
		}
        
        public function getLocation() {
            
            $location = $this->db->prepare("SELECT L_name FROM locations WHERE L_id = :location");
            $location->bindParam(':location', $this->info->US_location);
            $location->execute();
			
            $return = $location->fetch(PDO::FETCH_ASSOC);
            
            return $return['L_name'];
        }
		
		public function checkTimer($timer) {
		
			$time = $this->getTimer($timer);
			
			if (time() > $time) {
				return true;
			} else {
				return false;
			}
			
		}
		
		public function getTimer($timer) {
		
			$userID = $this->id;
			
			$select = $this->db->prepare("SELECT * FROM userTimer WHERE UT_desc = :desc AND UT_user = :user");
			$select->bindParam(':user', $userID);
			$select->bindParam(':desc', $timer);
			$select->execute();
			
			$array = $select->fetch(PDO::FETCH_ASSOC);
			
			// If the array is empty we make the user timer, this way the developer does not have to make any changes to the database to make a new timer.
			if (empty($array['UT_time'])) {
				
				$time = time()-1;
				$insert = $this->db->prepare("INSERT INTO userTimer (UT_user, UT_desc, UT_time) VALUES (:user, :desc, :time)");
				$insert->bindParam(':user', $userID);
				$insert->bindParam(':desc', $timer);
				$insert->bindParam(':time', $time);
				$insert->execute();
				return $time;
				
			} else {
				
				return $array['UT_time'];
				
			}
			
		}
		
		public function updateTimer($timer, $time, $add = false) {
		
			$user = $this->id;
			
			// Check that the timer exists, if it dosent this function will automaticly make it.
			// We do this so the user does not have to make any database changes to make a module.
			$this->getTimer($timer);
			
			if ($add) {
				$time = time() + $time;
			}
			
			$update = $this->db->prepare("UPDATE userTimer SET UT_time = :time WHERE UT_user = :user AND UT_desc = :desc");
			$update->bindParam(':time', $time);
			$update->bindParam(':user', $user);
			$update->bindParam(':desc', $timer);
			$update->execute();
			
		}
		
		public function getStatus() {
			
			$time =(time() - $this->getTimer("laston"));
			global $page;
			
			if ($time > 300 && $time <= 900) {
				return $page->buildElement("AFK", array());
			} else if ($time > 900) {
				return $page->buildElement("offline", array());
			} else {
				return $page->buildElement("online", array());
			}
			
		}
        
        public function logout() {
        
            session_destroy();
            
        }
		
	}
	
?>
