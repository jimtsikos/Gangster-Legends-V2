<?php

    class profileTemplate extends template {
		
        public $loginPage = false; // Ture means you can access this page without being logged in
        public $jailPage = true; // True means you can view this page in prison
        
        public $blankElement = '{var1} {var2} {var3}';
		
		public $online = "<strong class=\"text-success\">Online</strong>";
		public $offline = "<strong class=\"text-danger\">Offline</strong>";
		public $AFK = "<strong class=\"text-warning\">AFK</strong>";
		
		public $editProfile = '
			<form action="#" method="post">
				<div class="row">
					<div class="col-md-3 text-right">
						<strong>Bio</strong>
					</div>
					<div class="col-md-9">
						<textarea rows="5" name="bio" class="form-control" placeholder="A little bio about yourself ...">{var1}</textarea>
					</div>
				</div><br />
				<div class="row">
					<div class="col-md-3 text-right">
						<strong>Picture</strong>
					</div>
					<div class="col-md-9">
						<input type="text" name="pic" class="form-control" value="{var2}" placeholder="e.g. http://www.someurl.com/picture.png" />
					</div>
				</div><br />
				<div class="row">
					<div class="col-md-12">
						<button type="submit" name="submit" value="true" class="btn pull-right">Update</button>
					</div>
				</div>
			</form>';
		
		public $edit = '
			<div class="row">
				<div class="col-md-12">
					<a href="?page=profile&action=edit" class="btn pull-right">Edit Profile</a>
				</div>
			</div>';
		
		public $profile = '
			<div class="row">
				<div class="col-md-4 col-lg-3">
					<img src="{var1}" class="img-rounded img-thumbnail" alt="{var2}\'s Profile" />
				</div>
				<div class="col-md-8 col-lg-9">
					<table class="table table-borderless table-condensed">
						<tr>
							<th width="100px">Username</th>
							<td class="text-left">{var2}</td>
						</tr>
						<tr>
							<th width="100px">Rank</th>
							<td class="text-left">{var3}</td>
						</tr>
						<tr>
							<th width="100px">Family</th>
							<td class="text-left">{var4}</td>
						</tr>
						<tr>
							<th width="100px">Status</th>
							<td class="text-left">{var5}</td>
						</tr>
						<tr>
							<th width="100px">Bio</th>
							<td class="text-left">{var6}</td>
						</tr>
					</table>
				</div>
			</div>
		';
        
    }

?>