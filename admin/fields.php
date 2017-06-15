<?php
	require_once("../config.php");
	require_once("../shared.php");

	session_start();

	if(!isset($_SESSION["user"])) {
		header("Location: index.php");
	}

	$mysqli = mysqli_connect($__DATABASE["host"], $__DATABASE["user"], $__DATABASE["password"], $__DATABASE["name"]);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(isset($_POST["submit"])) {
		// $username = $_POST['username'];
		// $email = $_POST['email'];
		// $password = $_POST['password'];
		// $passagain = $_POST['passagain'];

		// $dead = false;
		// $error = "";

		// if($password != $passagain) {
		// 	$dead = true;
		// 	$error = "Entered passwords do not match.";
		// }
		// if(
		// 	(!isset($username) || !isset($email) || !isset($password) || !isset($passagain))
		//  || ($username == "" || $email == "" || $password == "" || $passagain == "")) {
		// 	$dead = true;
		// 	$error = "All fields must be filled in.";
		// }

		// if(!$dead) {
		// 	$hasher = new PasswordHash($_HASH_COST, $_HASH_PORTABLE);
		// 	$passHash = $hasher->HashPassword($password);

		// 	$insert_user = $mysqli->prepare("INSERT INTO users(username, email, password) VALUES(?, ?, ?)");
		// 	$insert_user->bind_param("sss", $username, $email, $password);
		// 	$insert_user->execute();
		// 	$insert_user->close();
		// }
	}

	$fetch_all_fields = $mysqli->query("SELECT * FROM fields");

	$page = "fields";
	require_once("./header.php");
?>
<h2>Fields</h2>
<div class="box">
	<?php if(isset($error) && !empty($error)) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<h3>Add New</h3>
	<form action="fields.php" method="post">
		<div class="row">
			<div class="col col50">
				<strong>Coming Soon</strong>
				<!-- <button type="submit" name="submit"><span class="icon-control-point"></span> Add Field</button> -->
			</div><div class="col col50">
			</div>
		</div>
	</form>
</div>
<div class="box">
	<h3>Existing Fields</h3>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Field</th>
				<th>Label</th>
				<th>Help Text</th>
				<th>Searchable</th>
				<th>Options</th>
			</tr>
		</thead>
		<tbody>
			<?php while($field = $fetch_all_fields->fetch_assoc()) { ?>
				<tr>
					<td><?=$field["id"]?></td>
					<td><?=$field["field"]?></td>
					<td style="min-width: 160px;"><?=$field["label"]?></td>
					<td><p><?=$field["help"]?></p></td>
					<td><input type="checkbox" onclick="return false" <?php if($field["searchable"] == 1) { echo "checked"; } ?>></td>
					<td style="min-width: 160px;">
						<a class="button" href="edit_field.php?id=<?php echo $field["id"]; ?>"><span class="icon-mode-edit"></span> Edit</a>
						<button class="button"><span class="icon-highlight-remove"></span> Delete</button>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php require_once("./footer.php"); ?>
