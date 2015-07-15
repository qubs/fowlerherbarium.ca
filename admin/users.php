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

	if(isset($_POST['submit'])) {
		$username = $_POST['username'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$passagain = $_POST['passagain'];

		$dead = false;
	}

	$fetch_all_users = $mysqli->query("SELECT * FROM users");

	$page = 'users';
	require_once('./header.php');
?>
<h2>Users</h2>
<div class="box">
	<h3>Add New</h3>
	<form action="users.php">
		<div class="row">
			<div class="col col50">
				<label for="username" class="required">Username</label>
				<input type="text" name="username" required>
				<label for="email" class="required">Email</label>
				<input type="email" name="email" required>
				<br>
				<button type="submit" name="submit">Create User</button>
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
					<td><?=$user['id']?></td>
					<td><?=$user['username']?></td>
					<td><a href="mailto:<?=$user['email']?>"><?=$user['email']?></a></td>
					<td><button onclick="window.location = 'edit_user.php?id=<?php echo $user['id']; ?>'">Edit</button> <button>Delete</button></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php
	require_once('./footer.php');
?>