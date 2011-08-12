<?php

define(DATAFILE,'data/result.rdf');

include_once ("arc2/ARC2.php");
$parser = ARC2 :: getRDFParser();
$parser->parse(DATAFILE);
$index = $parser->getSimpleIndex(0);
$uri = "http://" . $_SERVER["HTTP_HOST"] . $_REQUEST['q'];

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
		// mime-type $a is accepted with the quality $q
		// WARNING: $q == 0 means, that mime-type isn’t supported!
		$AcceptTypes[$a] = $q;
	}
	arsort($AcceptTypes);

	return $AcceptTypes;
}

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
			print "<html><head><title>Description of $uri</title></head><body><h1>Description of $uri</h1><table>";
			foreach ($index[$uri] as $key => $values) {
				print "<tr><td>$key</td><td>";
				foreach ($values as $value) {
					if ($value["type"] == "uri") {
						print "<a href='{$value['value']}'>{$value['value']}</a>";
					} else {
						print $value['value'];
					}
				}
				print "</td></tr>";
			}
			print "</table></body></html>";
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
#print(htmlentities));
?>
