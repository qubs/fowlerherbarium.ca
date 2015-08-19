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

	if(isset($_POST["submit"])) {
		$username = $_POST["username"];
		$email = $_POST["email"];
		$password = $_POST["password"];
		$passagain = $_POST["passagain"];

		$dead = false;
		$error = "";

		if($password != $passagain) {
			$dead = true;
			$error = "Entered passwords do not match.";
		}
		if(empty($username) || empty($email) || empty($password) || empty($passagain)) {
			$dead = true;
			$error = "All fields must be filled in.";
		}

		$check_existing = $mysqli->prepare("SELECT * FROM users WHERE email = ?");
		$check_existing->bind_param("s", $email);
		$check_existing->execute();
		$check_existing->store_result();

		if($check_existing->num_rows > 0) {
			$dead = true;
			$error = "A user with that email already exists.";
		}

		if(!$dead) {
			$hasher = new PasswordHash($_HASH_COST, $_HASH_PORTABLE);
			$passHash = $hasher->HashPassword($password);

			$insert_user = $mysqli->prepare("INSERT INTO users(username, email, password) VALUES(?, ?, ?)");
			$insert_user->bind_param("sss", $username, $email, $password);
			$insert_user->execute();
			$insert_user->close();
		}
	}

	$fetch_all_users = $mysqli->query("SELECT * FROM users");

	$page = "users";
	require_once('./header.php');
?>
<h2>Users</h2>
<div class="box">
	<?php if(isset($error) && $error != "") { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<h3>Add New</h3>
	<form action="users.php" method="post">
		<div class="row">
			<div class="col col50">
				<label for="username" class="required">Username</label>
				<input type="text" name="username" required>
				<label for="email" class="required">Email</label>
				<input type="email" name="email" required>
				<br>
				<button class="button" type="submit" name="submit"><span class="icon-control-point"></span> Create User</button>
			</div><div class="col col50">
				<label for="password" class="required">Password</label>
				<input type="password" name="password" required>
				<label for="passagain" class="required">Password Again</label>
				<input type="password" name="passagain" required>
			</div>
		</div>
	</form>
</div>
<div class="box">
	<h3>Existing Users</h3>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Username</th>
				<th>Email</th>
				<th>Options</th>
			</tr>
		</thead>
		<tbody>
			<?php while($user = $fetch_all_users->fetch_assoc()) { ?>
				<tr>
					<td><?=$user["id"]?></td>
					<td><?=$user["username"]?></td>
					<td><a href="mailto:<?=$user["email"]?>"><?=$user["email"]?></a></td>
					<td><a class="button" href="edit_user.php?id=<?php echo $user["id"]; ?>"><span class="icon-mode-edit"></span> Edit</a> <button class="button"><span class="icon-highlight-remove"></span> Delete</button></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php require_once("./footer.php"); ?>
