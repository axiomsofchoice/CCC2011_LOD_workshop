<?php

require_once('arc2/ARC2.php');


function curl_get($url) {

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	$headers = array();
	$headers[] = 'Accept: text/xml';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	try {
	$content = curl_exec($ch);
	$error_code = curl_errno($ch);
	curl_close($ch);
	} catch (Exception $e) {
	echo " (curl error): ".$e->getMessage().PHP_EOL;
	}
	return $content;

}

function get_annotations($subject) {

	//$topic = $_GET["page"];

	$text = "";

	global $triples;

	foreach($triples as $key=>$triple) {

		if ($triple['s'] == $subject && $triple['p'] == "http://www.w3.org/2000/01/rdf-schema#label") {
			$label = $triple['o'];
		}
		if ($triple['s'] == $subject && $triple['p'] == "http://fahrplan.u0d.de/schema.owl#hasDescription") {
			$description = $triple['o'];
		}
		if (isset($label) && isset($description)) {
			break;
		}

	}


	$annotated = curl_get("http://spotlight.dbpedia.org/rest/annotate?text=".urlencode($label)."&confidence=0.2&support=20");


	$dom = new DOMDocument();
	$dom->loadXML($annotated);

	$xpath = new DOMXPath($dom);
	$tags = $xpath->query('//Resource');
	$urls = array();
	foreach($tags as $tag) {
		array_push($urls, $tag->getAttribute("URI"));
	}
	//return $urls;

	if (sizeof($urls) == 0) {
		

	$annotated = curl_get("http://spotlight.dbpedia.org/rest/annotate?text=".urlencode($description)."&confidence=0.2&support=20");


	$dom = new DOMDocument();
	$dom->loadXML($annotated);

	$xpath = new DOMXPath($dom);
	$tags = $xpath->query('//Resource');
	$urls = array();
	foreach($tags as $tag) {
		array_push($urls, $tag->getAttribute("URI"));
	}
	}

	return $urls;

}

$parser = ARC2::getRDFParser();
$parser->parse("ccc.rdf");
$triples = $parser->getTriples();

$annotations = array();

foreach($triples as $key=>$triple) {

if ($triple['p'] == "http://www.w3.org/1999/02/22-rdf-syntax-ns#type" && $triple['o'] == "http://fahrplan.u0d.de/schema.owl#Lecture") {
	$annotations[$triple['s']] = get_annotations($triple['s']);
}
}

foreach($annotations as $subject=>$annotation) {
	foreach ($annotation as $url) {
		array_push($triples, array('s'=>$subject,
									'p'=>'http://fahrplan.u0d.de/schema.owl#hasTopic',
									'o'=>$url,
									'type'=>'triple',
									's_type'=>'uri',
									'p_type'=>'uri',
									'o_type'=>'uri'));
		}

}

$rdfstring = $parser->toNTriples($triples);

$ohandle = fopen('ccc2.rdf','w');

fwrite($ohandle, $rdfstring);



?>