<?php

namespace Controllers;

function initializeAPIPages($router) {
	// TODO: Document available API calls
	$router->map("GET", "/data/export/", function () {
		global $mysqli;
		global $fieldData;

		// Fetch search parameters
		$type = $_GET["type"];
		$booleans = $_GET["boolean"];
		$fields = $_GET["field"];
		$matchTypes = $_GET["match"];
		$parameters = $_GET["parameter"];

		$dead = false;

		// If any search parameters weren't included, terminate the request
		if(!isset($type) || !isset($booleans) || !isset($fields) || !isset($matchTypes) || !isset($parameters)) {
			$dead = true;
		}

		if(!$dead) {
			// Build a MySQL query out of the search parameter arrays
			$query = buildSearchQuery($mysqli, $booleans, $fields, $matchTypes, $parameters);

			$fetch_specimens = $mysqli->query($query);

			$headers = "";
			$output = "";

			if($type == "json") {
				$headers = "Content-type: application/json";

				$output_array = array();

				while($specimen = $fetch_specimens->fetch_assoc()) {
					array_push($output_array, $specimen);
				}

				$output = json_encode($output_array);
			} else if($type == "csv") {
				$headers = "Content-type: text/csv";

				$fieldKeys = array();
				foreach($fieldData as $f) {
					array_push($fieldKeys, $f["field"]);
				}

				$output .= implode(",", $fieldKeys) . "\n";

				while($specimen = $fetch_specimens->fetch_assoc()) {
					$s = $specimen;

					foreach($fieldData as $f) {
						$s[$f["field"]] = '"' . str_replace('"', '""', $s[$f["field"]]) . '"';
					}

					$output .= implode(",", $s) . "\n";
				}
			} else if($type == "xml") {
				$headers = "Content-type: text/xml";

				$xml = new \SimpleXMLElement("<xml/>");

				while($s = $fetch_specimens->fetch_assoc()) {
					$specimen = $xml->addChild("specimen");
					foreach($fieldData as $f) {
						$specimen->addChild($f["field"], htmlspecialchars($s[$f["field"]]));
					}
				}

				$dom = dom_import_simplexml($xml)->ownerDocument;
				$dom->formatOutput = true;
				$output = $dom->saveXML();
			}

			header($headers);
			echo $output;
		} else {
			header("Content-type: text/plain");
			http_response_code(400); // "Bad request" error code

			echo "Invalid request.";
		}
	});

	$router->map("GET", "/data/fields/", function () {
		global $mysqli;

		header("Content-type: application/json");
		$search = filter_var($_GET["search"], FILTER_VALIDATE_BOOLEAN); // Returns false if not set

		echo json_encode(\fetchFieldData($mysqli, $search));
	});
}
