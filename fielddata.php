<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/shared.php");

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);

	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}

	$search = filter_var($_GET["search"], FILTER_VALIDATE_BOOLEAN);

	header("Content-type: application/json");
	echo json_encode(fetchFieldData($mysqli, $search));
?>
