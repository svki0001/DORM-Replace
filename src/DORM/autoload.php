<?php
/*
* Autoload für den eigenen Namesspace
*/
spl_autoload_register(function ($className) {

	// ToDo: Prüfen ob ne classe aus dem eigenem namespace kommt.

	$fileName = dirname(__DIR__, 1 ) . '/' .  $className . '.php';

	if (file_exists($fileName)) {
		require_once $fileName;
	}
});

/*
* Autoload für die Tabellen Klassen welche sich nicht im namespace befinden
*/
// spl_autoload_register(function ($className) {
// 	$fileName = dirname(__DIR__, 1) . '/DORM/Models/' .  $className . '.php';

// 	if (file_exists($fileName)) {
// 		require_once $fileName;
// 	}
// });

?>