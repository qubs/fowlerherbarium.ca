<?php
	require_once("../config.php");
	require_once("../shared.php");

	session_start();

	if(!isset($_SESSION["user"])) {
		header("Location: index.php");
	}

	$mysqli = mysqli_connect($__DATABASE["host"], $__DATABASE["user"], $__DATABASE["password"], $__DATABASE["name"]);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(isset($_POST["submit"])) {
		$id = $_POST["page_id"];

		$title = $_POST["title"];
		$old_title = $_POST["old_title"];
		$header = $_POST["header"];
		$in_menu = $_POST["in_menu"];
		$menu_order = filter_var($_POST["menu_order"], FILTER_VALIDATE_INT, array("options" => array("default" => -1)));
		$menu_icon = $_POST["menu_icon"];
		$routes = $_POST["routes"];
		$content = $_POST["content"];

		$in_menu_bool = 0;

		if($in_menu == "1") {
			$in_menu_bool = 1;
		}

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
			$update_page = $mysqli->prepare("UPDATE pages SET title = ?, header = ?, in_menu = ?, menu_order = ?, menu_icon = ?, routes = ?, content = ? WHERE page_id = ? LIMIT 1");
			$update_page->bind_param("ssiissss", $title, $header, $in_menu_bool, $menu_order, $menu_icon, $routes, $content, $id);
			$update_page->execute();
			$update_page->close();

			header("Location: pages.php");
		}
	} else {
		if(!isset($_GET["id"])) {
			header("Location: main.php");
		}

		$id = $_GET["id"];
	}

	$query = $mysqli->prepare("SELECT * FROM pages WHERE page_id = ? LIMIT 1");
	$query->bind_param("s", $id);
	$query->execute();
	$fetch_page = $query->get_result();

	if($fetch_page->num_rows == 0) {
		header("Location: main.php");
	}

	$pageObj = $fetch_page->fetch_assoc();
	$query->close();

	$page = "editpage";
	require_once("./header.php");
?>
<h2>Edit Page "<em><?php echo $pageObj["title"]; ?></em>" (Web ID: <?php echo $pageObj["page_id"]; ?>)</h2>
<form action="edit_page.php" method="post" class="box">
	<?php if(isset($error) && !empty($error)) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<input type="hidden" name="page_id" value="<?php echo $id; ?>">
	<div class="row">
		<div class="col col50">
			<input type="hidden" name="old_title" value="<?php echo $pageObj["title"]; ?>">
			<label for="dataset" class="required">Title</label>
			<input type="text" name="title" value="<?php echo $pageObj["title"]; ?>" required>
			<label for="header" class="required">Header</label>
			<input type="text" name="header" value="<?php echo $pageObj["header"]; ?>" required>
			<div class="row">
				<div class="col col33 noPad padRight">
					<label><input type="checkbox" name="in_menu" value="1" <?php if($pageObj["in_menu"] == 1) { echo "checked"; } ?>> Show in menu?</label>
				</div><div class="col col33 noPad">
					<label for="menu_order">Order in Menu</label>
					<input type="number" name="menu_order" value="<?php echo $pageObj["menu_order"]; ?>">
				</div><div class="col col33 noPad padLeft">
					<label for="menu_icon">Menu Icon</label>
					<input type="text" name="menu_icon" value="<?php echo $pageObj["menu_icon"]; ?>">
				</div>
			</div>
			<label for="routes">Routes</label>
			<textarea name="routes" rows="3"><?php echo $pageObj["routes"]; ?></textarea>
			<button type="submit" name="submit" class="button"><span class="icon-done"></span> Save Changes</button> <a class="button" href="pages.php"><span class="icon-highlight-remove"></span> Cancel</a>
		</div><div class="col col50">
			<label for="content">Content</label>
			<textarea name="content" class="wysiwyg" rows="16" required><?php echo $pageObj["content"]; ?></textarea>
		</div>
	</div>
</form>
<?php require_once("./footer.php"); ?>
