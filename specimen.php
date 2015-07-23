<?php
	require_once('config.php');
	require_once('shared.php');

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	if(!isset($_GET['id'])) {
		header('Location: search.php');
	}

	$id = intval($_GET['id']);
	$fetch_specimen = $mysqli->query("SELECT * FROM specimens WHERE web_id = " . $mysqli->real_escape_string($id) . " LIMIT 1");

	$specimen = $fetch_specimen->fetch_assoc();

	$fieldData = fetchFieldData($mysqli, false);

	require_once('header.php');
?>
<button type="button" onclick="window.history.back();">&larr; Back</button>
<h2>Specimen Details</h2>
<div class="specimenFull">
<h3><?php echo $specimen['genus'] . ' ' . $specimen['species']; ?></h3>
<table>
	<thead>
		<tr>
			<th>Field</th><th>Value</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($fieldData as $f) { ?>
			<tr>
				<td><?php echo $f['label']; ?></td>
				<td><?php echo $specimen[$f['field']]; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table><div class="wiki">
	<h3>From Wikipedia</h3>
	<iframe src="http://en.m.wikipedia.org/wiki/<?php echo str_replace(' ', '_', ($specimen['genus'] . ' ' . $specimen['species'])); ?>" frameborder="0"></iframe>
</div>
</div>
<?php require_once('footer.php'); ?>