<?php


function loadSchema($location) {
	$xml = new DOMDocument();
	// Check to see if xml document has been create, if not, create xml file.
	if (!file_exists($location)) {
		$xml->version = "1.0";
		$xml->encoding = "UTF-8";
		$fb_elem = $xml->createElement("facebook");
		$n_elem = $xml->createElement("sample_size","0.1");
		$fb_elem->appendChild($n_elem);
		$paging_elem = $xml->createElement("paging");
		$paging_elem->appendChild($xml->createElement("friends","-1"));
		$paging_elem->appendChild($xml->createElement("comments","-1"));
		$paging_elem->appendChild($xml->createElement("feed","-1"));
		$paging_elem->appendChild($xml->createElement("photos","-1"));
		$paging_elem->appendChild($xml->createElement("id","-1"));
		$fb_elem->appendChild($paging_elem);
		$xml->appendChild($fb_elem);

		$xml->save($location);
	} else {
		$xml->load($location);
	}
	return $xml;
}
function saveSchema($location, $schema) {
	$xml = new DOMDocument("1.0","UTF-8");
	$fb_elem = $xml->createElement("facebook");
	$n_elem = $xml->createElement("sample_size",$schema["sample_size"]);
	$fb_elem->appendChild($n_elem);
	$paging_elem = $xml->createElement("paging");
	$paging_elem->appendChild($xml->createElement("friends",$schema["friends"]));
	$paging_elem->appendChild($xml->createElement("comments",$schema["comments"]));
	$paging_elem->appendChild($xml->createElement("feed",$schema["feed"]));
	$paging_elem->appendChild($xml->createElement("photos",$schema["photos"]));
	$paging_elem->appendChild($xml->createElement("id",$schema["id"]));
	$fb_elem->appendChild($paging_elem);
	$xml->appendChild($fb_elem);

	$xml->save($location);
}

function getSchema($location) {

	$schema = array();
	$xml = loadSchema($location);
	// facebook/sample_size
	$searchNodes = $xml->getElementsByTagName("sample_size"); 
	$schema[$searchNodes->item(0)->nodeName] = $searchNodes->item(0)->nodeValue;

	// facebook/paging
	$searchNodes = $xml->getElementsByTagName("paging");

	// facebook/sample_size/friends
	$searchNode = $searchNodes->item(0)->getElementsByTagName("friends");
	$schema[$searchNode->item(0)->nodeName] = $searchNode->item(0)->nodeValue;

	// facebook/sample_size/comments
	$searchNode = $searchNodes->item(0)->getElementsByTagName("comments");
	$schema[$searchNode->item(0)->nodeName] = $searchNode->item(0)->nodeValue;

	// facebook/sample_size/feed
	$searchNode = $searchNodes->item(0)->getElementsByTagName("feed");
	$schema[$searchNode->item(0)->nodeName] = $searchNode->item(0)->nodeValue;

	// facebook/sample_size/photos
	$searchNode = $searchNodes->item(0)->getElementsByTagName("photos");
	$schema[$searchNode->item(0)->nodeName] = $searchNode->item(0)->nodeValue;

	// facebook/sample_size/id
	$searchNode = $searchNodes->item(0)->getElementsByTagName("id");
	$schema[$searchNode->item(0)->nodeName] = $searchNode->item(0)->nodeValue;

	return $schema;
}

$location = "schema.xml"; // Location of xml schema document


// Check to see if schema file needs to be updated
if (!empty($_GET)) {
	$get_schema = array("sample_size" => $_GET["sample_size"],"friends" => $_GET["friends"],"comments" => $_GET["comments"],"feed" => $_GET["feed"],"photos" => $_GET["photos"],"id" => $_GET["id"]);
	$old_schema = getSchema($location);
	$new_schema = $old_schema;
	$change = false;
	foreach ($get_schema as $key=>$value) {
		if (isset($get_schema[$key]) && !(trim($get_schema[$key])==='') && !($old_schema[$key] == $get_schema[$key])) {
			$new_schema[$key] = $value;
			$change = true;
		}
	}
	if ($change) {
		saveSchema($location,$new_schema);
		echo "Schema updated.<br/>";
		foreach ($new_schema as $key=>$value) {
			echo $key . ": " . $value . "<br/>";
		}
		echo "<br/>";
	}
}

//  /  /  /  /  /  /  /  /  / Activity
$schema = getSchema($location);
echo "<br/>";
echo '<div style="border-style: solid;border-width: 5px;width: 200px;"><h2>Edit Schema</h2><form action="xml_test.php" method="get">';
echo 'Sample size: <input type="text" name="sample_size" placeholder="' . $schema["sample_size"] . '"><br/>';
echo 'Friends paging: <input type="text" name="friends" placeholder="' . $schema["friends"] . '"><br/>';
echo 'Comments paging: <input type="text" name="comments" placeholder="' . $schema["comments"] . '"><br/>';
echo 'Feed paging: <input type="text" name="feed" placeholder="' . $schema["feed"] . '"><br/>';
echo 'Photos paging: <input type="text" name="photos" placeholder="' . $schema["photos"] . '"><br/>';
echo 'ID paging: <input type="text" name="id" placeholder="' . $schema["id"] . '"><br/>';
echo '<input type="submit"></form></div>';
//  \  \  \  \  \  \  \  \  \

// Ignore below
$friend_list = ['0'=>['id'=>'123','name'=>'Rob'],'1'=>['id'=>'789','name'=>'Bor']];
echo "<br/>";

?>