<?php
	require_once('config.php');
	require_once('shared.php');

	$submitted = false;

	require_once('header.php');
?>
<script>
	$(document).ready(function() {
		//Aquilegia test
		var page = 1;
		var itemsPerPage = 100;

		$('#searchResults').css('display', 'none');

		function makeSearchResult(data) {
			var returnStr = '<a class="specimen" href="specimen.php?id=' + data['web_id'] + '"><h3>' + data['genus'] + ' ' + data['species'] + '</h3><div class="accession"><span>Accession #: </span>' + data['accession'] + '</div><div class="dth"><span>DTH #: </span>' + data['DTH'] + '</div><div class="date_collected"><span>Date Collected: </span>' + data['date_collected'] + '</div></a>';

			return returnStr;
		}

		function addFilter(boolVal, fieldVal, matchTypeVal, paramVal) {
			boolVal = typeof boolVal !== 'undefined' ? boolVal : "AND";
			fieldVal = typeof fieldVal !== 'undefined' ? fieldVal : "web_id";
			matchTypeVal = typeof matchTypeVal !== 'undefined' ? matchTypeVal : "equal";
			paramVal = typeof paramVal !== 'undefined' ? paramVal : "";

			var html = '<li class="searchRestriction"><select class="andOr" name="boolean[]">';

			if(boolVal == "OR") {
				html += '<option value="AND">AND</option><option value="OR" selected="selected">OR</option>';
			} else {
				html += '<option value="AND" selected="selected">AND</option><option value="OR">OR</option>'
			}

			html += '</select> <div class="booleanSpacing">&#8942;</div><select name="field[]" class="field">';

			var fields = <?php echo json_encode($availFields); ?>;

			for(var f in fields) {
				if(f == fieldVal) {
					html += '<option value="' + f + '" selected="selected">' + fields[f] + '</option>';
				} else {
					html += '<option value="' + f + '">' + fields[f] + '</option>';
				}
			}

			html += '</select> <select name="match[]" class="match">';

			var equivs = <?php echo json_encode(array_keys($equivs)); ?>;
			var equivLabels = {
				'equal': 'exactly equals',
				'notequal': 'does not equal',
				'contains': 'contains',
				'notcontains': 'does not contain',
				'start': 'starts with',
				'end': 'ends with'
			};

			for(var e in equivs) {
				if(equivs[e] == matchTypeVal) {
					html += '<option value="' + equivs[e] + '" selected="selected">' + equivLabels[equivs[e]] + '</option>';
				} else {
					html += '<option value="' + equivs[e] + '">' + equivLabels[equivs[e]] + '</option>';
				}
			}

			html += '<input type="text" name="parameter[]" class="parameter" value="' + paramVal + '"><button type="button" class="searchRestrictionDelete">Delete</button></li>';

			$('.searchRestrictions').append(html);
		}

		$('#searchForm').submit(function(e) {
			if(document.getElementById('exportType').value == '') {
				e.preventDefault();

				var booleans = [];
				$("[name='boolean[]']").each(function() {
					booleans.push(this.value);
				});

				var fields = [];
				$("[name='field[]']").each(function() {
					fields.push(this.value);
				});

				var matches = [];
				$("[name='match[]']").each(function() {
					matches.push(this.value);
				});

				var parameters = [];
				$("[name='parameter[]']").each(function() {
					parameters.push(this.value);
				});

				$.get("export.php", {
					type: "json", 
					"boolean[]": booleans, 
					"field[]": fields, 
					"match[]": matches, 
					"parameter[]": parameters
				}).done(function(data) {
					$('#searchResults').css('display', 'block');
					$('#resultsContainer').html('');
					console.log(data);
					if(data.length > 0) {
						for(var d in data) {
							$('#resultsContainer').append(makeSearchResult(data[d]));
						}
					} else {
						$('#resultsContainer').html('<strong>No specimens were found with those parameters.</strong>');
					}
				});
			}
		});

		if(document.getElementById('parameter0').value != '') {
			$('#searchForm').submit();
		}

		$('#addFilter').click(function() {
			addFilter();

			$('.searchRestrictionDelete').click(function() {
				$(this).parent().remove();
			});
		});
		$('.searchRestrictionDelete').click(function() {
			$(this).parent().remove();
		});

		$(".searchRestrictions").sortable({
			items: "li:not(.firstRestriction)"
		});
	});
</script>
<h2>Search Database</h2>
<p>
	We here provide a multi-term search engine for our herbarium database. As yet we do not have all records entered, but thus far include approximately 16,000 specimens from Eastern Ontario. Records can be exported in JSON, CSV, or XML format. With time we will include all 144,000 specimens.
</p>
<form id="searchForm" name="searchForm" action="search.php" method="post">
	<input type="hidden" name="type" id="exportType" value="">
	<ul class="searchRestrictions">
		<li class="searchRestriction firstRestriction">
			<input type="hidden" name="boolean[]" value="">
			<select name="field[]" class="field"><?php foreach(array_keys($availFields) as $f) { if($submitted) { $s = ""; if($f == $fields[0]) { $s = 'selected="selected"'; } echo '<option ' . $s . ' value="' . $f . '">' . $availFields[$f] . "</option>"; } else { echo '<option value="' . $f . '">' . $availFields[$f] . '</option>'; } } ?></select>
			<select name="match[]" class="match">
				<option value="equal" <?php if($submitted) { if($matchTypes[0] == "equal") { ?>selected="selected"<?php } } ?>>exactly equals</option>
				<option value="notequal" <?php if($submitted) { if($matchTypes[0] == "notequal") { ?>selected="selected"<?php } } ?>>does not equal</option>
				<option value="contains" <?php if($submitted) { if($matchTypes[0] == "contains") { ?>selected="selected"<?php } } ?>>contains</option>
				<option value="notcontains" <?php if($submitted) { if($matchTypes[0] == "notcontains") { ?>selected="selected"<?php } } ?>>does not contain</option>
				<option value="start" <?php if($submitted) { if($matchTypes[0] == "start") { ?>selected="selected"<?php } } ?>>starts with</option>
				<option value="end" <?php if($submitted) { if($matchTypes[0] == "end") { ?>selected="selected"<?php } } ?>>ends with</option>
			</select><input type="text" name="parameter[]" class="parameter" id="parameter0" <?php if($submitted) { echo 'value = "' . $parameters[0] . '"'; } ?>>
		</li>
	</ul>
	<button type="submit" name="submitBtn"><span class="icon-search"></span> Search</button> <button id="addFilter" type="button">+ Add Filter</button>
	<div id="searchResults">
		<h2>Search Results <button type="button" onclick="$('#exportType').attr('value', 'csv'); $('#searchForm').attr('action', 'export.php'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', 'search.php'); $('#searchForm').attr('method', 'post');">Export as CSV</button> <button type="button" onclick="$('#exportType').attr('value', 'json'); $('#searchForm').attr('action', 'export.php'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', 'search.php'); $('#searchForm').attr('method', 'post');">Export as JSON</button> <button type="button" onclick="$('#exportType').attr('value', 'xml'); $('#searchForm').attr('action', 'export.php'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', 'search.php'); $('#searchForm').attr('method', 'post');">Export as XML</button></h2>
		<div id="resultsContainer">
		</div>
	</div>
</form>
<?php require_once('footer.php'); ?>