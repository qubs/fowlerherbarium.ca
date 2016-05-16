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

	if(!isset($_GET["id"])) {
		header("Location: main.php");
	}

	$id = -1;

	if(isset($_POST["saveEntry"])) {
		$id = $_POST["id"];
	} else {
		$id = intval($mysqli->real_escape_string($_GET["id"]));
	}

	$field_info = fetchFieldData($mysqli, false);
	$field_info_assoc = array();

	foreach($field_info as $f) {
		$field_info_assoc[$f["field"]] = $f;
	}

	$query = "SELECT * FROM specimens WHERE web_id = $id LIMIT 1";
	$fetch_specimen = $mysqli->query($query);

	if($fetch_specimen->num_rows == 0) {
		header("Location: main.php");
	}

	$specimen = $fetch_specimen->fetch_assoc();

	if(isset($_POST["saveEntry"])) {
		$dataset = $_POST["dataset"];

		$genus = $_POST["genus"];
		$species = $_POST["species"];

		$DTH = $_POST["DTH"];
		$accession = $_POST["accession"];

		$common_name = $_POST["common_name"];

		$date_collected = $_POST["date_collected"];
		$collectors = $_POST["collectors"];

		$map_included = $_POST["map_included"];
		$map_reference = $_POST["map_reference"];
		$county = $_POST["county"];
		$township = $_POST["township"];
		$location = $_POST["location"];

		$habitat = $_POST["habitat"];
		$notes = $_POST["notes"];

		$image_name = $specimen["image"];

		$dead = false;
		$error = "";

		if(!(!file_exists($_FILES["image"]["tmp_name"]) || !is_uploaded_file($_FILES["image"]["tmp_name"]))) {
			$image_name = $accession . "-" . strval(time()) . "." . end(explode(".", $_FILES["image"]["name"]));
			$image_file_path = $__UPLOAD_DIRECTORY . $image_name;

			if(!move_uploaded_file($_FILES["image"]["tmp_name"], $image_file_path)) {
				$dead = true;
				$error = "An error was encountered while uploading the image.";
			}
		}

		if(!$dead) {
			$edit_entry = $mysqli->prepare("UPDATE specimens SET dataset = ?, genus = ?, species = ?, DTH = ?, accession = ?, common_name = ?, date_collected = ?, collectors = ?, map_included = ?, map_reference = ?, county = ?, township = ?, location = ?, habitat = ?, notes = ?, image = ? WHERE web_id = ? LIMIT 1");

			$edit_entry->bind_param("ssssssssssssssssi",
				$dataset,

				$genus,
				$species,

				$DTH,
				$accession,

				$common_name,

				$date_collected,
				$collectors,

				$map_included,
				$map_reference,
				$county,
				$township,
				$location,

				$habitat,
				$notes,

				$image_name,

				$id
			);

			$edit_entry->execute();
			$edit_entry->close();
		}
	}

	$page = "editentry";
	require_once("./header.php");
?>
<h2>Edit Entry "<em><?php echo $specimen["genus"] . " " . $specimen["species"]; ?></em>" (Web ID: <?php echo $specimen["web_id"]; ?>)</h2>
<form action="edit_entry.php" method="post" class="box" enctype="multipart/form-data">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
	<div class="row">
		<div class="col col50">
			<label for="dataset">
				Dataset
				<div class="help"><div><?php echo $field_info_assoc["dataset"]["help"]; ?></div></div>
			</label>
			<input type="text" name="dataset" value="<?php echo $specimen["dataset"]; ?>">
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="genus">
						Genus
						<div class="help"><div><?php echo $field_info_assoc["genus"]["help"]; ?></div></div>
					</label>
					<input type="text" name="genus" value="<?php echo $specimen["genus"]; ?>">
				</div><div class="col col50 noPad padLeft">
					<label for="species">
						Species
						<div class="help"><div><?php echo $field_info_assoc["species"]["help"]; ?></div></div>
					</label>
					<input type="text" name="species" value="<?php echo $specimen["species"]; ?>">
				</div>
			</div>
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="DTH">
						DTH #
						<div class="help"><div><?php echo $field_info_assoc["DTH"]["help"]; ?></div></div>
					</label>
					<input type="text" name="DTH" value="<?php echo $specimen["DTH"]; ?>">
				</div><div class="col col50 noPad padLeft">
					<label for="accession">
						Accession #
						<div class="help"><div><?php echo $field_info_assoc["accession"]["help"]; ?></div></div>
					</label>
					<input type="text" name="accession" value="<?php echo $specimen["accession"]; ?>">
				</div>
			</div>

			<label for="common_name">
				Common Name
				<div class="help"><div><?php echo $field_info_assoc["common_name"]["help"]; ?></div></div>
			</label>
			<input type="text" name="common_name" value="<?php echo $specimen["common_name"]; ?>">

			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="date_collected">
						Date Collected
						<div class="help"><div><?php echo $field_info_assoc["date_collected"]["help"]; ?></div></div>
					</label>
					<input type="text" name="date_collected" placeholder="mm dd yyyy" value="<?php echo $specimen["date_collected"]; ?>">
				</div><div class="col col50 noPad padLeft">
					<label for="collectors">
						Collector(s)
						<div class="help"><div><?php echo $field_info_assoc["collectors"]["help"]; ?></div></div>
					</label>
					<input type="text" name="collectors" value="<?php echo $specimen["collectors"]; ?>">
				</div>
			</div>

			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="map_included"><input type="checkbox" name="map_included" <?php if($specimen["map_included"] == "yes") { ?>checked<?php } ?>> Map Included</label>
				</div><div class="col col50 noPad padLeft">
					<label for="map_reference">
						Map Reference
						<div class="help"><div><?php echo $field_info_assoc["map_reference"]["help"]; ?></div></div>
					</label>
					<input type="text" name="map_reference" value="<?php echo $specimen["map_reference"]; ?>">
				</div>
			</div>

			<label for="image">
				Image <?php if(!empty($specimen["image"])) { echo "(Current: " . $specimen["image"] . ")"; } ?>
			</label>
			<input type="file" name="image">

			<button class="button" type="submit" name="saveEntry"><span class="icon-done"></span> Save Changes</button> <a class="button" href="main.php"><span class="icon-highlight-remove"></span> Cancel</a>
		</div><div class="col col50">
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="county">County <div class="help"><div><?php echo $field_info_assoc["county"]["help"]; ?></div></div></label>
					<input type="text" name="county" value="<?php echo $specimen["county"]; ?>">
				</div><div class="col col50 noPad padLeft">
					<label for="township">Township <div class="help"><div><?php echo $field_info_assoc["township"]["help"]; ?></div></div></label>
					<input type="text" name="township" value="<?php echo $specimen["township"]; ?>">
				</div>
			</div>
			<label for="location">
				Location
				<div class="help"><div><?php echo $field_info_assoc["location"]["help"]; ?></div></div>
			</label>
			<input type="text" name="location" value="<?php echo $specimen["location"]; ?>">
			<label for="habitat">
				Habitat
				<div class="help"><div><?php echo $field_info_assoc["habitat"]["help"]; ?></div></div>
			</label>
			<textarea name="habitat" cols="30" rows="5"><?php echo $specimen["habitat"]; ?></textarea>
			<label for="habitat">
				Notes
				<div class="help"><div><?php echo $field_info_assoc["notes"]["help"]; ?></div></div>
			</label>
			<textarea name="notes" cols="30" rows="5"><?php echo $specimen["notes"]; ?></textarea>
		</div>
	</div>
</form>
<?php require_once("./footer.php"); ?>
