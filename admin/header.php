<html lang="en">
	<head>
		<title>Admin | Fowler Herbarium</title>

		<meta charset="utf-8">

		<link href='http://fonts.googleapis.com/css?family=Playfair+Display:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,600,700' rel='stylesheet' type='text/css'>

		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<nav>
			<ul>
				<li class="title"><a href="main.php">Fowler Herbarium Admin</a></li>
				<li<?php if($page == "main") { ?> class="active"<?php } ?>><a href="main.php"><span class="icon-reorder"></span> Entries</a></li>
				<li<?php if($page == "users") { ?> class="active"<?php } ?>><a href="users.php"><span class="icon-people"></span> Users</a></li>
				<li<?php if($page == "usersettings") { ?> class="active"<?php } ?>><a href="usersettings.php"><span class="icon-settings"></span> My Settings</a></li>
				<li class="alignRight"><a href="logout.php">Log Out<?php if($page != "login") { ?> (<?php echo $_SESSION['user']; ?>)<?php } ?></a></li>
			</ul>
		</nav>
		<section class="main">