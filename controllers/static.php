<?php

namespace Controllers;

function fetchPage($page_id) {
	global $mysqli;

	$fetch_page = $mysqli->prepare("SELECT * FROM pages WHERE page_id = ?");
	$fetch_page->bind_param("s", $page_id);
	$fetch_page->execute();

	return $fetch_page->get_result()->fetch_assoc();
}

/**
 * TODO
 * @param object $router The routing object.
 */
function initializeStaticPages($router) {
	$router->map("GET", "/page/[a:pageID]/", function ($page_id) {
		global $menu_pages;
		global $twig;
		global $fieldData;
		global $web_settings;

		$page = fetchPage($page_id);

		echo $twig->render("page.html.twig", array(
			"settings" => $web_settings,
			"menu_pages" => $menu_pages,
			"page" => $page,
			"field_data" => $fieldData
		));
	});

	$router->map("GET", "/", function () {
		global $menu_pages;
		global $twig;
		global $fieldData;
		global $web_settings;

		$page = fetchPage($web_settings["home_page"]);

		echo $twig->render("page.html.twig", array(
			"settings" => $web_settings,
			"menu_pages" => $menu_pages,
			"page" => $page,
			"field_data" => $fieldData
		));
	});

	$router->map("GET", "/search/", function () {
		global $menu_pages;
		global $twig;
		global $fieldData;
		global $web_settings;

		$page = fetchPage($web_settings["search_page"]);

		echo $twig->render("search.html.twig", array(
			"settings" => $web_settings,
			"menu_pages" => $menu_pages,
			"page" => $page,
			"field_data" => $fieldData
		));
	});
}
