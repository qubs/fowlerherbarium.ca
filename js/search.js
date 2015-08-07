function buildFilter(fieldData, boolVal, fieldVal, matchTypeVal, paramVal) {
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

	for(var f in FHS.fieldData) {
		if(FHS.fieldData[f]["field"] == fieldVal) {
			html += '<option value="' + FHS.fieldData[f]["field"] + '" data-inputtype="' + FHS.fieldData[f]["input"] + '" selected="selected">' + FHS.fieldData[f]["label"] + '</option>';
		} else {
			html += '<option value="' + FHS.fieldData[f]["field"] + '" data-inputtype="' + FHS.fieldData[f]["input"] + '">' + FHS.fieldData[f]["label"] + '</option>';
		}
	}

	html += '</select> <select name="match[]" class="match">';

	var equivLabels = {
		'equal': 'exactly equals',
		'notequal': 'does not equal',
		'contains': 'contains',
		'notcontains': 'does not contain',
		'start': 'starts with',
		'end': 'ends with'
	};

	for(var e in equivLabels) {
		html += '<option value="' + e + '"';
		if(e == matchTypeVal) {
			html += ' selected="selected"';
		}
		if(e == "contains" || e == "notcontains" || e == "start" || e == "end") {
			html += ' class="disableForSelect"';
		}
		html += ">" + equivLabels[e] + "</option>";
	}

	html += '<input type="text" name="parameter[]" class="parameter" value="' + paramVal + '"><button type="button" class="searchRestrictionDelete"><span class="icon-highlight-remove"></span> Delete</button></li>';

	return html;
}

