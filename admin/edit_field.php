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

	if(!isset($_GET["id"]) && !isset($_POST["submit"])) {
		header("Location: main.php");
	} else if(isset($_POST["submit"])) {
		$id = intval($_POST["id"]);

		$field = $_POST["field"];
		$label = $_POST["label"];
		$help = $_POST["help"];
		$input = $_POST["input"];
		$options = $_POST["options"];
		$searchable = intval($_POST["searchable"]);

		$dead = false;
		$error = "";

		if(empty($field) || empty($label) || empty($input) || empty($searchable)) {
			$dead = true;
			$error = "All required fields must be filled out.";
		}

		if(!$dead) {
			$update_field = $mysqli->prepare("UPDATE fields SET field = ?, label = ?, help = ?, input = ?, options = ?, searchable = ? WHERE id = ? LIMIT 1");
			$update_field->bind_param("sssssii", $field, $label, $help, $input, $options, $searchable, $id);
			$update_field->execute();
			$update_field->close();

			header("Location: fields.php");
		}
	} else {
		$id = intval($mysqli->real_escape_string($_GET["id"]));
	}

	$query = "SELECT * FROM fields WHERE id = $id LIMIT 1";
	$fetch_field = $mysqli->query($query);

	if($fetch_field->num_rows == 0) {
		header("Location: main.php");
	}

	$field = $fetch_field->fetch_assoc();

	$page = "editfield";
	require_once("./header.php");
?>
<h2><a href="fields.php" class="button" style="float: left; margin-right: 10px; margin-left: 0;">&larr; Back to Fields</a> Edit Field "<em><?php echo $field["field"]; ?>"</em></h2>
<form action="edit_field.php" class="box">
	<?php if(isset($error) && !empty($error)) { ?>
		<div class="error"><?php echo $error; ?></div>
	<?php } ?>
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<div class="row">
		<div class="col col50">
			<label for="field" class="required" style="margin-top: 0;">Field</label>
			<input type="text" name="field" required value="<?php echo $field["field"]; ?>">
			<label for="label" class="required">Label</label>
			<input type="text" name="label" required value="<?php echo $field["label"]; ?>">
			<label for="help">Help Text</label>
			<textarea name="help" rows="7"><?php echo $field["help"]; ?></textarea>
			<br>
			<button class="button" type="submit" name="submit"><span class="icon-done"></span> Save Changes</button> <a class="button" href="fields.php"><span class="icon-highlight-remove"></span> Cancel</a>
		</div><div class="col col50">
			<label for="input" class="required" style="margin-top: 0;">Input</label>
			<input type="text" name="input" required value="<?php echo $field["input"]; ?>">
			<label for="options" class="required">Options</label>
			<input type="text" name="options" required value="<?php echo $field["options"]; ?>">
			<label for="searchable" class="required">Searchable?</label>
			<input type="text" name="searchable" required value="<?php echo $field["searchable"]; ?>">
		</div>
	</div>
</form>
<?php require_once("./footer.php"); ?>
