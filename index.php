<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/shared.php");

// Load Composer packages
require_once($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

// INITIALIZATION
// ===================================================

// Set up the database
$mysqli = mysqli_connect($__DATABASE["host"], $__DATABASE["user"], $__DATABASE["password"], $__DATABASE["name"]);
if(mysqli_connect_errno()) {
	http_response_code(500);
	die("Connect failed: " . mysqli_connect_error());
}
$mysqli->set_charset("utf8");

// Load template library and set it up
$loader = new Twig_Loader_Filesystem($_SERVER["DOCUMENT_ROOT"] . "/templates");
$twig = new Twig_Environment($loader);

// Set up the page router
$router = new AltoRouter();

// Variables used in routing/page handling
// ===================================================

// Fetch all pages from the database, even ones which don't show up in the menu
$fetch_pages = $mysqli->query("SELECT * FROM pages");
$pages = array();
while($page = $fetch_pages->fetch_assoc()) {
	$pages[$page["page_id"]] = $page;
}

// TODO: This doesn't need a second call; just use data from the above request and filter/order it
$fetchMenuPages = $mysqli->query("SELECT * FROM pages WHERE in_menu = 1 ORDER BY menu_order ASC");
$menuPages = array();
while($menuPage = $fetchMenuPages->fetch_assoc()) {
	array_push($menuPages, $menuPage);
}

$fieldData = fetchFieldData($mysqli, true); // Data about specimen fields which are searchable
$fieldDataAll = fetchFieldData($mysqli, false); // Data about all specimen fields

// ROUTING
// ===================================================

// Pages from database

foreach($pages as $page) {
	$router->map("GET|POST", $page["routes"], "databasePage", $page["page_id"]);
}

// Pages not editable

$router->map("GET|POST", "/specimen/[i:id]/", "specimenPage", "specimen");
$router->map("GET|POST", "/data/specimen/[i:id]/", "specimenData", "specimendata");
$router->map("GET|POST", "/export/", "exportPage", "export");
$router->map("GET|POST", "/fields/", "fieldsData", "fieldsdata");

// ROUTE-HANDLING FUNCTIONS
// ===================================================

/**
 * Renders a page with information pulled from the database.
 * @param string $targetPage The page to render.
 * @return void
 */
function databasePage($targetPage) {
	global $menuPages;
	global $twig;
	global $pages;
	global $fieldData;
	global $__GA_TRACKING_ID;

	echo $twig->render("page.html.twig", array(
		"menuPages" => $menuPages,
		"gaTrackingId" => $__GA_TRACKING_ID,
		"title" => $pages[$targetPage]["title"],
		"header" => $pages[$targetPage]["header"],
		"scripts" => $pages[$targetPage]["scripts"],
		"content" => $pages[$targetPage]["content"],
		"fieldData" => $fieldData
	));
}

/**
 * Renders a page for a specimen in the database.
 * @param int $id The web id of the specimen to render a page for.
 * @return void
 */
function specimenPage($id) {
	global $menuPages;
	global $twig;
	global $mysqli;
	global $fieldDataAll;
	global $__GA_TRACKING_ID;

	$fetchSpecimen = $mysqli->query("SELECT * FROM specimens WHERE web_id = "
		. $mysqli->real_escape_string($id) . " LIMIT 1");
	$specimen = $fetchSpecimen->fetch_assoc();

	echo $twig->render("specimen.html.twig", array(
		"menuPages" => $menuPages,
		"gaTrackingId" => $__GA_TRACKING_ID,
		"fieldData" => $fieldDataAll,
		"specimen" => $specimen
	));
}

/**
 * Returns a JSON object with the data for a particular specimen in the database.
 * @param int $id The web id of the specimen to create a JSON object for.
 * @return void
 */
function specimenData($id) {
	// Specimen API

	global $mysqli;

	$fetchSpecimen = $mysqli->prepare("SELECT * FROM specimens WHERE web_id = ? LIMIT 1");
	$fetchSpecimen->bind_param("i", $id);
	$fetchSpecimen->execute();
	$fetchSpecimenResult = $fetchSpecimen->get_result();
	$specimen = $fetchSpecimenResult->fetch_assoc();

	header("Content-type: application/json");
	echo json_encode($specimen);
}

/**
 * Pulls data about a specimen set, dictated by a particular set of search parameters, and turns them into a data file.
 * @return void
 */
function exportPage() {
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

			$xml = new SimpleXMLElement("<xml/>");

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
		// TODO: Handle a failure
	}
}

/**
 * Renders a JSON list containing fields (either searchable or all) depending on the search parameter.
 * @return void
 */
function fieldsData() {
	global $mysqli;

	header("Content-type: application/json");
	$search = filter_var($_GET["search"], FILTER_VALIDATE_BOOLEAN);

	echo json_encode(fetchFieldData($mysqli, $search));
}

// ROUTE HANDLING
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
	// If there's no match to the request URL, return a 404 page.

	http_response_code(404);
	echo $twig->render("404.html.twig", array(
		"menuPages" => $menuPages,
		"gaTrackingId" => $__GA_TRACKING_ID
	));
}

?>
