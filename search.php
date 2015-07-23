<?php
	require_once('config.php');
	require_once('shared.php');

	$submitted = false;

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	$fieldData = fetchFieldData($mysqli, true);

	require_once('header.php');
?>
<script>
	var page = 1;
	var maxPage = 1;
	var pagination = false;
	var itemsPerPage = 100;

	$(document).ready(function() {
		//Aquilegia test

		var fields = <?php echo json_encode($fieldData); ?>;

		$('#searchResults').css('display', 'none');

		function makeSearchResult(searchResultNumber, data) {
			var returnStr = '<a class="specimen" id="specimen' + searchResultNumber.toString() + '" href="specimen.php?id=' + data['web_id'] + '"><h3>' + data['genus'] + ' ' + data['species'] + '</h3><div class="accession"><span>Accession #: </span>' + data['accession'] + '</div><div class="dth"><span>DTH #: </span>' + data['DTH'] + '</div><div class="date_collected"><span>Date Collected: </span>' + data['date_collected'] + '</div></a>';

			return returnStr;
		}

		function addFilter(boolVal, fieldVal, matchTypeVal, paramVal) {
			boolVal = typeof boolVal !== 'undefined' ? boolVal : "AND";
			fieldVal = typeof fieldVal !== 'undefined' ? fieldVal : "genus";
			matchTypeVal = typeof matchTypeVal !== 'undefined' ? matchTypeVal : "equal";
			paramVal = typeof paramVal !== 'undefined' ? paramVal : "";

			var html = '<li class="searchRestriction"><select class="andOr" name="boolean[]">';

			if(boolVal == "OR") {
				html += '<option value="AND">AND</option><option value="OR" selected="selected">OR</option>';
			} else {
				html += '<option value="AND" selected="selected">AND</option><option value="OR">OR</option>'
			}

			html += '</select> <div class="booleanSpacing">&#8942;</div><select name="field[]" class="field">';

			var fields = <?php echo json_encode($fieldData); ?>;

			for(var f in fields) {
				if(fields[f]["field"] == fieldVal) {
					html += '<option value="' + fields[f]["field"] + '" data-inputtype="' + fields[f]["input"] + '" selected="selected">' + fields[f]["label"] + '</option>';
				} else {
					html += '<option value="' + fields[f]["field"] + '" data-inputtype="' + fields[f]["input"] + '">' + fields[f]["label"] + '</option>';
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
				html += '<option value="' + equivs[e] + '"';
				if(equivs[e] == matchTypeVal) {
					html += ' selected="selected"';
				}
				if(equivs[e] == "contains" || equivs[e] == "notcontains" || equivs[e] == "start" || equivs[e] == "end") {
					html += ' class="disableForSelect"';
				}
				html += ">" + equivLabels[equivs[e]] + "</option>";
			}

			html += '<input type="text" name="parameter[]" class="parameter" value="' + paramVal + '"><button type="button" class="searchRestrictionDelete"><span class="icon-highlight-remove"></span> Delete</button></li>';

			$('.searchRestrictions').append(html);
		}

		if(window.location.hash != '' && window.location.hash != '#') {
			var searchParams = JSON.parse(window.location.hash.substring(1));

			if(searchParams.hasOwnProperty("page")) {
				page = searchParams["page"];
			}

			for(var i = 0; i < searchParams["boolean[]"].length; i++) {
				if(i == 0) {
					document.getElementById("boolean0").value = searchParams["boolean[]"][i];
					document.getElementById("field0").value = searchParams["field[]"][i];
					document.getElementById("match0").value = searchParams["match[]"][i];
					document.getElementById("parameter0").value = searchParams["parameter[]"][i];
				} else {
					addFilter(searchParams["boolean[]"][i], searchParams["field[]"][i], searchParams["match[]"][i], searchParams["parameter[]"][i])
				}
			}
		}

		$('#searchForm').submit(function(e) {
			if(document.getElementById('exportType').value == '') {
				e.preventDefault();

				$('#loading').css('display', 'block');

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
					$('#resultsContainer').html('');
					var numberOfResults = data.length;
					maxPage = Math.ceil(numberOfResults / itemsPerPage);

					if(data.length > 0) {
						var i = 1;
						var html = "";
						for(var d in data) {
							html += makeSearchResult(i, data[d]);
							i++;
						}

						$('#resultsContainer').append(html);
						$('#loading').css('display', 'none');
					} else {
						$('#resultsContainer').html('<strong>No specimens were found with those parameters.</strong>');
					}

					$("#searchResults").css("display", "block");

					if(numberOfResults > itemsPerPage) {
						pagination = true;
						$('#pagination').css("display", "block");
						$('.specimen').css("display", "none");

						var startOfPage = ((page - 1) * itemsPerPage) + 1;
						var endOfPage = (page * itemsPerPage);

						for(var i = startOfPage; i <= endOfPage; i++) {
							$("#specimen" + i.toString()).css("display", "block");
						}

						$("#bBeforeBtn").text((page - 2).toString());
						$("#beforeBtn").text((page - 1).toString());
						$("#currentPage").text(page.toString());
						$("#afterBtn").text((page + 1).toString());
						$("#aAfterBtn").text((page + 2).toString());

						if(page == 1) {
							$("#prevPageBtn").attr("disabled", "disabled");
							$("#prevPageBtn").addClass("disabled");
							$("#beforeBtn").css("display", "none");
							$("#beforeBtn").parent().css("borderRightWidth", "0");
							$("#aAfterBtn").css("display", "inline-block");
							$("#aAfterBtn").parent().css("borderRightWidth", "1px");
						} else {
							$("#prevPageBtn").removeAttr("disabled");
							$("#prevPageBtn").removeClass("disabled");
							$("#beforeBtn").css("display", "inline-block");
							$("#beforeBtn").parent().css("borderRightWidth", "1px");
							$("#aAfterBtn").css("display", "none");
							$("#aAfterBtn").parent().css("borderRightWidth", "0");
						}

						if(page == maxPage) {
							$("#nextPageBtn").attr("disabled", "disabled");
							$("#nextPageBtn").addClass("disabled");
							$("#afterBtn").css("display", "none");
							$("#afterBtn").parent().css("borderRightWidth", "0");
							$("#bBeforeBtn").css("display", "inline-block");
							$("#bBeforeBtn").parent().css("borderRightWidth", "1px");
						} else {
							$("#nextPageBtn").removeAttr("disabled");
							$("#nextPageBtn").removeClass("disabled");
							$("#afterBtn").css("display", "inline-block");
							$("#afterBtn").parent().css("borderRightWidth", "1px");
							$("#bBeforeBtn").css("display", "none");
							$("#bBeforeBtn").parent().css("borderRightWidth", "0");
						}
					}

					var hashObject = {
										"boolean[]": booleans, 
										"field[]": fields, 
										"match[]": matches, 
										"parameter[]": parameters
									};

					if(numberOfResults > itemsPerPage) {
						hashObject["page"] = page;
					}

					window.location.hash = JSON.stringify(hashObject);
				});
			}
		});

		if(document.getElementById('parameter0').value != '') {
			$('#searchForm').submit();
		}

		function updateInputTypeOnParameter(obj) {
			var inputType = $(obj).children("option:selected").data("inputtype");

			if(inputType == "select") {
				var include0id = false;
				if($(obj).parent().children(".parameter")[0].id == "parameter0") {
					include0id = true;
				}

				$(obj).parent().children(".parameter").remove();
				var select = '<select name="parameter[]" class="parameter select"';
				if(include0id) {
					select += ' id="parameter0"';
				}
				select += '>';
				var options = '';
				for(var f in fields) {
					if(fields[f]["field"] == obj.value) {
						var splitOptions = fields[f]["options"].split(",");
						for(var o in splitOptions) {
							options += '<option value="' + splitOptions[o] + '">' + splitOptions[o] + '</option>';
						}
					}
				}
				select += options;
				select += "</select>";
				$(obj).parent().append(select);

				$(obj).parent().children("select").children(".disableForSelect").each(function() {
					$(this).attr("disabled", "disabled");
				});
			} else {
				if($(obj).parent().children('.parameter').hasClass("select")) {
					$(obj).parent().children('.parameter').remove();
					var input = '<input type="text" name="parameter[]" class="parameter">';
					$(obj).parent().append(input);
				}
				$(obj).parent().children(".parameter").attr("type", inputType);

				$(obj).parent().children("select").children(".disableForSelect").each(function() {
					$(this).removeAttr("disabled");
				});
			}
		}

		$('.field').change(function() {
			updateInputTypeOnParameter(this);
		});
		$('.field').change();

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

	function refreshHash() {
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

		var hashObject = {
							"boolean[]": booleans, 
							"field[]": fields, 
							"match[]": matches, 
							"parameter[]": parameters,
							"page": page
						};

		window.location.hash = JSON.stringify(hashObject);
	}

	function setPage(pageNo) {
		var startOfOldPage = ((page - 1) * itemsPerPage) + 1;
		var endOfOldPage = (page * itemsPerPage);

		for(var i = startOfOldPage; i <= endOfOldPage; i++) {
			$('#specimen' + i.toString()).css("display", "none");
		}

		page = pageNo;

		var startOfPage = ((page - 1) * itemsPerPage) + 1;
		var endOfPage = (page * itemsPerPage);

		for(var i = startOfPage; i <= endOfPage; i++) {
			$("#specimen" + i.toString()).css("display", "block");
		}

		$("#bBeforeBtn").text((page - 2).toString());
		$("#beforeBtn").text((page - 1).toString());
		$("#currentPage").text(page.toString());
		$("#afterBtn").text((page + 1).toString());
		$("#aAfterBtn").text((page + 2).toString());

		if(page == 1) {
			$("#prevPageBtn").attr("disabled", "disabled");
			$("#prevPageBtn").addClass("disabled");
			$("#beforeBtn").css("display", "none");
			$("#beforeBtn").parent().css("borderRightWidth", "0");
			$("#aAfterBtn").css("display", "inline-block");
			$("#aAfterBtn").parent().css("borderRightWidth", "1px");
		} else {
			$("#prevPageBtn").removeAttr("disabled");
			$("#prevPageBtn").removeClass("disabled");
			$("#beforeBtn").css("display", "inline-block");
			$("#beforeBtn").parent().css("borderRightWidth", "1px");
			$("#aAfterBtn").css("display", "none");
			$("#aAfterBtn").parent().css("borderRightWidth", "0");
		}

		if(page == maxPage) {
			$("#nextPageBtn").attr("disabled", "disabled");
			$("#nextPageBtn").addClass("disabled");
			$("#afterBtn").css("display", "none");
			$("#afterBtn").parent().css("borderRightWidth", "0");
			$("#bBeforeBtn").css("display", "inline-block");
			$("#bBeforeBtn").parent().css("borderRightWidth", "1px");
		} else {
			$("#nextPageBtn").removeAttr("disabled");
			$("#nextPageBtn").removeClass("disabled");
			$("#afterBtn").css("display", "inline-block");
			$("#afterBtn").parent().css("borderRightWidth", "1px");
			$("#bBeforeBtn").css("display", "none");
			$("#bBeforeBtn").parent().css("borderRightWidth", "0");
		}

		refreshHash();
	}

	function nextPage() {
		if(page < maxPage) {
			setPage(page + 1);
		}

	}
	function prevPage() {
		if(page > 1) {
			setPage(page - 1);
		}
	}
	function firstPage() {
		setPage(1);
	}
	function lastPage() {
		setPage(maxPage);
	}
</script>
<h2>Search Database</h2>
<p>
	We here provide a multi-term search engine for our herbarium database. As yet we do not have all records entered, but thus far include approximately 16,000 specimens from Eastern Ontario. Records can be exported in JSON, CSV, or XML format. With time we will include all 144,000 specimens.
</p>
<form id="searchForm" name="searchForm" action="search.php" method="post">
	<input type="hidden" name="type" id="exportType" value="">
	<ul class="searchRestrictions">
		<li class="searchRestriction firstRestriction">
			<input type="hidden" name="boolean[]" id="boolean0" value="">
			<select name="field[]" class="field" id="field0"><?php foreach($fieldData as $f) { echo '<option value="' . $f["field"] . '" data-inputtype="' . $f["input"] . '"'; if($f["field"] == "genus") { echo " selected"; } echo '>' . $f["label"] . '</option>'; } ?></select>
			<select name="match[]" class="match" id="match0">
				<option value="equal">exactly equals</option>
				<option value="notequal">does not equal</option>
				<option value="contains" class="disableForSelect">contains</option>
				<option value="notcontains" class="disableForSelect">does not contain</option>
				<option value="start" class="disableForSelect">starts with</option>
				<option value="end" class="disableForSelect">ends with</option>
			</select><input type="text" name="parameter[]" class="parameter" id="parameter0">
		</li>
	</ul>
	<button type="submit" name="submitBtn" onclick="firstPage();"><span class="icon-search"></span> Search</button> <button id="addFilter" type="button">+ Add Filter</button>
	<div id="loading" style="display: none;">Loading...</div>
	<div id="searchResults">
		<h2>Search Results <button type="button" onclick="$('#exportType').attr('value', 'csv'); $('#searchForm').attr('action', 'export.php'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', 'search.php'); $('#searchForm').attr('method', 'post');">Export as CSV</button> <button type="button" onclick="$('#exportType').attr('value', 'json'); $('#searchForm').attr('action', 'export.php'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', 'search.php'); $('#searchForm').attr('method', 'post');">Export as JSON</button> <button type="button" onclick="$('#exportType').attr('value', 'xml'); $('#searchForm').attr('action', 'export.php'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', 'search.php'); $('#searchForm').attr('method', 'post');">Export as XML</button></h2>
		<div id="resultsContainer">
		</div>

		<ul id="pagination" style="display: none;">
			<li><button onclick="firstPage();" type="button">&laquo; First</button></li><li><button onclick="prevPage();" id="prevPageBtn" type="button">&#8249; Previous</button></li><li><button id="bBeforeBtn" onclick="setPage(page - 2);" type="button"></button></li><li><button id="beforeBtn" onclick="prevPage();" type="button"></button></li><li><button id="currentPage" class="active" onclick="" type="button">1</button></li><li><button id="afterBtn" onclick="nextPage();" type="button"></button></li><li><button id="aAfterBtn" onclick="setPage(page + 2);" type="button"></button></li><li><button id="nextPageBtn" onclick="nextPage();" type="button">Next &#8250;</button></li><li><button onclick="lastPage();" type="button">Last &raquo;</button></li>
		</ul>
	</div>
</form>
<?php require_once('footer.php'); ?>