<?php

namespace Controllers;

/**
 * TODO
 * @param $id
 * @return array
 */
function fetchSpecimen($id) {
	global $mysqli;

	$fetchSpecimen = $mysqli->prepare("SELECT * FROM specimens WHERE web_id = ? LIMIT 1");
	$fetchSpecimen->bind_param("i", $id);
	$fetchSpecimen->execute();
	return $fetchSpecimen->get_result()->fetch_assoc();
}

/**
 * TODO
 * @param $router
 * @return void
 */
function initializeSpecimenPages($router) {
	// Renders a page for a specimen in the database.
	$router->map("GET", "/specimen/[i:id]/", function ($id) {
		global $menu_pages;
		global $twig;
		global $fieldDataAll;
		global $web_settings;

		$specimen = fetchSpecimen($id);

		echo $twig->render("specimen.html.twig", array(
			"settings" => $web_settings,
			"menu_pages" => $menu_pages,
			"field_data" => $fieldDataAll,
			"specimen" => $specimen
		));
	});

	// Returns a JSON object with the data for a particular specimen in the database.
	$router->map("GET", "/data/specimen/[i:id]/", function ($id) {
		$specimen = fetchSpecimen($id);

		header("Content-type: application/json");
		echo json_encode($specimen);
	});
}
