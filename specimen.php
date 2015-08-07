<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/header.php");

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

	echo $twig->render("specimen.html", array("fieldData" => $fieldData, "specimen" => $specimen));
?>
