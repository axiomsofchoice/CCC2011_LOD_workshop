<?php
define(DATAFILE, 'data/ccc2.nt');
define(START_RESOURCE, '/camp2011');


include_once ("arc2/ARC2.php");
$parser = ARC2 :: getRDFParser();
$parser->parse(DATAFILE);
$index = $parser->getSimpleIndex(0);
$query = $_REQUEST['q'];
if (empty($query) || $query == "/") {
	$query = START_RESOURCE;
}

$uri = "http://" . $_SERVER["HTTP_HOST"] . $query;

if (empty ($index[$uri])) {
	header("HTTP/1.0 404 Not Found");
	echo "<h1>404 Not Found</h1>";
	echo "The page that you have requested could not be found.";
	exit ();
}

$uridata = array (
	$uri => $index[$uri]
);

$acceptedTypes = getAcceptMimeTypes();

foreach ($acceptedTypes as $type => $q) {
	switch ($type) {
		case "text/html" :
			header("Content-Type: text/html");
			print "<html><head><meta charset='utf-8'>
<link rel='stylesheet' type='text/css' href='http://dbpedia.org/statics/style.css' /> 
									<title>".getLabel($uri)."</title></head><body><div id='header'>
	  <h1 id='title'><a href='$uri'>".getLabel($uri)."</a></h1></div><div id='content'>
<table class='description'><tr><th>Property</th><th>Value</th></tr> ";
			$row = 0;
			foreach ($index[$uri] as $key => $values) {
				$class = ($row % 2 == 0) ? "odd" : "even";
				print "<tr class='$class'><td><a href='$key'>" . getLabel($key) . "</a></td><td><ul>";
				foreach ($values as $value) {
					print "<li>";
					if ($value["type"] == "uri") {
						print "<a href='{$value['value']}'>" . getLabel($value['value']) . "</a>";
					} else {
						print $value['value'];
					}
					print "</li>";
				}
				print "</ul></td></tr>";
				$row++;
			}
			print "</table></div><div id='footer'>Created by the participants of the <a href='http://events.ccc.de/camp/2011/wiki/LinkedData'>Linked Data Workshop</a> at <a href='http://events.ccc.de/camp/2011/'>Chaos Communication Camp 2011</a> using data from the <a href='http://events.ccc.de/camp/2011/Fahrplan/'>Fahrplan event calendar</a>.</body></html>";
			exit;
			break;

		case "text/n3" :
			header("text/n3");
			print $parser->toNTriples($uridata);
			exit;
			break;

		case "application/rdf+xml" :
			header("application/rdf+xml");
			print $parser->toRDFXML($uridata);
			exit;
			break;

	}
}

function getLabel($url) {
	$dbhandle = sqlite_open('labelcache.db');
	if (!sqlite_has_more(sqlite_query($dbhandle, "SELECT name FROM sqlite_master WHERE name='labels'"))) {
		sqlite_query($dbhandle, "CREATE TABLE labels (url,label)");
	}
	$query = sqlite_query($dbhandle, 'SELECT label FROM labels WHERE url="' . $url . '"');
	$result = sqlite_fetch_single($query, SQLITE_ASSOC);
	if (empty ($result)) {
		$label = retrieveLabel($url);
		sqlite_query($dbhandle, "INSERT INTO labels (url,label) VALUES ('$url','".sqlite_escape_string($label)."');");
		return $label;

	} else {
		return $result;
	}
}

function retrieveLabel($url) {
	$parser = ARC2 :: getRDFParser();
	$parser->parse($url);
	$index = $parser->getSimpleIndex(0);
	if (is_array($index[$url]["http://www.w3.org/2000/01/rdf-schema#label"])) {
	foreach ($index[$url]["http://www.w3.org/2000/01/rdf-schema#label"] as $sl) {
		if ($sl['lang'] == "en" || $sl['lang'] == "") {
			$label = $sl['value'];
		}
	}
	}
	if (trim($label) != "") {
		return $label;
	}
	if (strrpos($url, "#") !== false) {
		return substr($url, strrpos($url, "#") + 1);
	}
	if (strrpos($url, "/") !== false) {
		return substr($url, strrpos($url, "/") + 1);
	}
	return $url;
}

function getAcceptMimeTypes() {
	// Values will be stored in this array
	$AcceptTypes = Array ();

	// Accept header is case insensitive, and whitespace isn’t important
	$accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
	// divide it into parts in the place of a ","
	$accept = explode(',', $accept);
	foreach ($accept as $a) {
		// the default quality is 1.
		$q = 1;
		// check if there is a different quality
		if (strpos($a, ';q=')) {
			// divide "mime/type;q=X" into two parts: "mime/type" i "X"
			list ($a, $q) = explode(';q=', $a);
		}
		$AcceptTypes[$a] = $q;
	}
	arsort($AcceptTypes);

	return $AcceptTypes;
}

?>
