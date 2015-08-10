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
		$pages[strtolower(str_replace(" ", "", $page["title"]))] = $page;
	}

	$fieldData = fetchFieldData($mysqli, true);

	// Routing
	// ===================================================

	$router->map("GET|POST", "/", "homePage", "home");

	$router->map("GET|POST", "/search/", "searchPage", "search");
	$router->map("GET|POST", "/specimen/[i:id]/", "specimenPage", "specimen");
	$router->map("GET|POST", "/export/", "exportPage", "export");

	$router->map("GET|POST", "/publications/", "publicationsPage", "publications");
	$router->map("GET|POST", "/history/", "historyPage", "history");
	$router->map("GET|POST", "/links/", "linksPage", "links");
	$router->map("GET|POST", "/contact/", "contactPage", "contact");

	// Routing functions
	// ===================================================

	function homePage() {
		global $twig;
		global $pages;
		echo $twig->render("base.html", array(
			"title" => $pages["home"]["title"], 
			"header" => $pages["home"]["header"],
			"scripts" => $pages["home"]["scripts"],
			"content" => $pages["home"]["content"]
		));
	}

	function searchPage() {
		global $twig;
		global $pages;
		global $fieldData;

		error_log($pages["search"]["title"]);

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

				$headers = "Content-type:application/json";
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

				$headers = "Content-type:text/csv";
			} else if($type == "xml") {
				$xml = new SimpleXMLElement('<xml/>');

				while($s = $fetch_specimens->fetch_assoc()) {
					$specimen = $xml->addChild('specimen');
					foreach($fieldData as $f) {
						$specimen->addChild($f["field"], htmlspecialchars($s[$f["field"]]));
					}
				}

				$headers = "Content-type:text/xml";

				$dom = dom_import_simplexml($xml)->ownerDocument;
				$dom->formatOutput = true;
				$output = $dom->saveXML();
			}

			header($headers);
			echo $output;
		}
	}

	function publicationsPage() {
		global $twig;
		global $pages;
		echo $twig->render("base.html", array(
			"title" => $pages["publications"]["title"], 
			"header" => $pages["publications"]["header"],
			"scripts" => $pages["publications"]["scripts"],
			"content" => $pages["publications"]["content"]
		));
	}

	function historyPage() {
		global $twig;
		global $pages;
		echo $twig->render("base.html", array(
			"title" => $pages["history"]["title"], 
			"header" => $pages["history"]["header"],
			"scripts" => $pages["history"]["scripts"],
			"content" => $pages["history"]["content"]
		));
	}

	function linksPage() {
		global $twig;
		global $pages;
		echo $twig->render("base.html", array(
			"title" => $pages["links"]["title"], 
			"header" => $pages["links"]["header"],
			"scripts" => $pages["links"]["scripts"],
			"content" => $pages["links"]["content"]
		));
	}

	function contactPage() {
		global $twig;
		global $pages;
		echo $twig->render("base.html", array(
			"title" => $pages["contact"]["title"], 
			"header" => $pages["contact"]["header"],
			"scripts" => $pages["contact"]["scripts"],
			"content" => $pages["contact"]["content"]
		));
	}

	// Route Handling
	// ===================================================

	$match = $router->match();

	if($match && is_callable($match["target"])) {
		call_user_func_array($match["target"], $match["params"]); 
	} else {
		$twig->render("404.html");
	}
?>
