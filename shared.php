<?php
	$availFields = array(
		'web_id' => 'Web ID',
		'dataset' => 'Dataset',
		'genus' => 'Genus',
		'species' => 'Species',
		'DTH' => 'DTH #',
		'accession' => 'Accession #',
		'common_name' => 'Common Name',
		'date_collected' => 'Date Collected',
		'collectors' => 'Collectors',
		'map_included' => 'Map Included',
		'map_reference' => 'Map Reference',
		'county' => 'County',
		'township' => 'Township',
		'location' => 'Location',
		'habitat' => 'Habitat',
		'notes' => 'Notes',
		'image' => 'Image'
	);

	$equivs = array(
		'equal' => ' = "@" ',
		'notequal' => ' != "@" ',
		'contains' => ' LIKE "%@%" ',
		'notcontains' => ' NOT LIKE "%@%" ',
		'start' => ' LIKE "@%" ',
		'end' => ' LIKE "%@" '
	);

	function buildSearchQuery($mysqli, $booleans, $fields, $matchTypes, $parameters) {
		global $availFields, $equivs;

		$query = "SELECT * FROM specimens ";

		for($i = 0; $i < count($fields); $i++) {
			$whereClause = "WHERE ";

			if($i != 0) {
				$whereClause = " " . $booleans[$i] . " ";
			}

			$whereClause .= $mysqli->real_escape_string($fields[$i]);
			$whereClause .= str_replace('@', $parameters[$i], $equivs[$matchTypes[$i]]);

			$query .= $whereClause;
		}

		$query .= " ORDER BY CONCAT(genus, species) ASC";

		return $query;
	}
?>