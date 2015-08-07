<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/config.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/shared.php");
	require_once($_SERVER["DOCUMENT_ROOT"] . "/lib/Twig/Autoloader.php");

	Twig_Autoloader::register();

	$loader = new Twig_Loader_Filesystem($_SERVER["DOCUMENT_ROOT"] . "/templates");
	$twig = new Twig_Environment($loader);
?>
