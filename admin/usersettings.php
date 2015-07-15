<?php
	require_once('../config.php');
	require_once('../shared.php');

	session_start();

	if(!isset($_SESSION['user'])) {
		header('Location: index.php');
	}

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(isset($_POST['submitChangePassword'])) {
		
	}

	$fetch_user = $mysqli->query("SELECT * FROM users WHERE username = '" . $mysqli->real_escape_string($_SESSION['user']) . "'");
	$user_object = $fetch_user->fetch_assoc();

	$page = 'usersettings';
	require_once('./header.php');
?>
<h2>Settings and Options for <?=$user_object['username']?></h2>
<div class="box">
	<h3>Change Password</h3>
	<form action="usersettings.php" method="post">
		<label for="oldpass">Old Password</label>
		<input type="password" name="oldpass">
		<label for="newpass">New Password</label>
		<input type="password" name="newpass">
		<label for="newpassagain">New Password Again</label>
		<input type="password" name="newpassagain">
		<br><button type="submit" name="submitChangePassword">Change Password</button>
	</form>
</div>
<div class="box">
	<h3>Update Email</h3>
	<form action="usersettings.php" method="post">
		<label for="email">Email</label>
		<input type="email" name="email" value="<?=$user_object['email']?>">
		<br>
		<button type="submit" name="submitUpdateEmail">Update Email</button>
	</form>
</div>
<?php
	require_once('./footer.php');
?>