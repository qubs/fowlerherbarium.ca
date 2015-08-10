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

	$page = "main";
	require_once('./header.php');
?>
<h2>Pages</h2>
<!-- <form action="main.php" class="box">
	<h3>Add New</h3>
	<div class="row">
		<div class="col col50">
			<label for="dataset">Dataset <div class="help"><div>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Molestias, quidem excepturi adipisci officia, cum eaque doloribus autem dolores voluptas eum animi minima eius. Eveniet vitae provident facere tempore mollitia enim!</div></div></label>
			<input type="text" name="dataset" value="kingston">
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="genus">Genus</label>
					<input type="text" name="genus">
				</div><div class="col col50 noPad padLeft">
					<label for="species">Species</label>
					<input type="text" name="species">
				</div>
			</div>
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="DTH">DTH #</label>
					<input type="text" name="DTH">
				</div><div class="col col50 noPad padLeft">
					<label for="accession">Accession #</label>
					<input type="text" name="accession">
				</div>
			</div>
			<label for="common_name">Common Name</label>
			<input type="text" name="common_name">
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="date_collected">Date Collected</label>
					<input type="text" name="date_collected" placeholder="mm dd yyyy">
				</div><div class="col col50 noPad padLeft">
					<label for="collectors">Collector(s)</label>
					<input type="text" name="collectors">
				</div>
			</div>
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="map_included"><input type="checkbox" name="map_included" value="yes"> Map Included</label>
				</div><div class="col col50 noPad padLeft">
					<label for="map_reference">Map Reference</label>
					<input type="text" name="map_reference">
				</div>
			</div>
			<label for="image">Image</label>
			<input type="file" name="image">
			<button type="submit" name="submitEntry"><span class="icon-control-point"></span> Add Entry</button>
		</div><div class="col col50">
			<div class="row">
				<div class="col col50 noPad padRight">
					<label for="county">County</label>
					<input type="text" name="county">
				</div><div class="col col50 noPad padLeft">
					<label for="township">Township</label>
					<input type="text" name="township">
				</div>
			</div>
			<label for="location">Location</label>
			<input type="text" name="location">
			<label for="habitat">Habitat</label>
			<textarea name="habitat" cols="30" rows="5"></textarea>
			<label for="habitat">Notes</label>
			<textarea name="notes" cols="30" rows="5"></textarea>
		</div>
	</div>
</form> -->
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
