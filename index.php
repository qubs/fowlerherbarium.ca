<?php 
	require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/shared.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

	// Initialize things
	// ===================================================

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	$loader = new Twig_Loader_Filesystem($_SERVER["DOCUMENT_ROOT"] . "/templates");
	$twig = new Twig_Environment($loader);

	$router = new AltoRouter();

	// Variables used in routing/page handling
	// ===================================================

	$fetch_pages = $mysqli->query("SELECT * FROM pages");
	$pages = array();
	while($page = $fetch_pages->fetch_assoc()) {
		$pages[$page["page_id"]] = $page;
	}

	$fieldData = fetchFieldData($mysqli, true);

	// Routing
	// ===================================================

	// Pages from database

	foreach($pages as $page) {
		$router->map("GET|POST", $page["routes"], "databasePage", $page["page_id"]);
	}

	// Pages not editable

	$router->map("GET|POST", "/specimen/[i:id]/", "specimenPage", "specimen");
	$router->map("GET|POST", "/data/specimen/[i:id]/", "specimenData", "specimendata");
	$router->map("GET|POST", "/export/", "exportPage", "export");

	// Routing functions
	// ===================================================

	function databasePage($targetPage) {
		global $twig;
		global $pages;
		global $fieldData;

		echo $twig->render("base.html", array(
			"title" => $pages[$targetPage]["title"], 
			"header" => $pages[$targetPage]["header"],
			"scripts" => $pages[$targetPage]["scripts"],
			"content" => $pages[$targetPage]["content"],
			"fieldData" => $fieldData
		));
	}

	function searchPage() {
		global $twig;
		global $pages;
		global $fieldData;

		echo $twig->render("base.html", array(
			"title" => $pages["search"]["title"], 
			"header" => $pages["search"]["header"],
			"scripts" => $pages["search"]["scripts"],
			"content" => $pages["search"]["content"],
			"fieldData" => $fieldData
		));
	}

	function specimenRender($id) {
		global $twig;
		global $pages;
		global $mysqli;
		global $fieldData;

		$fetchSpecimen = $mysqli->query("SELECT * FROM specimens WHERE web_id = " . $mysqli->real_escape_string($id) . " LIMIT 1");
		$specimen = $fetchSpecimen->fetch_assoc();

		return $twig->render("specimen.html", array("fieldData" => $fieldData, "specimen" => $specimen));
	}
	function specimenPage($id) {
		global $twig;
		echo $twig->render("base.html", array(
			"title" => $pages["specimen"]["title"], 
			"header" => $pages["specimen"]["header"],
			"scripts" => $pages["specimen"]["scripts"],
			"content" => specimenRender($id)
		));
	}
	function specimenData($id) {
		$fetchSpecimen = $mysqli->prepare("SELECT * FROM specimens WHERE web_id = ? LIMIT 1");
		$fetchSpecimen->bind_param("i", $id);
		$fetchSpecimen->execute();
		$fetchSpecimenResult = $fetchSpecimen->get_result();
		$specimen = $fetchSpecimenResult->fetch_assoc();

		$headers = "Content-type: application/json";
		echo json_encode($specimen);
	}

	function exportPage() {
		global $twig;
		global $pages;
		global $mysqli;
		global $fieldData;

		$type = $_GET["type"];
		$booleans = $_GET["boolean"];
		$fields = $_GET["field"];
		$matchTypes = $_GET["match"];
		$parameters = $_GET["parameter"];

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

				$headers = "Content-type: application/json";
				$output = json_encode($output_array);
			} else if($type == "csv") {
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

				$headers = "Content-type: text/csv";
			} else if($type == "xml") {
				$xml = new SimpleXMLElement("<xml/>");

				while($s = $fetch_specimens->fetch_assoc()) {
					$specimen = $xml->addChild("specimen");
					foreach($fieldData as $f) {
						$specimen->addChild($f["field"], htmlspecialchars($s[$f["field"]]));
					}
				}

				$headers = "Content-type: text/xml";

				$dom = dom_import_simplexml($xml)->ownerDocument;
				$dom->formatOutput = true;
				$output = $dom->saveXML();
			}

			header($headers);
			echo $output;
		}
	}

	// Route Handling
	// ===================================================

	$match = $router->match();

	if($match && is_callable($match["target"])) {
		if($match["target"] == "databasePage") {
			array_push($match["params"], $match["name"]);
			call_user_func_array($match["target"], $match["params"]);
		} else {
			call_user_func_array($match["target"], $match["params"]);
		}
	} else {
		$twig->render("404.html");
	}
?>
