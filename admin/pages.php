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

	$fetch_pages = $mysqli->query("SELECT * FROM pages");

	// if(isset($_POST["submitEntry"])) {
	// 	$dataset = $_POST["dataset"];

	// 	$genus = $_POST["genus"];
	// 	$species = $_POST["species"];

	// 	$DTH = $_POST["DTH"];
	// 	$accession = $_POST["accession"];

	// 	$common_name = $_POST["common_name"];

	// 	$date_collected = $_POST["date_collected"];
	// 	$collectors = $_POST["collectors"];

	// 	$map_included = $_POST["map_included"];
	// 	$map_reference = $_POST["map_reference"];
	// 	$county = $_POST["county"];
	// 	$township = $_POST["township"];
	// 	$location = $_POST["location"];

	// 	$habitat = $_POST["habitat"];
	// 	$notes = $_POST["notes"];

	// 	//$image = ...

	// 	$dead = false;
	// 	$error = "";

	// 	if(!$dead) {
	// 		$insert_entry = $mysqli->prepare("INSERT INTO specimens(dataset, genus, species, DTH, accession, common_name, date_collected, collectors, map_included, map_reference, county, township, location, habitat, notes) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	// 		$insert_entry->bind_param("sssssssssssssss", 
	// 				$dataset,

	// 				$genus,
	// 				$species,

	// 				$DTH,
	// 				$accession,

	// 				$common_name,

	// 				$date_collected,
	// 				$collectors,

	// 				$map_included,
	// 				$map_reference,
	// 				$county,
	// 				$township,
	// 				$location,

	// 				$habitat,
	// 				$notes
	// 			);

	// 		$insert_entry->execute();
	// 		$insert_entry->close();
	// 	}
	// }

	$page = "pages";
	require_once("./header.php");
?>
<h2>Pages</h2>
<form action="pages.php" class="box">
	<h3>Add New</h3>
	<div class="row">
		<div class="col col50">
			<strong>Coming Soon</strong>
			<!-- <button type="submit" name="submitEntry"><span class="icon-control-point"></span> Add Page</button> -->
		</div><div class="col col50">
		</div>
	</div>
</form>
<div class="box">
	<h3>Existing Pages</h3>
	<p>Specimens are listed in order of ascending latin name.</p>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Header</th>
				<th>Routes</th>
				<th>Options</th>
			</tr>
		</thead>
		<tbody>
			<?php while($page = $fetch_pages->fetch_assoc()) { ?>
				<tr>
					<td><?=$page["id"]?></td>
					<td><?=$page["title"]?></td>
					<td><?=$page["header"]?></td>
					<td><?=$page["routes"]?></td>
					<td><button class="button" onclick="window.location = 'edit_page.php?id=<?php echo $page["id"]; ?>'"><span class="icon-mode-edit"></span> Edit</button> <button class="button"><span class="icon-highlight-remove"></span> Delete</button></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php require_once("./footer.php"); ?>
