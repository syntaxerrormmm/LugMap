<?php

require_once ('../../varie.php');

init_geocache ();
global $geocache;

$current_year = date ('Y');
$current_month = date ('m');
$current_day = date ('d');

$year = $current_year;
$month = date ('m', strtotime ('-1 month'));
$day = date ('d', strtotime ('-1 month'));

if ($month > $current_month)
	$year = $year - 1;

$events = file ("$year.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

for ($events_index = 0; $events_index < count ($events); $events_index++) {
	$row = $events [$events_index];
	list ($date, $useless) = explode ('|', $row);
	list ($d, $m) = explode ('/', $date);

	if ($m < $month || ($m == $month && $d < $day)) {
		$events_index--;
		break;
	}
}

$found_cities = array ();
$icon = "past_events.png";
$rows = array ("lat\tlon\ttitle\tdescription\ticonSize\ticonOffset\ticon");

while (true) {
	$row = $events [$events_index];
	list ($date, $useless, $location, $what, $url) = explode ('|', $row);
	list ($d, $m) = explode ('/', $date);

	$c = str_replace (' ', '%20', $location);

	$result = ask_coordinates ($c);
	if ($result == null)
		continue;

	list ($lat, $lon) = $result;

	/*
		Questo e' per evitare che due punti si sovrappongano, quelli che vengono
		trovati nella stessa citta' (e dunque alle stesse coordinate) vengono
		arbitrariamente shiftati
	*/
	$occurrences = howMany ($location, $found_cities);
	if ($occurrences != 0)
		$lon = $lon + (3000 * $occurrences);

	$found_cities [] = $location;

	if (($d > $current_day && $m == $current_month && $year == $current_year) || ($m > $current_month && $year == $current_year) || ($year > $current_year))
		$icon = "icon.png";

	$rows [] = "$lat\t$lon\t$d/$m\t<a href=\"$url\">$what</a>\t16,19\t-8,-19\thttp://lugmap.it/images/$icon";

	$events_index--;

	if ($events_index == -1) {
		$year++;

		if (file_exists ("$year.txt"))
			$events = file ("$year.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		else
			break;

		$events_index = count ($events) - 1;
	}
}

if (file_put_contents ('geoevents.txt', join ("\n", $rows) . "\n") === false)
	echo "Errore nel salvataggio del file\n";
else
	echo "I dati sono stati scritti nel file 'geoevents.txt'\n";

save_geocache ();

?>

