<?php

// A list of boolean comparisons and their MySQL syntaxes.
$equivs = array(
	'equal' => ' = "@" ',
	'notequal' => ' != "@" ',
	'contains' => ' LIKE "%@%" ',
	'notcontains' => ' NOT LIKE "%@%" ',
	'start' => ' LIKE "@%" ',
	'end' => ' LIKE "%@" '
);

/**
 * Fetches site settings from the database.
 * @param object $mysqli The mysqli connection object to use for querying.
 * @return array A collection of site settings.
 */
function fetchSettings($mysqli) {
	$fetch_settings = $mysqli->query("SELECT * FROM settings");

	// Turn rows into an associative array
	$settings = array();
	while($s = $fetch_settings->fetch_assoc()) {
		$settings[$s["name"]] = $s["value"];
	}
	return $settings;
}

/**
 * Fetches site pages.
 * @param object $mysqli
 * @return array Array of two arrays: an array of all pages and an array of pages in the menu, sorted.
 */
function fetchPages($mysqli) {
	$fetch_pages = $mysqli->query("SELECT * FROM pages");

	$pages = array();
	$menu_pages = array();

	function compareWeight($a, $b) {
		if($a["menu_order"] == $b["menu_order"]) {
			return 0;
		}

		return ($a["menu_order"] < $b["menu_order"] ? -1 : 1);
	}

	while($page = $fetch_pages->fetch_assoc()) {
		$pages[$page["page_id"]] = $page;
		if($page["in_menu"] == 1) {
			array_push($menu_pages, $page);
		}
	}

	uasort($menu_pages, "compareWeight");

	return array($pages, $menu_pages);
}

/**
 * Fetches data about fields for specimen objects.
 * @param object $mysqli The mysqli connection object to use for querying.
 * @param bool $search Whether or not to limit the generated list of fields to searchable ones.
 * @return array A list of fields within the bounds of the parameters given.
 */
function fetchFieldData($mysqli, $search = false) {
	$query = "SELECT * FROM fields";
	if($search) {
		$query .= " WHERE searchable = 1 ORDER BY field ASC";
	}
	$fetch_fields = $mysqli->query($query);
	$fieldInfo = array();
	$fieldInfoFirst = array();

	while($f = $fetch_fields->fetch_assoc()) {
		if(!$search || ($f["field"] != "genus" && $f["field"] != "species")) {
			array_push($fieldInfo, $f);
		} else if($f["field"] == "genus" || $f["field"] == "species") {
			array_push($fieldInfoFirst, $f);
		}
	}

	$fieldInfo = array_merge($fieldInfoFirst, $fieldInfo);

	return $fieldInfo;
}

/**
 * Builds a search query with the specified restrictions.
 * @param object $mysqli
 * @param $booleans
 * @param $fields
 * @param $matchTypes
 * @param $parameters
 * @return string The query string created by the method.
 */
function buildSearchQuery($mysqli, $booleans, $fields, $matchTypes, $parameters) {
	global $equivs;

	$query = "SELECT * FROM specimens ";

	for($i = 0; $i < count($fields); $i++) {
		$whereClause = "WHERE ";

		if($i != 0) {
			$whereClause = " " . $booleans[$i] . " ";
		}

		$whereClause .= $mysqli->real_escape_string($fields[$i]);

		// Append the comparison operator, replacing the placeholder symbol with the correct parameter.
		$whereClause .= str_replace('@', $parameters[$i], $equivs[$matchTypes[$i]]);

		$query .= $whereClause;
	}

	$query .= " ORDER BY CONCAT(genus, species) ASC";

	return $query;
}
