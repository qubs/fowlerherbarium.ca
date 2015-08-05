<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/shared.php");

	$submitted = false;

	$mysqli = mysqli_connect($_DATABASE_HOST, $_DATABASE_USER, $_DATABASE_PASS, $_DATABASE_DEFAULT);
	if(mysqli_connect_errno()) {
		$message = message(4, "Connect failed: " . mysqli_connect_error());
	}
	$mysqli->set_charset("utf8");

	$fieldData = fetchFieldData($mysqli, true);

	require_once($_SERVER["DOCUMENT_ROOT"] . "/header.php");
?>
<script type="text/javascript" src="/js/search.js"></script>
<h2>Search Database</h2>
<p>
	We here provide a multi-term search engine for our herbarium database. As yet we do not have all records entered, but thus far include approximately 16,000 specimens from Eastern Ontario. Records can be exported in JSON, CSV, or XML format. With time we will include all 144,000 specimens.
</p>
<form id="searchForm" name="searchForm" action="/search/" method="post">
	<input type="hidden" name="type" id="exportType" value="">
	<ul class="searchRestrictions">
		<li class="searchRestriction firstRestriction">
			<input type="hidden" name="boolean[]" id="boolean0" value="">
			<select name="field[]" class="field" id="field0"><?php foreach($fieldData as $f) { echo '<option value="' . $f["field"] . '" data-inputtype="' . $f["input"] . '"'; if($f["field"] == "genus") { echo " selected"; } echo '>' . $f["label"] . '</option>'; } ?></select>
			<div class="help" id="helpDisplay"><div></div></div>
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
		<h2>Search Results <button type="button" onclick="$('#exportType').attr('value', 'csv'); $('#searchForm').attr('action', '/export/'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', '/search/'); $('#searchForm').attr('method', 'post');">Export as CSV</button> <button type="button" onclick="$('#exportType').attr('value', 'json'); $('#searchForm').attr('action', '/export/'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', '/search/'); $('#searchForm').attr('method', 'post');">Export as JSON</button> <button type="button" onclick="$('#exportType').attr('value', 'xml'); $('#searchForm').attr('action', '/export/'); $('#searchForm').attr('method', 'get'); $('#searchForm').submit(); $('#exportType').attr('value', ''); $('#searchForm').attr('action', '/search/'); $('#searchForm').attr('method', 'post');">Export as XML</button></h2>
		<div id="resultsContainer">
		</div>

		<ul id="pagination" style="display: none;">
			<li><button onclick="firstPage();" type="button">&laquo; First</button></li><li><button onclick="prevPage();" id="prevPageBtn" type="button">&#8249; Previous</button></li><li><button id="bBeforeBtn" onclick="setPage(page - 2);" type="button"></button></li><li><button id="beforeBtn" onclick="prevPage();" type="button"></button></li><li><button id="currentPage" class="active" onclick="" type="button">1</button></li><li><button id="afterBtn" onclick="nextPage();" type="button"></button></li><li><button id="aAfterBtn" onclick="setPage(page + 2);" type="button"></button></li><li><button id="nextPageBtn" onclick="nextPage();" type="button">Next &#8250;</button></li><li><button onclick="lastPage();" type="button">Last &raquo;</button></li>
		</ul>
	</div>
</form>
<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/footer.php"); ?>