function FHSearch(params) {
	var FHS = this;

	FHS.page = 1;
	FHS.maxPage = 1;
	FHS.itemsPerPage = 100;
	FHS.pagination = false;

	FHS.container = document.querySelector(params["selector"]);

	FHS.equivLabels = {
		"equal": "exactly equals",
		"notequal": "does not equal",
		"contains": "contains",
		"notcontains": "does not contain",
		"start": "starts with",
		"end": "ends with"
	};

	FHS.fieldData = [];

	FHS.refreshHash = function() {
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
			"page": FHS.page
		};

		window.location.hash = JSON.stringify(hashObject);
	}

	FHS.setPage = function(pageNo) {
		var startOfOldPage = ((FHS.page - 1) * FHS.itemsPerPage) + 1;
		var endOfOldPage = (FHS.page * FHS.itemsPerPage);

		for(var i = startOfOldPage; i <= endOfOldPage; i++) {
			$('#specimen' + i.toString()).css("display", "none");
		}

		FHS.page = pageNo;

		var startOfPage = ((FHS.page - 1) * FHS.itemsPerPage) + 1;
		var endOfPage = (FHS.page * FHS.itemsPerPage);

		for(var i = startOfPage; i <= endOfPage; i++) {
			$("#specimen" + i.toString()).css("display", "block");
		}

		$("#bBeforeBtn").text((FHS.page - 2).toString());
		$("#beforeBtn").text((FHS.page - 1).toString());
		$("#currentPage").text(FHS.page.toString());
		$("#afterBtn").text((FHS.page + 1).toString());
		$("#aAfterBtn").text((FHS.page + 2).toString());

		if(FHS.page == 1) {
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

		if(FHS.page == FHS.maxPage) {
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

		FHS.refreshHash();
	}

	FHS.nextPage = function() {
		if(FHS.page < FHS.maxPage) {
			FHS.setPage(FHS.page + 1);
		}

	}
	FHS.prevPage = function() {
		if(FHS.page > 1) {
			FHS.setPage(FHS.page - 1);
		}
	}
	FHS.firstPage = function() {
		FHS.setPage(1);
	}
	FHS.lastPage = function() {
		FHS.setPage(FHS.maxPage);
	}

	$.get("/fielddata.php", {
		"search": "true"
	}).done(function(data) {
		FHS.fieldData = data;

		// Build the search form dynamically.
		// ---------------------------------------------------

		var searchForm = document.createElement("form"); // <form id="searchForm" name="searchForm" action="/search/" method="post">
		searchForm.setAttribute("id", "searchForm");
		searchForm.setAttribute("name", "searchForm");
		searchForm.setAttribute("method", "post");
		searchForm.setAttribute("action", "/search/");

		var exportType = document.createElement("input"); // <input type="hidden" name="type" id="exportType" value="">
		exportType.setAttribute("type", "hidden");
		exportType.setAttribute("id", "exportType");
		exportType.setAttribute("name", "type");
		exportType.setAttribute("value", "");

		searchForm.appendChild(exportType);

		var searchRestrictions = document.createElement("ul"); // <ul class="searchRestrictions">
		searchRestrictions.setAttribute("class", "searchRestrictions");

		var firstRestriction = document.createElement("li");
		firstRestriction.setAttribute("class", "searchRestriction firstRestriction"); // <li class="searchRestriction firstRestriction">

		var boolean0 = document.createElement("input");
		boolean0.setAttribute("type", "hidden");
		boolean0.setAttribute("id", "boolean0");
		boolean0.setAttribute("name", "boolean[]");
		boolean0.setAttribute("value", "");

		firstRestriction.appendChild(boolean0);

		var field0 = document.createElement("select"); // <select name="field[]" class="field" id="field0">
		field0.setAttribute("id", "field0");
		field0.setAttribute("name", "field[]");
		field0.setAttribute("class", "field");

		for(var f in FHS.fieldData) {
			var opt = document.createElement("option");
			opt.setAttribute("value", FHS.fieldData[f]["field"]);
			opt.setAttribute("data-inputtype", FHS.fieldData[f]["input"]);
			if(FHS.fieldData[f]["field"] === "genus") {
				opt.setAttribute("selected", "selected");
			}
			opt.appendChild(document.createTextNode(FHS.fieldData[f]["label"]));

			field0.appendChild(opt);
		}

		firstRestriction.appendChild(field0);
		firstRestriction.appendChild(document.createTextNode(" "));

		var helpDisplay = document.createElement("div"); // <div class="help" id="helpDisplay"><div></div></div>
		helpDisplay.setAttribute("id", "helpDisplay");
		helpDisplay.setAttribute("class", "help");

		var helpInner = document.createElement("div");
		helpDisplay.appendChild(helpInner);

		firstRestriction.appendChild(helpDisplay);
		firstRestriction.appendChild(document.createTextNode(" "));

		var match0 = document.createElement("select"); // <select name="match[]" class="match" id="match0">
		match0.setAttribute("id", "match0");
		match0.setAttribute("name", "match[]");
		match0.setAttribute("class", "match");

		for(var e in FHS.equivLabels) {
			var option_equiv = document.createElement("option");
			option_equiv.setAttribute("value", e);
			option_equiv.appendChild(document.createTextNode(FHS.equivLabels[e]));

			match0.appendChild(option_equiv);
		}

		firstRestriction.appendChild(match0);

		var parameter0 = document.createElement("input"); // <input type="text" name="parameter[]" class="parameter" id="parameter0">
		parameter0.setAttribute("type", "text");
		parameter0.setAttribute("id", "parameter0");
		parameter0.setAttribute("name", "parameter[]");
		parameter0.setAttribute("class", "parameter");

		firstRestriction.appendChild(parameter0);
		searchRestrictions.appendChild(firstRestriction);
		searchForm.appendChild(searchRestrictions);

		var submitBtn = document.createElement("button"); // <button type="submit" name="submitBtn" onclick="window.search.firstPage();"><span class="icon-search"></span> Search</button>
		submitBtn.setAttribute("type", "submit");
		submitBtn.setAttribute("name", "submitBtn");
		submitBtn.setAttribute("onclick", "window.search.firstPage();");

		var submitBtnIcon = document.createElement("span");
		submitBtnIcon.setAttribute("class", "icon-search");

		submitBtn.appendChild(submitBtnIcon);
		submitBtn.appendChild(document.createTextNode(" Search"));

		searchForm.appendChild(submitBtn);
		searchForm.appendChild(document.createTextNode(" "));

		var addFilter = document.createElement("button");
		addFilter.setAttribute("type", "button");
		addFilter.setAttribute("id", "addFilter");
		addFilter.appendChild(document.createTextNode("+ Add Filter"));

		searchForm.appendChild(addFilter);

		var loading = document.createElement("div");
		loading.setAttribute("id", "loading");
		loading.setAttribute("style", "display: none;");
		loading.appendChild(document.createTextNode("Loading..."));

		searchForm.appendChild(loading);

		var searchResults = document.createElement("div");
		searchResults.setAttribute("id", "searchResults");

		var searchResultsHeader = document.createElement("h2");
		searchResultsHeader.appendChild(document.createTextNode("Search Results "));

		var exportCSV = document.createElement("button");
		exportCSV.setAttribute("type", "button");
		exportCSV.setAttribute("onclick", "$('#exportType').attr('value', 'csv'); $('#searchForm').attr('action', '/export/'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', '/search/'); $('#searchForm').attr('method', 'post');");
		exportCSV.appendChild(document.createTextNode("Export as CSV"));
		searchResultsHeader.appendChild(exportCSV);

		var exportJSON = document.createElement("button");
		exportJSON.setAttribute("type", "button");
		exportJSON.setAttribute("onclick", "$('#exportType').attr('value', 'json'); $('#searchForm').attr('action', '/export/'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', '/search/'); $('#searchForm').attr('method', 'post');");
		exportJSON.appendChild(document.createTextNode("Export as JSON"));
		searchResultsHeader.appendChild(exportJSON);

		var exportXML = document.createElement("button");
		exportXML.setAttribute("type", "button");
		exportXML.setAttribute("onclick", "$('#exportType').attr('value', 'xml'); $('#searchForm').attr('action', '/export/'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', '/search/'); $('#searchForm').attr('method', 'post');");
		exportXML.appendChild(document.createTextNode("Export as XML"));
		searchResultsHeader.appendChild(exportXML);

		searchResults.appendChild(searchResultsHeader);

		var resultsContainer = document.createElement("div");
		resultsContainer.setAttribute("id", "resultsContainer");

		searchResults.appendChild(resultsContainer);
		searchForm.appendChild(searchResults);

		var pagination = document.createElement("ul");
		pagination.setAttribute("id", "pagination");
		pagination.setAttribute("style", "display: none;");

		// Pagination buttons

		var firstPageLI = document.createElement("li");
		var firstPageBtn = document.createElement("button");
		firstPageBtn.setAttribute("type", "button");
		firstPageBtn.setAttribute("onclick", "window.search.firstPage();");
		firstPageBtn.appendChild(document.createTextNode("« First"));

		firstPageLI.appendChild(firstPageBtn);
		pagination.appendChild(firstPageLI);

		var prevPageLI = document.createElement("li");
		var prevPageBtn = document.createElement("button");
		prevPageBtn.setAttribute("type", "button");
		prevPageBtn.setAttribute("onclick", "window.search.prevPage();");
		prevPageBtn.appendChild(document.createTextNode("‹ Previous"));

		prevPageLI.appendChild(prevPageBtn);
		pagination.appendChild(prevPageLI);

		var bBeforeLI = document.createElement("li");
		var bBeforeBtn = document.createElement("button");
		bBeforeBtn.setAttribute("type", "button");
		bBeforeBtn.setAttribute("id", "bBeforeBtn");
		bBeforeBtn.setAttribute("onclick", "window.search.setPage(window.search.page - 2);");

		bBeforeLI.appendChild(bBeforeBtn);
		pagination.appendChild(bBeforeLI);

		var beforeLI = document.createElement("li");
		var beforeBtn = document.createElement("button");
		beforeBtn.setAttribute("type", "button");
		beforeBtn.setAttribute("id", "beforeBtn");
		beforeBtn.setAttribute("onclick", "window.search.prevPage();");

		beforeLI.appendChild(beforeBtn);
		pagination.appendChild(beforeLI);

		var currentPageLI = document.createElement("li");
		var currentPageBtn = document.createElement("button");
		currentPageBtn.setAttribute("type", "button");
		currentPageBtn.setAttribute("id", "currentPage");
		currentPageBtn.setAttribute("class", "active");
		currentPageBtn.setAttribute("onclick", "");
		currentPageBtn.appendChild(document.createTextNode("1"));

		currentPageLI.appendChild(currentPageBtn);
		pagination.appendChild(currentPageLI);

		var afterLI = document.createElement("li");
		var afterBtn = document.createElement("button");
		afterBtn.setAttribute("type", "button");
		afterBtn.setAttribute("id", "afterBtn");
		afterBtn.setAttribute("onclick", "window.search.nextPage();");
		afterBtn.appendChild(document.createTextNode("2"));

		afterLI.appendChild(afterBtn);
		pagination.appendChild(afterLI);

		var aAfterLI = document.createElement("li");
		var aAfterBtn = document.createElement("button");
		aAfterBtn.setAttribute("type", "button");
		aAfterBtn.setAttribute("id", "aAfterBtn");
		aAfterBtn.setAttribute("onclick", "window.search.setPage(window.search.page + 2);");

		aAfterLI.appendChild(aAfterBtn);
		pagination.appendChild(aAfterLI);

		var nextPageLI = document.createElement("li");
		var nextPageBtn = document.createElement("button");
		nextPageBtn.setAttribute("type", "button");
		nextPageBtn.setAttribute("onclick", "window.search.nextPage();");
		nextPageBtn.appendChild(document.createTextNode("Next ›"));

		nextPageLI.appendChild(nextPageBtn);
		pagination.appendChild(nextPageLI);

		var lastPageLI = document.createElement("li");
		var lastPageBtn = document.createElement("button");
		lastPageBtn.setAttribute("type", "button");
		lastPageBtn.setAttribute("onclick", "window.search.lastPage();");
		lastPageBtn.appendChild(document.createTextNode("Last »"));

		lastPageLI.appendChild(lastPageBtn);
		pagination.appendChild(lastPageLI);

		searchForm.appendChild(pagination);

		FHS.container.innerHTML = "";
		FHS.container.appendChild(searchForm);

		// ---------------------------------------------------

		$("#searchResults").css("display", "none");

		function makeSearchResult(searchResultNumber, data) {
			var html = '<a class="specimen" id="specimen' + searchResultNumber.toString() + '" href="/specimen/?id=' + data["web_id"] + '"><h3>' + data["genus"] + ' ' + data["species"] + '</h3><div class="accession"><span>Accession #: </span>' + data["accession"] + '</div><div class="dth"><span>DTH #: </span>' + data["DTH"] + '</div><div class="date_collected"><span>Date Collected: </span>' + data["date_collected"] + '</div></a>';
			return html;
		}

		function addFilter(boolVal, fieldVal, matchTypeVal, paramVal) {
			$('.searchRestrictions').append(buildFilter(fieldData, boolVal, fieldVal, matchTypeVal, paramVal));
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

				$.get("/export.php", {
					"type": "json", 
					"boolean[]": booleans, 
					"field[]": fields, 
					"match[]": matches, 
					"parameter[]": parameters
				}).done(function(data) {
					$('#resultsContainer').html('');
					var numberOfResults = data.length;
					FHS.maxPage = Math.ceil(numberOfResults / FHS.itemsPerPage);

					if(data.length > 0) {
						var i = 1;
						var html = "";
						for(var d in data) {
							html += makeSearchResult(i, data[d]);
							i++;
						}

						$('#resultsContainer').append(html);
					} else {
						$('#resultsContainer').html('<strong>No specimens were found with those parameters.</strong>');
					}

					$('#loading').css('display', 'none');
					$("#searchResults").css("display", "block");

					if(numberOfResults > FHS.itemsPerPage) {
						FHS.pagination = true;
						$('#pagination').css("display", "block");
						$('.specimen').css("display", "none");

						var startOfPage = ((FHS.page - 1) * FHS.itemsPerPage) + 1;
						var endOfPage = (FHS.page * FHS.itemsPerPage);

						for(var i = startOfPage; i <= endOfPage; i++) {
							$("#specimen" + i.toString()).css("display", "block");
						}

						$("#bBeforeBtn").text((FHS.page - 2).toString());
						$("#beforeBtn").text((FHS.page - 1).toString());
						$("#currentPage").text(FHS.page.toString());
						$("#afterBtn").text((FHS.page + 1).toString());
						$("#aAfterBtn").text((FHS.page + 2).toString());

						if(FHS.page == 1) {
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

						if(FHS.page == FHS.maxPage) {
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

					if(numberOfResults > FHS.itemsPerPage) {
						hashObject["page"] = FHS.page;
					}

					window.location.hash = JSON.stringify(hashObject);
				});
			}
		});

		if(window.location.hash != '' && window.location.hash != '#') {
			var searchParams = JSON.parse(window.location.hash.substring(1));

			if(searchParams.hasOwnProperty("page")) {
				FHS.page = searchParams["page"];
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

			$('#searchForm').submit();
		} else if(document.getElementById('parameter0').value != '') {
			$('#searchForm').submit();
		}

		function updateInputTypeOnParameter(obj) {
			var inputType = $(obj).children("option:selected").data("inputtype");
			var fieldName = $(obj).children("option:selected")[0].value;
			var help = $("#helpDisplay");

			for(var f in FHS.fieldData) {
				if(FHS.fieldData[f]["field"] == fieldName) {
					help.children("div").html(FHS.fieldData[f]["help"]);
				}
			}

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
				for(var f in FHS.fieldData) {
					if(FHS.fieldData[f]["field"] == obj.value) {
						var splitOptions = fieldData[f]["options"].split(",");
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
}
