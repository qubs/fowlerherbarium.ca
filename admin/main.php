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

	$fetch_all = $mysqli->query("SELECT web_id FROM specimens");

	$rowsPerPage = 100.0;
	$nOfPages = ceil($fetch_all->num_rows / $rowsPerPage);

	$pageNo = 1;

	if(isset($_GET['page'])) {
		$set_page = intval($_GET['page']);
		if($set_page >= 1 && $set_page < $nOfPages) {
			$pageNo = $set_page;
		}
	}

	$query = "SELECT * FROM specimens ORDER BY CONCAT(genus, species) ASC LIMIT " . $pageNo * $rowsPerPage . ", 100";
	$fetch_specimens = $mysqli->query($query);

	$page = "main";
	require_once('./header.php');
?>
<h2>Entries</h2>
<form action="main.php" class="box">
	<h3>Add New</h3>
	<div class="row">
		<div class="col col50">
			<label for="dataset">Dataset</label>
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
					<label for="map_included"><input type="checkbox" name="map_included"> Map Included</label>
				</div><div class="col col50 noPad padLeft">
					<label for="map_reference">Map Reference</label>
					<input type="text" name="map_reference">
				</div>
			</div>
			<label for="image">Image</label>
			<input type="file" name="image">
			<button type="submit" name="submit">Add Entry</button>
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
</form>
<div class="box">
	<h3>Existing Entries</h3>
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
					<td><?=$specimen['web_id']?></td>
					<td><?=$specimen['dataset']?></td>
					<td><?=$specimen['genus']?></td>
					<td><?=$specimen['species']?></td>
					<td><?=$specimen['DTH']?></td>
					<td><?=$specimen['accession']?></td>
					<td><?=$specimen['common_name']?></td>
					<td><?=$specimen['date_collected']?></td>
					<td><button onclick="window.location = 'edit_entry.php?id=<?php echo $specimen['web_id']; ?>'">Edit</button> <button>Delete</button></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

	<ul class="pagination">
		<li><button onclick="window.location = 'main.php?page=1'">&laquo; First</button></li><li><button <?php if($pageNo > 1) { ?>onclick="window.location = 'main.php?page=<?php echo $pageNo - 1; ?>'" <?php } else { echo 'class="disabled"'; } ?>>&#8249; Previous</button></li><!-- <li><button class="active">1</button></li><li><button>2</button></li><li><button>3</button></li><li class="skipToPage"><label for="skip">Skip to Page:</label> <input type="text" name="skip"><button>Go</button></li><li><button>98</button></li><li><button>99</button></li><li><button><?php echo $nOfPages; ?></button> --><?php
			if($pageNo == 1) {
		?><li><button class="active" onclick="window.location = 'main.php?page=1'">1</button></li><li><button onclick="window.location = 'main.php?page=2'">2</button></li><li><button onclick="window.location = 'main.php?page=3'">3</button></li><?php
			} else if($pageNo == $nOfPages - 1) {
		?><li><button onclick="window.location = 'main.php?page=<?php echo $nOfPages - 3; ?>'"><?php echo $nOfPages - 3; ?></button></li><li><button onclick="window.location = 'main.php?page=<?php echo $nOfPages - 2; ?>'"><?php echo $nOfPages - 2; ?></button></li><li><button class="active" onclick="window.location = 'main.php?page=<?php echo $nOfPages - 1; ?>'"><?php echo $nOfPages - 1; ?></button></li><?php
			} else {
		?><li><button onclick="window.location = 'main.php?page=<?php echo $pageNo - 1; ?>'"><?php echo $pageNo - 1; ?></button></li><li><button class="active" onclick="window.location = 'main.php?page=<?php echo $pageNo; ?>'"><?php echo $pageNo; ?></button></li><li><button onclick="window.location = 'main.php?page=<?php echo $pageNo + 1; ?>'"><?php echo $pageNo + 1; ?></button></li><?php
			}
		?><li><button onclick="window.location = 'main.php?page=<?php echo $pageNo + 1; ?>'">Next &#8250;</button></li><li><button onclick="window.location = 'main.php?page=<?php echo $nOfPages - 1; ?>'">Last &raquo;</button></li>
	</ul>
</div>
<?php
	require_once('./footer.php');
?>