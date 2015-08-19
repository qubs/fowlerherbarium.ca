<?php
	require_once("../config.php");
	require_once("../shared.php");

	session_start();

	if(!isset($_SESSION["user"])) {
		header("Location: index.php");
	}

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(!isset($_GET["id"]) && !isset($_POST["submit"])) {
		header('Location: main.php');
	} else if(isset($_POST["submit"])) {
		$id = $_POST["id"];
	} else {
		$id = intval($mysqli->real_escape_string($_GET['id']));
	}

	$query = "SELECT * FROM users WHERE id = $id LIMIT 1";
	$fetch_user = $mysqli->query($query);

	if($fetch_user->num_rows == 0) {
		header("Location: main.php");
	}

	$user = $fetch_user->fetch_assoc();

	$page = "edituser";
	require_once("./header.php");
?>
<h2><a href="users.php" class="button" style="float: left; margin-right: 10px; margin-left: 0;">&larr; Back to Users</a> Edit User "<em><?php echo $user['username']; ?>"</em> (ID: <?php echo $user['id']; ?>)</h2>
<form action="edit_user.php" class="box">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<div class="row">
		<div class="col col50">
			<label for="username" class="required" style="margin-top: 0;">Username</label>
			<input type="text" name="username" required value="<?php echo $user["username"]; ?>">
			<label for="email" class="required">Email</label>
			<input type="email" name="email" required value="<?php echo $user["email"]; ?>">
			<br>
			<button class="button" type="submit" name="submit"><span class="icon-done"></span> Save Changes</button> <a class="button" href="users.php"><span class="icon-highlight-remove"></span> Cancel</a>
		</div><!-- <div class="col col50">
			<label for="password" class="required">Password</label>
			<input type="password" name="password" required>
			<label for="passagain" class="required">Password Again</label>
			<input type="password" name="passagain" required>
		</div> -->
	</div>
</form>
<?php require_once("./footer.php"); ?>
