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

	$fieldInfo = fetchFieldData($mysqli); // TODO: Is this needed?

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

?>
