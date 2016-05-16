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

	$fetch_all = $mysqli->query("SELECT web_id FROM specimens");

	$fieldData = fetchFieldData($mysqli, false);
	$fieldDataAssoc = array();
	foreach($fieldData as $f) {
		$fieldDataAssoc[$f["field"]] = $f;
	}

	$rowsPerPage = 100.0;
	$nOfPages = ceil($fetch_all->num_rows / $rowsPerPage);

	$pageNo = 1;

	if(isset($_GET["page"])) {
		$set_page = intval($_GET["page"]);
		if($set_page >= 1 && $set_page <= $nOfPages) {
			$pageNo = $set_page;
		}
	}

	if(isset($_POST["submitEntry"])) {
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

		$image_file_path = $__UPLOAD_DIRECTORY . $accession . "-" . strval(time()
				. "." . end(explode(".", $_FILES["image"]["name"])));

		$dead = false;
		$error = "";

		$check_existing = $mysqli->prepare("SELECT * FROM specimens WHERE accession = ?");
		$check_existing->bind_param("s", $accession);
		$check_existing->execute();
		$check_existing->store_result();

		if($check_existing->num_rows > 0) {
			$dead = true;
			$error = "A specimen with that accession number already exists.";
		}

		$check_existing->close();

		if(!$dead) {
			// Don't bother moving the file if the form has already failed for another reason
			if(!move_uploaded_file($_FILES["image"]["tmp_name"], $image_file_path)) {
				$dead = true;
				$error = "An error was encountered while uploading the image.";
			}
		}

		if(!$dead) {
			$insert_entry = $mysqli->prepare("INSERT INTO specimens(dataset, genus, species, DTH, accession, common_name, date_collected, collectors, map_included, map_reference, county, township, location, habitat, notes, image) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

			$insert_entry->bind_param("ssssssssssssssss",
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

					$image_file_path
				);

			$insert_entry->execute();
			$insert_entry->close();
		}
	}

	$query = "SELECT * FROM specimens ORDER BY CONCAT(genus, species) ASC LIMIT " . ($pageNo - 1) * $rowsPerPage . ", 100";
	$fetch_specimens = $mysqli->query($query);

	$page = "main";
	require_once("./header.php");
?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".help").each(function() {
			if($(this).children("div")[0].innerHTML == "") {
				$(this).css("display", "none");
			}
		});
	});
</script>
<h2>Entries</h2>
<form action="main.php" method="post" class="box" enctype="multipart/form-data">
	<h3>Add New</h3>
	<div class="row">
		<div class="col col50">
			<label for="dataset">
				Dataset
				<div class="help"><div><?php echo $fieldDataAssoc["dataset"]["help"]; ?></div></div>
			</label>
			<input type="text" name="dataset" value="kingston">

			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="genus">
						Genus
						<div class="help"><div><?php echo $fieldDataAssoc["genus"]["help"]; ?></div></div>
					</label>
					<input type="text" name="genus" id="genus">
				</div><div class="col col50 noPad padLeft">
					<label for="species">
						Species
						<div class="help"><div><?php echo $fieldDataAssoc["species"]["help"]; ?></div></div>
					</label>
					<input type="text" name="species" id="species">
				</div>
			</div>

			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="DTH">DTH # <div class="help"><div><?php echo $fieldDataAssoc["DTH"]["help"]; ?></div></div></label>
					<input type="text" name="DTH">
				</div><div class="col col50 noPad padLeft">
					<label for="accession">Accession # <div class="help"><div><?php echo $fieldDataAssoc["accession"]["help"]; ?></div></div></label>
					<input type="text" name="accession" id="accession">
				</div>
			</div>

			<label for="common_name">
				Common Name
				<div class="help"><div><?php echo $fieldDataAssoc["common_name"]["help"]; ?></div></div>
			</label>
			<input type="text" name="common_name">

			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="date_collected">
						Date Collected
						<div class="help"><div><?php echo $fieldDataAssoc["date_collected"]["help"]; ?></div></div>
					</label>
					<input type="text" name="date_collected" placeholder="mm dd yyyy">
				</div><div class="col col50 noPad padLeft">
					<label for="collectors">
						Collector(s)
						<div class="help"><div><?php echo $fieldDataAssoc["collectors"]["help"]; ?></div></div>
					</label>
					<input type="text" name="collectors" id="collectors">
				</div>
			</div>
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="map_included"><input type="checkbox" name="map_included" value="yes"> Map Included <div class="help"><div><?php echo $fieldDataAssoc["map_included"]["help"]; ?></div></div></label>
				</div><div class="col col50 noPad padLeft">
					<label for="map_reference">Map Reference <div class="help"><div><?php echo $fieldDataAssoc["map_reference"]["help"]; ?></div></div></label>
					<input type="text" name="map_reference">
				</div>
			</div>
			<label for="image">
				Image
				<div class="help"><div><?php echo $fieldDataAssoc["image"]["help"]; ?></div></div>
			</label>
			<input type="file" name="image">
			<button class="button" type="submit" name="submitEntry"><span class="icon-control-point"></span> Add Entry</button>
		</div><div class="col col50">
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="county">County <div class="help"><div><?php echo $fieldDataAssoc["county"]["help"]; ?></div></div></label>
					<input type="text" name="county">
				</div><div class="col col50 noPad padLeft">
					<label for="township">Township <div class="help"><div><?php echo $fieldDataAssoc["township"]["help"]; ?></div></div></label>
					<input type="text" name="township">
				</div>
			</div>

			<label for="location">
				Location
				<div class="help"><div><?php echo $fieldDataAssoc["location"]["help"]; ?></div></div>
			</label>
			<input type="text" name="location">

			<label for="habitat">
				Habitat
				<div class="help"><div><?php echo $fieldDataAssoc["habitat"]["help"]; ?></div></div>
			</label>
			<textarea name="habitat" rows="5"></textarea>

			<label for="habitat">
				Notes
				<div class="help"><div><?php echo $fieldDataAssoc["notes"]["help"]; ?></div></div>
			</label>
			<textarea name="notes" rows="5"></textarea>
		</div>
	</div>
