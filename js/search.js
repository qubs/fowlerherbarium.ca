"use strict";

/**
 * The Fowler Herbarium Search object constructor.
 * @param params A list of parameters for constructing the object.
 * @constructor
 */
var FHSearch = function(params) {
	this.page = 1;
	this.maxPage = 1;
	this.itemsPerPage = 100;
	this.pagination = false;

	this.container = document.querySelector(params["selector"]);

	this.equivLabels = {
		"equal": "exactly equals",
		"notequal": "does not equal",
		"contains": "contains",
		"notcontains": "does not contain",
		"start": "starts with",
		"end": "ends with"
	};

	this.fieldData = [];

	$.get("/fielddata.php", {
		"search": "true"
	}).done(this.processCall.bind(this));
};

FHSearch.prototype.processCall = function(data) {
	var FHS = this;

	this.fieldData = data;

	// Build the search form dynamically.
	// ---------------------------------------------------

	// <form id="searchForm" name="searchForm" action="/search/" method="post">
	var searchForm = document.createElement("form");
	searchForm.setAttribute("id", "searchForm");
	searchForm.setAttribute("name", "searchForm");
	searchForm.setAttribute("method", "post");
	searchForm.setAttribute("action", "/search/");

	// <input type="hidden" name="type" id="exportType" value="">
	var exportType = document.createElement("input");
	exportType.setAttribute("type", "hidden");
	exportType.setAttribute("id", "exportType");
	exportType.setAttribute("name", "type");
	exportType.setAttribute("value", "");

	searchForm.appendChild(exportType);

	// <ul class="searchRestrictions">
	var searchRestrictions = document.createElement("ul");
	searchRestrictions.setAttribute("id", "searchRestrictions");

	// <li class="searchRestriction firstRestriction">
	var firstRestriction = document.createElement("li");
	firstRestriction.setAttribute("class", "searchRestriction firstRestriction");

	var boolean0 = document.createElement("input");
	boolean0.setAttribute("type", "hidden");
	boolean0.setAttribute("id", "boolean0");
	boolean0.setAttribute("name", "boolean[]");
	boolean0.setAttribute("value", "");

	firstRestriction.appendChild(boolean0);

	// <select name="field[]" class="field" id="field0">
	var field0 = document.createElement("select");
	field0.setAttribute("id", "field0");
	field0.setAttribute("name", "field[]");
	field0.setAttribute("class", "field");

	for(var f in this.fieldData) {
		if(this.fieldData.hasOwnProperty(f)) {
			var opt = document.createElement("option");
			opt.setAttribute("value", this.fieldData[f]["field"]);
			opt.setAttribute("data-inputtype", this.fieldData[f]["input"]);
			if (this.fieldData[f]["field"] === "genus") {
				opt.setAttribute("selected", "selected");
			}
			opt.appendChild(document.createTextNode(this.fieldData[f]["label"]));

			field0.appendChild(opt);
		}
	}

	firstRestriction.appendChild(field0);
	firstRestriction.appendChild(document.createTextNode(" "));

	// <div class="help" id="helpDisplay"><div></div></div>
	var helpDisplay = document.createElement("div");
	helpDisplay.setAttribute("id", "helpDisplay");
	helpDisplay.setAttribute("class", "help");

	var helpInner = document.createElement("div");
	helpDisplay.appendChild(helpInner);

	firstRestriction.appendChild(helpDisplay);
	firstRestriction.appendChild(document.createTextNode(" "));

	// <select name="match[]" class="match" id="match0">
	var match0 = document.createElement("select");
	match0.setAttribute("id", "match0");
	match0.setAttribute("name", "match[]");
	match0.setAttribute("class", "match");

	for(var e in this.equivLabels) {
		if(this.equivLabels.hasOwnProperty(e)) {
			var option_equiv = document.createElement("option");
			option_equiv.setAttribute("value", e);
			option_equiv.appendChild(document.createTextNode(this.equivLabels[e]));

			match0.appendChild(option_equiv);
		}
	}

	firstRestriction.appendChild(match0);

	// <input type="text" name="parameter[]" class="parameter" id="parameter0">
	var parameter0 = document.createElement("input");
	parameter0.setAttribute("type", "text");
	parameter0.setAttribute("id", "parameter0");
	parameter0.setAttribute("name", "parameter[]");
	parameter0.setAttribute("class", "parameter");

	firstRestriction.appendChild(parameter0);
	searchRestrictions.appendChild(firstRestriction);
	searchForm.appendChild(searchRestrictions);

	// <button type="submit" name="submitBtn" onclick="window.search.firstPage();">
	//     <span class="icon-search"></span> Search
	// </button>
	var submitBtn = document.createElement("button");
	submitBtn.setAttribute("type", "submit");
	submitBtn.setAttribute("name", "submitBtn");
	submitBtn.setAttribute("onclick", "window.search.firstPage();");

	var submitBtnIcon = document.createElement("span");
	submitBtnIcon.setAttribute("class", "icon-search");

	submitBtn.appendChild(submitBtnIcon);
	submitBtn.appendChild(document.createTextNode(" Search"));

	searchForm.appendChild(submitBtn);
	searchForm.appendChild(document.createTextNode(" "));

	var addFilterBtn = document.createElement("button");
	addFilterBtn.setAttribute("type", "button");
	addFilterBtn.setAttribute("id", "addFilter");
	addFilterBtn.appendChild(document.createTextNode("+ Add Filter"));

	searchForm.appendChild(addFilterBtn);

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
	exportCSV.addEventListener("click", function () {
		$('#exportType').attr('value', 'csv');
		$('#searchForm').attr('action', '/export/');
		$('#searchForm').attr('method', 'get');
		$('#searchForm').submit();

		$('#exportType').attr('value', '');
		$('#searchForm').attr('action', '/search/');
		$('#searchForm').attr('method', 'post');
	});
	exportCSV.appendChild(document.createTextNode("Export as CSV"));
	searchResultsHeader.appendChild(exportCSV);

	var exportJSON = document.createElement("button");
	exportJSON.setAttribute("type", "button");
	exportJSON.addEventListener("click", function () {
		$('#exportType').attr('value', 'json');
		$('#searchForm').attr('action', '/export/');
		$('#searchForm').attr('method', 'get');
		$('#searchForm').submit();

		$('#exportType').attr('value', '');
		$('#searchForm').attr('action', '/search/');
		$('#searchForm').attr('method', 'post');
	});
	exportJSON.appendChild(document.createTextNode("Export as JSON"));
	searchResultsHeader.appendChild(exportJSON);

	var exportXML = document.createElement("button");
	exportXML.setAttribute("type", "button");
	exportXML.addEventListener("click", function () {
		$('#exportType').attr('value', 'xml');
		$('#searchForm').attr('action', '/export/');
		$('#searchForm').attr('method', 'get');
		$('#searchForm').submit();

		$('#exportType').attr('value', '');
		$('#searchForm').attr('action', '/search/');
		$('#searchForm').attr('method', 'post');
	});
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
	prevPageBtn.setAttribute("id", "prevPageBtn");
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
	nextPageBtn.setAttribute("id", "nextPageBtn");
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

	this.container.innerHTML = "";
	this.container.appendChild(searchForm);

	// ---------------------------------------------------

	this.searchForm = document.getElementById("searchForm");
	this.searchRestrictions = document.getElementById("searchRestrictions");
	this.resultsContainer = document.getElementById("resultsContainer");
	this.searchResults = document.getElementById("searchResults");

	this.loadingAnimation = document.getElementById("loading");

	// ---------------------------------------------------

	this.searchResults.style.display = "none";

	function makeSearchResult(searchResultNumber, data) {
		var specimen = document.createElement("a");
		specimen.setAttribute("id", "specimen" + searchResultNumber.toString());
		specimen.setAttribute("class", "specimen");
		specimen.setAttribute("href", "/specimen/" + data["web_id"] + "/");

		var specimenTitle = document.createElement("h3");
		specimenTitle.appendChild(document.createTextNode(data["genus"] + " " + data["species"]));
		specimen.appendChild(specimenTitle);

		var accession = document.createElement("div");
		accession.setAttribute("class", "accession");
		var accessionLabel = document.createElement("span");
		accessionLabel.appendChild(document.createTextNode("Accession #: "));
		accession.appendChild(accessionLabel);
		accession.appendChild(document.createTextNode(data["accession"]));
		specimen.appendChild(accession);

		var dth = document.createElement("div");
		dth.setAttribute("class", "dth");
		var dthLabel = document.createElement("span");
		dthLabel.appendChild(document.createTextNode("DTH #: "));
		dth.appendChild(dthLabel);
		dth.appendChild(document.createTextNode(data["DTH"]));
		specimen.appendChild(dth);

		var dateCollected = document.createElement("div");
		dateCollected.setAttribute("class", "date_collected");
		var dateCollectedLabel = document.createElement("span");
		dateCollectedLabel.appendChild(document.createTextNode("Date Collected: "));
		dateCollected.appendChild(dateCollectedLabel);
		dateCollected.appendChild(document.createTextNode(data["date_collected"]));
		specimen.appendChild(dateCollected);

		return specimen;
	}

	// ---------------------------------------------------

	$(this.searchForm).submit((function(e) {
		if(document.getElementById("exportType").value == "") {
			e.preventDefault();

			this.loadingAnimation.style.display = "block";

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

			var processExport = (function(data) {
				this.resultsContainer.innerHTML = "";

				var numberOfResults = data.length;
				this.maxPage = Math.ceil(numberOfResults / this.itemsPerPage);

				if(data.length > 0) {
					var i = 1;
					for(var d in data) {
						this.resultsContainer.appendChild(makeSearchResult(i, data[d]));
						i++;
					}
				} else {
					this.resultsContainer.innerHTML = "<strong>No specimens were found with those parameters.</strong>";
				}

				this.loadingAnimation.style.display = "none";
				this.searchResults.style.display = "block";

				if(numberOfResults > this.itemsPerPage) {
					this.pagination = true;
					$("#pagination").css("display", "block");
					$(".specimen").css("display", "none");

					var startOfPage = ((this.page - 1) * this.itemsPerPage) + 1;
					var endOfPage = (this.page * this.itemsPerPage);

					for(var i = startOfPage; i <= endOfPage; i++) {
						$("#specimen" + i.toString()).css("display", "block");
					}

					$("#bBeforeBtn").text((this.page - 2).toString());
					$("#beforeBtn").text((this.page - 1).toString());
					$("#currentPage").text(this.page.toString());
					$("#afterBtn").text((this.page + 1).toString());
					$("#aAfterBtn").text((this.page + 2).toString());

					if(this.page == 1) {
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

					if(this.page == this.maxPage) {
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

					// Special conditions for 2 pages

					if(this.page == 1 && this.maxPage == 2) {
						$("#aAfterBtn").css("display", "none");
						$("#aAfterBtn").parent().css("borderRightWidth", "0");
					} else if(this.page == 2 && this.maxPage == 2) {
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

				if(numberOfResults > this.itemsPerPage) {
					hashObject["page"] = this.page;
				}

				window.location.hash = JSON.stringify(hashObject);
			}).bind(this);

			$.get("/export/", {
				"type": "json",
				"boolean[]": booleans,
				"field[]": fields,
				"match[]": matches,
				"parameter[]": parameters
			}).done(processExport);
		}
	}).bind(this));

	if(window.location.hash != "" && window.location.hash != "#") {
		var searchParams = JSON.parse(window.location.hash.substring(1));

		if(searchParams.hasOwnProperty("page")) {
			this.page = searchParams["page"];
		}

		for(var i = 0; i < searchParams["boolean[]"].length; i++) {
			if(i == 0) {
				document.getElementById("boolean0").value = searchParams["boolean[]"][i];
				document.getElementById("field0").value = searchParams["field[]"][i];
				document.getElementById("match0").value = searchParams["match[]"][i];
				document.getElementById("parameter0").value = searchParams["parameter[]"][i];
			} else {
				this.addFilter(searchParams["boolean[]"][i], searchParams["field[]"][i], searchParams["match[]"][i],
					searchParams["parameter[]"][i])
			}
		}

		$(this.searchForm).submit();
	} else if(document.getElementById("parameter0").value != "") {
		$(this.searchForm).submit();
	}

	function updateInputTypeOnParameter(obj) {
		var inputType = $(obj).children("option:selected").data("inputtype");
		var fieldName = $(obj).children("option:selected")[0].value;
		var help = $("#helpDisplay");

		for(var f in FHS.fieldData) {
			if(FHS.fieldData.hasOwnProperty(f)) {
				if (FHS.fieldData[f]["field"] == fieldName) {
					help.children("div").html(FHS.fieldData[f]["help"]);
				}
			}
		}

		if(inputType == "select") {
			var include0id = false;
			if($(obj).parent().children(".parameter")[0].id == "parameter0") {
				include0id = true;
			}

			$(obj).parent().children(".parameter").remove();

			var parameterSelect = document.createElement("select");
			parameterSelect.setAttribute("name", "parameter[]");
			parameterSelect.setAttribute("class", "parameter select");
			if(include0id) {
				parameterSelect.setAttribute("id", "parameter0");
			}

			for(var f in FHS.fieldData) {
				if(FHS.fieldData.hasOwnProperty(f)) {
					if (FHS.fieldData[f]["field"] === obj.value) {
						var splitOptions = FHS.fieldData[f]["options"].split(",");
						for (var o in splitOptions) {
							var parameterOption = document.createElement("option");
							parameterOption.setAttribute("value", splitOptions[o]);
							parameterOption.appendChild(document.createTextNode(splitOptions[o]));
							parameterSelect.appendChild(parameterOption);
						}
					}
				}
			}

			$(obj).parent()[0].appendChild(parameterSelect);
			$(obj).parent().children("select").children(".disableForSelect").each(function() {
				$(this).attr("disabled", "disabled");
			});
		} else {
			if($(obj).parent().children(".parameter").hasClass("select")) {
				$(obj).parent().children(".parameter").remove();

				var input = document.createElement("input");
				input.setAttribute("type", "text");
				input.setAttribute("name", "parameter[]");
				input.setAttribute("class", "parameter");

				$(obj).parent()[0].appendChild(input);
			}
			$(obj).parent().children(".parameter").attr("type", inputType);

			$(obj).parent().children("select").children(".disableForSelect").each(function() {
				$(this).removeAttr("disabled");
			});
		}
	}

	$(".field").change(function() {
		updateInputTypeOnParameter(this);
	});
	$(".field").change();

	$("#addFilter").click(function() {
		FHS.addFilter();

		$(".searchRestrictionDelete").click(function() {
			$(this).parent().remove();
		});
	});
	$(".searchRestrictionDelete").click(function() {
		$(this).parent().remove();
	});

	$(this.searchRestrictions).sortable({
		items: "li:not(.firstRestriction)"
	});
};

/**
 * Moves the search results object to the next page.
 */
FHSearch.prototype.nextPage = function() {
	if(this.page < this.maxPage) {
		this.setPage(this.page + 1);
	}

};

/**
 * Moves the search results object to the previous page.
 */
FHSearch.prototype.prevPage = function() {
	if(this.page > 1) {
		this.setPage(this.page - 1);
	}
};

/**
 * Moves the search results object to the first page.
 */
FHSearch.prototype.firstPage = function() {
	this.setPage(1);
};

/**
 * Moves the search results object to the last page.
 */
FHSearch.prototype.lastPage = function() {
	this.setPage(this.maxPage);
};

/**
 * Refreshes the results URL hash.
 */
FHSearch.prototype.refreshHash = function() {
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
		"page": this.page
	};

	window.location.hash = JSON.stringify(hashObject);
};

/**
 * Sets the search results object's current page.
 * @param pageNo The page to move to.
 */
FHSearch.prototype.setPage = function(pageNo) {
	var startOfOldPage = ((this.page - 1) * this.itemsPerPage) + 1;
	var endOfOldPage = (this.page * this.itemsPerPage);

	for(var o = startOfOldPage; o <= endOfOldPage; o++) {
		if(document.getElementById("specimen" + o.toString()) !== null) {
			document.getElementById("specimen" + o.toString()).style.display = "none";
		}
	}

	this.page = pageNo;

	var startOfPage = ((this.page - 1) * this.itemsPerPage) + 1;
	var endOfPage = (this.page * this.itemsPerPage);

	for(var p = startOfPage; p <= endOfPage; p++) {
		if(document.getElementById("specimen" + p.toString()) !== null) {
			document.getElementById("specimen" + p.toString()).style.display = "block";
		}
	}

	$("#bBeforeBtn").text((this.page - 2).toString());
	$("#beforeBtn").text((this.page - 1).toString());
	$("#currentPage").text(this.page.toString());
	$("#afterBtn").text((this.page + 1).toString());
	$("#aAfterBtn").text((this.page + 2).toString());

	if(this.page == 1) {
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

	if(this.page == this.maxPage) {
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

	// Special conditions for 2 pages

	if(this.page == 1 && this.maxPage == 2) {
		$("#aAfterBtn").css("display", "none");
		$("#aAfterBtn").parent().css("borderRightWidth", "0");
	} else if(this.page == 2 && this.maxPage == 2) {
		$("#bBeforeBtn").css("display", "none");
		$("#bBeforeBtn").parent().css("borderRightWidth", "0");
	}

	this.refreshHash();
};

/**
 * Builds a new filter DOM object for the search form.
 * @param boolVal
 * @param fieldVal
 * @param matchTypeVal
 * @param paramVal
 * @returns {Element}
 */
FHSearch.prototype.buildFilter = function(boolVal, fieldVal, matchTypeVal, paramVal) {
	boolVal = typeof boolVal !== "undefined" ? boolVal : "AND";
	fieldVal = typeof fieldVal !== "undefined" ? fieldVal : "genus";
	matchTypeVal = typeof matchTypeVal !== "undefined" ? matchTypeVal : "equal";
	paramVal = typeof paramVal !== "undefined" ? paramVal : "";

	// Create search restriction element and subelements

	var searchRestriction = document.createElement("li");
	searchRestriction.setAttribute("class", "searchRestriction");

	var boolSelect = document.createElement("select");
	boolSelect.setAttribute("name", "boolean[]");
	boolSelect.setAttribute("class", "andOr");

	var boolAnd = document.createElement("option");
	boolAnd.setAttribute("value", "AND");
	boolAnd.appendChild(document.createTextNode("AND"));

	var boolOr = document.createElement("option");
	boolOr.setAttribute("value", "OR");
	boolOr.appendChild(document.createTextNode("OR"));

	if(boolVal === "OR") {
		boolOr.setAttribute("selected", "selected");
	} else {
		boolAnd.setAttribute("selected", "selected");
	}

	boolSelect.appendChild(boolAnd);
	boolSelect.appendChild(boolOr);

	searchRestriction.appendChild(boolSelect);
	searchRestriction.appendChild(document.createTextNode(" "));

	var boolSpacing = document.createElement("div");
	boolSpacing.setAttribute("class", "booleanSpacing");
	boolSpacing.appendChild(document.createTextNode("⋮"));

	searchRestriction.appendChild(boolSpacing);

	var fieldSelect = document.createElement("select");
	fieldSelect.setAttribute("name", "field[]");
	fieldSelect.setAttribute("class", "field");

	for(var f in this.fieldData) {
		if(this.fieldData.hasOwnProperty(f)) {
			var fieldOption = document.createElement("option");
			fieldOption.setAttribute("value", this.fieldData[f]["field"]);
			fieldOption.setAttribute("data-inputtype", this.fieldData[f]["input"]);

			if (this.fieldData[f]["field"] == fieldVal) {
				fieldOption.setAttribute("selected", "selected");
			}

			fieldOption.appendChild(document.createTextNode(this.fieldData[f]["label"]));
			fieldSelect.appendChild(fieldOption);
		}
	}

	searchRestriction.appendChild(fieldSelect);
	searchRestriction.appendChild(document.createTextNode(" "));

	var matchSelect = document.createElement("select");
	matchSelect.setAttribute("name", "match[]");
	matchSelect.setAttribute("class", "match");

	for(var e in this.equivLabels) {
		if(this.equivLabels.hasOwnProperty(e)) {
			var matchOption = document.createElement("option");
			matchOption.setAttribute("value", e);
			if (e == matchTypeVal) {
				matchOption.setAttribute("selected", "selected");
			}

			if (e === "contains" || e === "notcontains" || e === "start" || e === "end") {
				matchOption.setAttribute("class", "disableForSelect");
			}

			matchOption.appendChild(document.createTextNode(this.equivLabels[e]));
			matchSelect.appendChild(matchOption);
		}
	}

	searchRestriction.appendChild(matchSelect);
	searchRestriction.appendChild(document.createTextNode(" "));

	var parameterInput = document.createElement("input");
	parameterInput.setAttribute("type", "text");
	parameterInput.setAttribute("name", "parameter[]");
	parameterInput.setAttribute("class", "parameter");
	parameterInput.setAttribute("value", paramVal);

	searchRestriction.appendChild(parameterInput);

	var searchRestrictionDelete = document.createElement("button");
	searchRestrictionDelete.setAttribute("type", "button");
	searchRestrictionDelete.setAttribute("class", "searchRestrictionDelete");

	var searchRestrictionDeleteIcon = document.createElement("span");
	searchRestrictionDeleteIcon.setAttribute("class", "icon-highlight-remove");

	searchRestrictionDelete.appendChild(searchRestrictionDeleteIcon);
	searchRestrictionDelete.appendChild(document.createTextNode(" Delete"));
	searchRestriction.appendChild(searchRestrictionDelete);

	return searchRestriction;
};

/**
 * Adds a filter to the search form.
 * @param boolVal
 * @param fieldVal
 * @param matchTypeVal
 * @param paramVal
 */
FHSearch.prototype.addFilter = function(boolVal, fieldVal, matchTypeVal, paramVal) {
	this.searchRestrictions.appendChild(this.buildFilter(boolVal, fieldVal, matchTypeVal, paramVal));
};
