<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/shared.php");

// Load Composer packages
require_once($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

// Load other libraries
require_once($_SERVER["DOCUMENT_ROOT"] . "/admin/includes/PasswordHash.php");

require_once("controllers/static.php");
require_once("controllers/specimen.php");
require_once("controllers/api.php");

// INITIALIZATION
// ===================================================

// Start sessions for user login
session_start();

// Set up the database
$mysqli = mysqli_connect($__DATABASE["host"], $__DATABASE["user"], $__DATABASE["password"], $__DATABASE["name"]);
if(mysqli_connect_errno()) {
	http_response_code(500);
	die("Connect failed: " . mysqli_connect_error());
}
$mysqli->set_charset("utf8");

// Load template library and set it up
$loader = new \Twig\Loader\FilesystemLoader($_SERVER["DOCUMENT_ROOT"] . "/templates");
$twig = new \Twig\Environment($loader);

// Set up the page router
$router = new AltoRouter();

// Fetch settings from database
$settings = fetchSettings($mysqli);
$web_settings = $settings; // TODO: Remove any settings that shouldn't be passed to templates

// Variables used in routing/page handling
// ===================================================

// Fetch arrays of all pages from the database, even ones which don't show up in the menu
list($pages, $menu_pages) = fetchPages($mysqli);

$fieldData = fetchFieldData($mysqli, true); // Data about specimen fields which are searchable
$fieldDataAll = fetchFieldData($mysqli, false); // Data about all specimen fields

// ROUTING
// ===================================================

// Pages from database, home, and search page
Controllers\initializeStaticPages($router);

// Pages not editable
Controllers\initializeSpecimenPages($router);

// API-specific routes
Controllers\initializeAPIPages($router);

// ROUTE HANDLING
// ===================================================

$match = $router->match();

if($match && is_callable($match["target"])) {
	call_user_func_array($match["target"], $match["params"]);
} else {
	// If there's no match to the request URL, return a 404 page.

	http_response_code(404);
	echo $twig->render("404.html.twig", array(
		"site_name" => $settings["site_name"],
		"menu_pages" => $menu_pages,
		"ga_tracking_id" => $settings["ga_tracking_id"]
	));
}
