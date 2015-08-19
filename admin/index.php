<?php
	require_once("../config.php");
	require_once("../shared.php");

	require_once("./includes/PasswordHash.php");

	session_start();

	if(isset($_SESSION["user"])) {
		header("Location: main.php");
	}

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(isset($_POST['submit'])) {
		$hasher = new PasswordHash($_HASH_COST, $_HASH_PORTABLE);

		$username = $_POST["username"];
		$password = $_POST["password"];

		$fail = false;
		$error = "";

		$fetchUser = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
		$fetchUser->bind_result("s", $username);
		$fetchUser->execute();
		$fetchUserResult = $fetchUser->get_result();

		if($fetchUserResult->num_rows == 0) {
			$fail = true;
			$error = "No user with that username exists.";
		} else {
			$user = $fetchUserResult->fetch_assoc();
			$username = $user["username"];

			if(!$hasher->CheckPassword($password, $user["password"])) {
				$fail = true;
				$error = "The password entered is incorrect.";
			}
		}

		if(!$fail) {
			$_SESSION["user"] = $username;

			header("Location: main.php");
		}
	}

	$page = "login";
	require_once("./header.php");
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
<?php require_once("./footer.php"); ?>
