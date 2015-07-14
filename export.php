<?php
	require_once('config.php');
	require_once('shared.php');

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);

	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}

	$mysqli->set_charset("utf8");

	$type = $_GET['type'];
	$booleans = $_GET['boolean'];
	$fields = $_GET['field'];
	$matchTypes = $_GET['match'];
	$parameters = $_GET['parameter'];

	$dead = false;

	if(!isset($type) || !isset($booleans) || !isset($fields) || !isset($matchTypes) || !isset($parameters)) {
		$dead = true;
	}

	if(!$dead) {
		$query = buildSearchQuery($mysqli, $booleans, $fields, $matchTypes, $parameters);

		$fetch_specimens = $mysqli->query($query);

		$headers = "";
		$output = "";

		if($type == "json") {
			$output_array = array();

			while($specimen = $fetch_specimens->fetch_assoc()) {
				array_push($output_array, $specimen);
			}

			$headers = "Content-type:application/json";
			$output = json_encode($output_array);
		} else if($type == "csv") {
			$output .= implode(",", array_keys($availFields)) . "\n";

			while($specimen = $fetch_specimens->fetch_assoc()) {
				$s = $specimen;

				foreach(array_keys($s) as $k) {
					$s[$k] = '"' . str_replace('"', '""', $s[$k]) . '"';
				}

				$output .= implode(",", $s) . "\n";
			}

			$headers = "Content-type:text/csv";
		} else if($type == "xml") {
			//...
		}

		header($headers);
		echo $output;
	}
?>