</form>
<div class="box">
	<h3>Existing Entries</h3>
	<p>Specimens are listed in order of ascending latin name.</p>
	<table>
		<thead>
			<tr>
				<th>Web ID</th>
				<th>Dataset</th>
				<th>Genus</th>
				<th>Species</th>
				<th>DTH #</th>
				<th>Acc. #</th>
				<th>Common Name</th>
				<th>Date Collected</th>
				<th>Options</th>
			</tr>
		</thead>
		<tbody>
			<?php while($specimen = $fetch_specimens->fetch_assoc()) { ?>
				<tr>
					<td><?=$specimen["web_id"]?></td>
					<td><?=$specimen["dataset"]?></td>
					<td><?=$specimen["genus"]?></td>
					<td><?=$specimen["species"]?></td>
					<td><?=$specimen["DTH"]?></td>
					<td><?=$specimen["accession"]?></td>
					<td><?=$specimen["common_name"]?></td>
					<td><?=$specimen["date_collected"]?></td>
					<td>
						<a class="button" href="edit_entry.php?id=<?php echo $specimen["web_id"]; ?>">
							<span class="icon-mode-edit"></span> Edit
						</a>
						<button class="button"><span class="icon-highlight-remove"></span> Delete</button>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

	<ul class="pagination">
		<li><button class="button" onclick="window.location = 'main.php?page=1'">&laquo; First</button></li><li><button class="button<?php if($pageNo <= 1) { echo ' disabled'; } ?>" <?php if($pageNo > 1) { ?>onclick="window.location = 'main.php?page=<?php echo $pageNo - 1; ?>'" <?php } ?>>&#8249; Previous</button></li><!-- <li><button class="active">1</button></li><li><button>2</button></li><li><button>3</button></li><li class="skipToPage"><label for="skip">Skip to Page:</label> <input type="text" name="skip"><button>Go</button></li><li><button>98</button></li><li><button>99</button></li><li><button><?php echo $nOfPages; ?></button> --><?php
			if($pageNo == 1) {
		?><li><button class="button active" onclick="window.location = 'main.php?page=1'">1</button></li><li><button class="button" onclick="window.location = 'main.php?page=2'">2</button></li><li><button class="button" onclick="window.location = 'main.php?page=3'">3</button></li><?php
			} else if($pageNo == $nOfPages) {
		?><li><button class="button" onclick="window.location = 'main.php?page=<?php echo $nOfPages - 2; ?>'"><?php echo $nOfPages - 2; ?></button></li><li><button class="button" onclick="window.location = 'main.php?page=<?php echo $nOfPages - 1; ?>'"><?php echo $nOfPages - 1; ?></button></li><li><button class="button active" onclick="window.location = 'main.php?page=<?php echo $nOfPages; ?>'"><?php echo $nOfPages; ?></button></li><?php
			} else {
		?><li><button class="button" onclick="window.location = 'main.php?page=<?php echo $pageNo - 1; ?>'"><?php echo $pageNo - 1; ?></button></li><li><button class="button active" onclick="window.location = 'main.php?page=<?php echo $pageNo; ?>'"><?php echo $pageNo; ?></button></li><li><button class="button" onclick="window.location = 'main.php?page=<?php echo $pageNo + 1; ?>'"><?php echo $pageNo + 1; ?></button></li><?php
			}
		?><li><button class="button<?php if($pageNo >= $nOfPages) { echo ' disabled'; } ?>" <?php if($pageNo < $nOfPages) { ?>onclick="window.location = 'main.php?page=<?php echo $pageNo + 1; ?>'"<?php } ?>>Next &#8250;</button></li><li><button class="button" onclick="window.location = 'main.php?page=<?php echo $nOfPages; ?>'">Last &raquo;</button></li>
	</ul>
</div>
<?php require_once("./footer.php"); ?>
