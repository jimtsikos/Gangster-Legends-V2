<?php

	class settings {

		private $page, $user, $db, $debug = false;
		
		public function __construct() {
			
			global $page, $user, $db;
			
			$this->db = $db;
			$this->user = $user;
			$this->page = $page;
		
			$page->addToTemplate('game_name', $this->loadSetting('game_name', true, 'Game Name'));
			$page->theme = $this->loadSetting('theme', true, 'default');
			
		}
		
		public function loadSetting($setting, $makeIfNotExist = true, $value = 'Not Set') {
		
			$select = $this->db->prepare("SELECT * FROM settings WHERE S_desc = :desc");
			$select->bindParam(":desc", $setting);
			$select->execute();
			$row = $select->fetch(PDO::FETCH_ASSOC);
			
			if (empty($row['S_desc'])) {
				
				if ($makeIfNotExist) {
				
					if (is_array($value)) {

						$value = json_encode($value);

					}
					$this->makeSetting($setting, $value);
				
					return $value;
					
				} else {
					
					return false;
					
				}
				
			} else {
				
				$value = @json_decode($row['S_value'], true);
				
				if (is_array($value)) {
				
					return $value;
					
				} else {
				
					return $row['S_value'];
				
				}
				
			}
			
		}
		
		public function makeSetting ($setting, $value) {
		
			if(!$this->loadSetting($setting, false)) {
				
				if (is_array($value)) {
				
					$value = json_encode($value);
					
				}
			
				$delete = $this->db->prepare("DELETE FROM settings WHERE S_desc = :desc");
				$delete->bindParam(":desc", $setting);
				$delete->execute();
				
				$insert = $this->db->prepare("INSERT INTO settings (S_desc, S_value) VALUES (:desc, :value)");
				$insert->bindParam(":desc", $setting);
				$insert->bindParam(":value", $value);
				$insert->execute();
				
			}
			
		}
		
		public function updateSetting($setting, $value) {
				
			if (is_array($value)) {

				$value = json_encode($value);

			}
			
			$this->loadSetting($setting);
			
			$update = $this->db->prepare("UPDATE settings SET S_value = :value WHERE S_desc = :desc");
			$update->bindParam(":value", $value);
			$update->bindParam(":desc", $setting);
			$update->ececute();
		
		}
		
	}

?>