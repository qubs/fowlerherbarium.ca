<?php
	require_once('../config.php');
	require_once('../shared.php');

	require_once('./includes/PasswordHash.php');

	session_start();

	if(isset($_SESSION['user'])) {
		header('Location: main.php');
	}

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(isset($_POST['submit'])) {
		$hasher = new PasswordHash($_HASH_COST, $_HASH_PORTABLE);

		$username = $mysqli->real_escape_string($_POST['username']);
		$password = $_POST['password'];
		// echo $hasher->HashPassword($password);

		$fail = false;
		$error = "";

		$fetch_user = $mysqli->query("SELECT * FROM users WHERE username = '$username'");
		if($fetch_user->num_rows == 0) {
			$fail = true;
			$error = "No user with that username exists.";
		} else {
			$user = $fetch_user->fetch_assoc();

			if(!$hasher->CheckPassword($password, $user['password'])) {
				$fail = true;
				$error = "The password entered is incorrect.";
			}
		}

		if(!$fail) {
			$_SESSION['user'] = $username;

			header('Location: main.php');
		}
	}

	$page = "login";
	require_once('./header.php');
?>
<form action="index.php" method="post">
	<?php if($fail) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<h2>Log In</h2>
	<label for="username">Username</label>
	<input type="text" name="username">
	<label for="password">Password</label>
	<input type="password" name="password">
	<br>
	<button class="button" type="submit" name="submit">Log In</button>
</form>
<?php
	require_once('./footer.php');
?>
