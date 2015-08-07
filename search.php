<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/header.php");

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	$fieldData = fetchFieldData($mysqli, true);

	echo $twig->render("search.html", array("fieldData" => $fieldData));
?>
