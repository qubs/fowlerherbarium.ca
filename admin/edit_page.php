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
		$id = $_POST["id"];

		$title = $_POST["title"];
		$old_title = $_POST["old_title"];
		$header = $_POST["header"];
		$routes = $_POST["routes"];
		$scripts = $_POST["scripts"];
		$content = $_POST["content"];

		$dead = false;
		$error = "";

		if(empty($title) || empty($old_title) || empty($header) || empty($routes) || empty($content)) {
			$dead = true;
			$error = "All required fields must be filled out.";
		}

		if($title !== $old_title) {
			$check_existing = $mysqli->prepare("SELECT * FROM pages WHERE title = ?");
			$check_existing->bind_param("s", $title);
			$check_existing->execute();
			$check_existing->store_result();

			if($check_existing->num_rows > 0) {
				$dead = true;
				$error = "A page with that title already exists";
			}

			$check_existing->close();
		}

		if(!$dead) {
			$update_page = $mysqli->prepare("UPDATE pages SET title = ?, header = ?, routes = ?, scripts = ?, content = ? WHERE id = ? LIMIT 1");
			$update_page->bind_param("sssssi", $title, $header, $routes, $scripts, $content, $id);
			$update_page->execute();
			$update_page->close();

			header("Location: pages.php");
		}
	} else {
		if(!isset($_GET["id"])) {
			header("Location: main.php");
		} 

		$id = intval($mysqli->real_escape_string($_GET["id"]));
	}

	$query = "SELECT * FROM pages WHERE id = $id LIMIT 1";
	$fetch_page = $mysqli->query($query);

	if($fetch_page->num_rows == 0) {
		header("Location: main.php");
	}

	$pageObj = $fetch_page->fetch_assoc();

	$page = "editpage";
	require_once("./header.php");
?>
<h2>Edit Page "<em><?php echo $pageObj["title"]; ?></em>" (Web ID: <?php echo $pageObj["id"]; ?>)</h2>
<form action="edit_page.php" method="post" class="box">
	<?php if(isset($error) && !empty($error)) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<div class="row">
		<div class="col col50">
			<input type="hidden" name="old_title" value="<?php echo $pageObj["title"]; ?>">
			<label for="dataset" class="required">Title</label>
			<input type="text" name="title" value="<?php echo $pageObj["title"]; ?>" required>
			<label for="header" class="required">Header</label>
			<input type="text" name="header" value="<?php echo $pageObj["header"]; ?>" required>
			<label for="routes">Routes</label>
			<textarea name="routes" rows="3"><?php echo $pageObj["routes"]; ?></textarea>
			<label for="scripts">Scripts</label>
			<textarea name="scripts" rows="7"><?php echo $pageObj["scripts"]; ?></textarea>
			<button type="submit" name="submit" class="button"><span class="icon-done"></span> Save Changes</button> <button type="button" class="button">Cancel</button>
		</div><div class="col col50">
			<label for="content">Content</label>
			<textarea name="content" class="wysiwyg" rows="16" required><?php echo $pageObj["content"]; ?></textarea>
		</div>
	</div>
</form>
<?php
	require_once("./footer.php");
?>
