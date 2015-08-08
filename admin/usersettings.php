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

	$fetch_user = $mysqli->query("SELECT * FROM users WHERE username = '" . $mysqli->real_escape_string($_SESSION['user']) . "'");
	$user_object = $fetch_user->fetch_assoc();

	if(isset($_POST['submitChangePassword'])) {
		$oldpass = $_POST['oldpass'];
		$newpass = $_POST['newpass'];
		$newpassagain = $_POST['newpassagain'];

		$dead = false;
		$changePasswordError = "";

		if((!isset($oldpass) || !isset($newpass) || !isset($newpassagain)) || ($oldpass == "" || $newpas == "" || $newpassagain == "")) {
			$dead = true;
			$changePasswordError = "All fields must be filled in.";
		}
		if($newpass != $newpassagain) {
			$dead = true;
			$changePasswordError = "The new passwords entered do not match.";
		}

		if(!$dead) {
			$hasher = new PasswordHash($_HASH_COST, $_HASH_PORTABLE);
			$passHash = $hasher->HashPassword($newpass);

			$change_password = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ? LIMIT 1");
			$change_password->bind_param("si", $passHash, $user_object['id']);
			$change_password->execute();
			$change_password->close();
		}
	} else if(isset($_POST['submitUpdateEmail'])) {
		$email = $_POST['email'];

		$dead = false;
		$updateEmailError = "";

		if(!isset($email) || $email == "" || $email == " ") {
			$dead = true;
			$updateEmailError = "All fields must be filled in.";
		}

		$check_existing = $mysqli->query("SELECT * FROM users WHERE email = '" . $mysqli->real_escape_string($email) . "'");
		if($check_existing->num_rows != 0) {
			$dead = true;
			$updateEmailError = "A user with that email already exists.";
		}

		if(!$dead) {
			$change_email = $mysqli->prepare("UPDATE users SET email = ? WHERE id = ? LIMIT 1");
			$change_email->bind_param("si", $email, $user_object['id']);
			$change_email->execute();
			$change_email->close();
		}
	}

	$page = 'usersettings';
	require_once('./header.php');
?>
<h2>Settings and Options for <?=$user_object['username']?></h2>
<div class="box">
	<?php if(isset($changePasswordError) && $changePasswordError != "") { ?>
		<div class="error"><?php echo $changePasswordError; ?></div>
	<?php } ?>
	<h3>Change Password</h3>
	<form action="usersettings.php" method="post">
		<div class="row">
			<div class="col col33 nopad">
				<label for="oldpass">Old Password</label>
				<input type="password" name="oldpass" required>
				<label for="newpass">New Password</label>
				<input type="password" name="newpass" required>
				<label for="newpassagain">New Password Again</label>
				<input type="password" name="newpassagain" required>
				<br><button class="button" type="submit" name="submitChangePassword">Change Password</button>
			</div>
		</div>
	</form>
</div>
<div class="box">
	<?php if(isset($updateEmailError) && $updateEmailError != "") { ?>
		<div class="error"><?php echo $updateEmailError; ?></div>
	<?php } ?>
	<h3>Update Email</h3>
	<form action="usersettings.php" method="post">
		<div class="row">
			<div class="col col33 nopad">
				<label for="email">Email</label>
				<input type="email" name="email" value="<?=$user_object['email']?>" required>
				<br>
				<button class="button" type="submit" name="submitUpdateEmail">Update Email</button>
			</div>
		</div>
	</form>
</div>
<?php
	require_once('./footer.php');
?>