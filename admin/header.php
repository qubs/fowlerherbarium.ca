<html lang="en">
	<head>
		<title>Admin | Fowler Herbarium</title>

		<meta charset="utf-8">

		<link href='https://fonts.googleapis.com/css?family=Playfair+Display:400,700,400italic,700italic'
			  rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,600,700'
			  rel='stylesheet' type='text/css'>

		<link rel="stylesheet" href="style.css">

		<script src="/bower_components/jquery/dist/jquery.min.js"></script>

		<script type="text/javascript" src="/bower_components/tinymce/jquery.tinymce.min.js"></script>
		<script type="text/javascript" src="/bower_components/tinymce/tinymce.min.js"></script>

		<script type="text/javascript">
			$(document).ready(function() {
				tinymce.init({
					selector: ".wysiwyg",
					plugins: "code image media link table template",
					entity_encoding: "raw",
					paste_word_valid_elements: "b,strong,i,em,h1,h2,sup",
					paste_auto_cleanup_on_paste: true,
					paste_use_dialog: true,
					removed_menuitems: "newdocument",
					content_css: "tinymce.css",
					relative_urls: false,
					forced_root_block: "",
					style_formats: [
						{title: "Image Align Left", selector: "img", classes: "alignLeft"},
						{title: "Image Align Right", selector: "img", classes: "alignRight"},

						{title: "Button", selector: "a", classes: "button"}
					]
				});
			});
		</script>
	</head>
	<body>
		<nav>
			<ul>
				<li class="title"><a href="main.php">Fowler Herbarium Admin</a></li>
				<?php if($page != "login") { ?>
				<li<?php if($page == "main") { ?> class="active"<?php } ?>>
					<a href="main.php"><span class="icon-reorder"></span> Entries</a>
				</li>
				<li<?php if($page == "pages") { ?> class="active"<?php } ?>>
					<a href="pages.php"><span class="icon-description"></span> Pages</a>
				</li>
				<li<?php if($page == "users") { ?> class="active"<?php } ?>>
					<a href="users.php"><span class="icon-people"></span> Users</a>
				</li>
				<li<?php if($page == "fields") { ?> class="active"<?php } ?>>
					<a href="fields.php"><span class="icon-reorder"></span> Fields</a>
				</li>
				<li<?php if($page == "usersettings") { ?> class="active"<?php } ?>>
					<a href="usersettings.php"><span class="icon-settings"></span> My Settings</a>
				</li>
				<li class="alignRight">
					<a href="logout.php">Log Out (<?php echo $_SESSION["user"]; ?>)</a>
				</li>
				<?php } ?>
			</ul>
		</nav>
		<section class="main">
