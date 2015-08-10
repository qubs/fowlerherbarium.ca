<?php
	require_once("../config.php");
	require_once("../shared.php");

	session_start();

	if(!isset($_SESSION["user"])) {
		header('Location: index.php');
	}

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(!isset($_GET["id"])) {
		header('Location: main.php');
	}

	$id = intval($mysqli->real_escape_string($_GET["id"]));

	if(isset($_POST["submit"])) {
		$id = $_POST["id"];
	}

	$query = "SELECT * FROM pages WHERE id = $id LIMIT 1";
	$fetch_page = $mysqli->query($query);

	if($fetch_page->num_rows == 0) {
		header('Location: main.php');
	}

	$pageObj = $fetch_page->fetch_assoc();

	$page = "editpage";
	require_once('./header.php');
?>
<h2>Edit Page "<em><?php echo $pageObj["title"]; ?></em>" (Web ID: <?php echo $pageObj["id"]; ?>)</h2>
<form action="edit_page.php" class="box">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<div class="row">
		<div class="col col50">
			<label for="dataset">Title</label>
			<input type="text" name="title" value="<?php echo $pageObj["title"]; ?>">
			<label for="header">Header</label>
			<input type="text" name="header" value="<?php echo $pageObj["header"]; ?>">
			<label for="routes">Routes</label>
			<textarea name="routes" rows="3"><?php echo $pageObj["routes"]; ?></textarea>
			<label for="scripts">Scripts</label>
			<textarea name="scripts" rows="7"><?php echo $pageObj["scripts"]; ?></textarea>
			<button type="submit" name="submit" class="button"><span class="icon-done"></span> Save Changes</button> <button type="button" class="button">Cancel</button>
		</div><div class="col col50">
			<label for="content">Content</label>
			<textarea name="content" class="wysiwyg" rows="16"><?php echo $pageObj["content"]; ?></textarea>
		</div>
	</div>
</form>
<?php
	require_once("./footer.php");
?>