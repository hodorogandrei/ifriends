<?php

// TO DO:
// Add paging to inbox

// Look at bottom for code that executes the subsequent class methods.

// import the specified classes to the current scope

 require('php-sdk/src/Facebook/autoload.php');

use Facebook\FacebookSession;
//use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookRequest;
//use Facebook\FacebookRequestException;
use Facebook\FacebookResponse;
//use Facebook\FacebookSDKException;
//use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
//use Facebook\GraphUser;

FacebookSession::setDefaultApplication('1601241650112745', '100f579df0b4cb8eaa39214c0f8228e3');

$session = new FacebookSession($_POST["0"]);

function getNameFromID($user_id) {
		global $session;
		$url = "/" . $user_id . "/";
		$userInfo = (new FacebookRequest( $session, 'GET', $url ))->execute()->getGraphObject()->asArray();
		$user_name = $userInfo["name"];
		return $user_name;
}

class Settings {
	private $schema = null;
	private function getFile($location) {
		$xml = new DOMDocument();
		// Check to see if xml document has been create, if not, create xml file.
		if (!file_exists($location)) {
			$xml->version = "1.0";
			$xml->encoding = "UTF-8";
			$fb_elem = $xml->createElement("facebook");
			$paging_elem = $xml->createElement("paging");
			$paging_elem->appendChild($xml->createElement("friends","2"));
			$paging_elem->appendChild($xml->createElement("comments","1"));
			$paging_elem->appendChild($xml->createElement("feed","1"));
			$paging_elem->appendChild($xml->createElement("photos","1"));
			$paging_elem->appendChild($xml->createElement("id","1"));
			$fb_elem->appendChild($paging_elem);
			$xml->appendChild($fb_elem);

			$xml->save($location);
		} else {
			$xml->load($location);
		}
		return $xml;
	}
	private function loadSchema($location) {
		$schema = array();
		$xml = $this->getFile($location);
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
	public function getSchema($location) {
		if ($location == "") {
			$location = "schema.xml"; // Default name/location
		}

		if (is_null($this->schema)) { // If the class hasn't already got the schema in memory it loads it from disk
			$this->schema = $this->loadSchema($location);
		}
		return $this->schema;
	}
}

class FB {
	public function __construct($token) {
		$this->token = $token;
		$this->settings = new Settings();
		$this->paging = $this->settings->getSchema(""); // Array ( [friends] => -1 [comments] => -1 [feed] => -1 [photos] => -1 [id] => -1 )
		
		$this->session = new FacebookSession($token);
	}
	public function getProfile($user_id, $params) {
		$url = "/" . $user_id . "?fields=" . $params;
		$result = (new FacebookRequest( $this->session, 'GET', $url ))->execute()->getGraphObject()->asArray();
		unset($result["id"]);
		/*
		echo " " . $user_id . ", " . $params . " ";
		print_r($result);
		echo "<br>";
		*/
		return $result;
	}
	public function getInbox($user_id_1, $user_id_2, $params) {
		$url = "/" . $user_id_1 . "/inbox?fields=to{id}," . $params;
		$result = (new FacebookRequest( $this->session, 'GET', $url ))->execute()->getGraphObject()->asArray();
		//echo " <b>" . $url . "</b> " . $params . " <br>";
		//echo "<pre>";
		//print_r($result);
		//echo "</pre>";
		$found = false;
		// Individual Conversations: $result['data']
		foreach ($result['data'] as $key=>$conversation) {
			if (count($conversation->to->data) > 2) {
				continue;
			}
			foreach ($conversation->to->data as $key=>$user) {
				if ($user_id_2 == $user->id){
					$found = true;
					// echo "<b>Found</b><br>";
					break;
				}
			}
			if ($found) {
				/*
				$result = array();
				$params = explode(",",$params);
				foreach ($params as $id=>$param) {
					$result[$param] = $conversation->{$param};
				}
				*/
				$result = $conversation;
				/*
				echo "<br>result<pre>";
				print_r($result);
				echo "</pre><br>";
				*/
				return $result;
			}
		}
		if (!$found) {
			return null;
		}
		echo "Error: FB->getInbox() result found but not returned";
		return null;
	}
	public function getId($id, $params) {
		$url = "/" . $id . "" . $params;
		$result = (new FacebookRequest( $this->session, 'GET', $url ))->execute()->getGraphObject()->asArray();
		unset($result["id"]);
		//echo " " . $params . " ";
		//print_r($result);
		return $result;
	}
	public function checkFriends($user_id_1, $user_id_2) {
		$url = "/" . $user_id_1 . "/friends/" . $user_id_2;
		$result = (new FacebookRequest( $this->session, 'GET', $url ))->execute()->getGraphObject()->asArray();
		if (count($result[data]) == 0) {
			return false;
		} else {
			return true;
		}
		return $result;
	}
	public function getFriends($user_id, $params) {
		$url = "/" . $user_id . "/friends?fields=" . $params;
		$result = (new FacebookRequest( $this->session, 'GET', $url ))->execute()->getGraphObject()->asArray();
		/*
		echo " " . $params . " ";
		print_r($result);
		echo "<br>";
		*/
		return $result;
	}
}

class Functions {
	public function __construct($token, $variables) {
		$this->fb = new FB($token);
		$this->variables = $variables;
	}
	public function run($user_id, $friend_id) {
		$values = array();
		
		if (in_array("daysSinceLastCommunication",$this->variables)) {
			$values["daysSinceLastCommunication"] = $this->daysSinceLastCommunication($user_id,$friend_id);
		}
		if (in_array("daysSinceFirstCommunication",$this->variables)) {
			$values["daysSinceFirstCommunication"] = $this->daysSinceFirstCommunication($user_id,$friend_id);
		}
		if (in_array("differenceBetweenTimezones",$this->variables)) {
			$values["differenceBetweenTimezones"] = $this->differenceBetweenTimezones($user_id,$friend_id); // cannot get friend timezone
		}
		if (in_array("distanceBetweenHometowns",$this->variables)) {
			$values["distanceBetweenHometowns"] = $this->distanceBetweenHometowns($user_id,$friend_id);
		}
		if (in_array("politicalDifference",$this->variables)) {
			$values["politicalDifference"] = $this->politicalDifference($user_id,$friend_id);
		}
		if (in_array("numberOfMutualFriends",$this->variables)) {
			$values["numberOfMutualFriends"] = $this->numberOfMutualFriends($user_id,$friend_id);
		}
		if (in_array("participantNumberFriends",$this->variables)) {
			$values["participantNumberFriends"] = $this->numberOfFriends($user_id);
		}
		if (in_array("friendNumberFriends",$this->variables)) {
			$values["friendNumberFriends"] = $this->numberOfFriends($friend_id);
		}
		if (in_array("ageDifference",$this->variables)) {
			$values["ageDifference"] = $this->getAgeDifference($user_id,$friend_id);
		}
		if (in_array("numberOfOccupationsDifference",$this->variables)) {
			$values["numberOfOccupationsDifference"] = $this->numberOfOccupationsDifference($user_id,$friend_id);
		}
		
		return $values;
	}
	private function daysSinceLastCommunication($user_id_1,$user_id_2) { // Works perfectly
		$data = $this->fb->getInbox($user_id_1,$user_id_2,"updated_time"); // Gets data from FB API
		if (is_null($data->updated_time) || !isset($data->updated_time)) {
			return null;
		}
		
		/*
		
		echo "here";
		echo "<b> " . $user_id_1 . " & " . $user_id_2 . " data : ";
		print_r($data->updated_time);
		echo "</b><br>";
		
		*/
		
		$today = date("d-m-Y"); //Get todays date
		$first_contact_date = date_create($data->updated_time);
		$today_date = date_create($today);
		$interval = date_diff($first_contact_date, $today_date);
		$result = $interval->format('%a');
		return $result;
	}
	private function daysSinceFirstCommunication($user_id_1,$user_id_2) { // Works perfectly
		$data = $this->fb->getInbox($user_id_1,$user_id_2,"comments{created_time}"); // Gets data from FB API
		if (is_null($data->comments->data[0]->created_time) || !isset($data->comments->data[0]->created_time)) {
			return null;
		}
		
		/*
		
		echo "<b> " . $user_id_1 . " & " . $user_id_2 . " data : ";
		print_r($data->comments->data[0]->created_time);
		echo "</b><br>";
		
		*/
		
		$today = date("d-m-Y"); //Get todays date
		$first_contact_date = date_create($data->comments->data[0]->created_time);
		$today_date = date_create($today);
		$interval = date_diff($first_contact_date, $today_date);
		$result = $interval->format('%a');
		
		/*
		
		$data = $this->fb->checkFriend($user_id_1,$user_id_2); // Gets data from FB API
		echo "check friend<br>";
		print_r($data);
		echo "<br>";
		
		*/
		
		return $result;
	}
	private function differenceBetweenTimezones($user_id_1,$user_id_2) { // Cannot get friend timezone
		$data_1 = $this->fb->getProfile($user_id_1,"timezone")["timezone"]; // Gets data from FB API
		if (!isset($data_1))
			return null;
		$data_2 = $this->fb->getProfile($user_id_2,"timezone")["timezone"]; // Gets data from FB API
		if (!isset($data_2))
			return null;
		
		/*
		
		echo "<b> " . $user_id_1 . " data : ";
		print_r($data_1);
		echo " | " . $user_id_2 . " data : ";
		print_r($data_2);
		echo "</b><br>";
		
		*/
		
		$t_zone_1 = $data_1;
		$t_zone_2 = $data_2;
		$result = $t_zone_1 - $t_zone_2; // Determine the difference between zones
		$result = abs($result); // Ensures returned integer is postive...
		return $result;
	}
	private function distanceBetweenHometowns($user_id_1,$user_id_2) { // Works perfectly
		// Gets location id from FB class
		$location_id_1 = $this->fb->getProfile($user_id_1,"hometown")["hometown"]->id; // Gets data from FB API
		if (!isset($location_id_1))
			return null;
		$location_id_2= $this->fb->getProfile($user_id_2,"hometown")["hometown"]->id; // Gets data from FB API
		if (!isset($location_id_2))
			return null;
		
		$data_1 = $this->fb->getId($location_id_1, "?fields=location"); // Gets location data from FB API
		$data_2 = $this->fb->getId($location_id_2, "?fields=location"); // Gets location data from FB API
		$lat1 = $data_1["location"]->latitude;
		$lat2 = $data_2["location"]->latitude;
		$long1 = $data_1["location"]->longitude;
		$long2 = $data_2["location"]->longitude;
		
		if (is_null($lat1) || is_null($lat2) || is_null($long1) || is_null($long2)) {
			return null; // Catch if any user hasn't set their hometown
		} else {
			$theta = $long1 - $long2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			return $miles;
		}
	}
	private function politicalDifference($user_id_1,$user_id_2) { // Works perfectly
		$data_1 = $this->fb->getProfile($user_id_1,"political"); // Gets data from FB API
		$data_2 = $this->fb->getProfile($user_id_2,"political"); // Gets data from FB API
		
		if (!isset($data_1["political"]))
			return null;
		if (!isset($data_2["political"]))
			return null;
			
		/*
		
		echo "<b> " . $user_id_1 . " data : ";
		print_r($data_1);
		echo " | " . $user_id_2 . " data : ";
		print_r($data_2);
		echo "</b><br>";
		
		*/
		
		foreach($data_1 as $subData) { // For each word in array 1
			$words1 = explode(" ", strtolower($subData)); // Split them into separate values, converted to lower case	
		}
		foreach($data_2 as $subData) {// For each word in array 2
			$words2 = explode(" ", strtolower($subData)); // Split them into separate values, converted to lower case
		}
		$matching = array_intersect($words1, $words2); // Find the matching values in both arrays
		$numMatching = count($matching); // Count the number of matching values
		$numOriginal = count($words1); // Stores the count of the number of words in user 1's political section
		$result = ($numMatching/$numOriginal)*100; // Get the percentage of matching words
		return $result;
	}
	private function numberOfMutualFriends($user_id_1, $user_id_2) { // Works perfectly
		$data = $this->fb->getFriends($user_id_1,""); // Gets data from FB API
		
		$result = 0; // Counter for the number of mutual friends
		
		foreach ($data["data"] as $id=>$friend) { // Goes through each of user 1's friends list
			if ($this->fb->checkFriends($user_id_2, $friend->id)) { // Checks if user 2's friends list contains the same friend
				$result++; // Increments counter when both users have the same friend
			}
		}
		
		return $result;
	}
	private function numberOfFriends($user_id) { // Works perfectly
		$data = $this->fb->getFriends($user_id,""); // Gets data from FB API
		$result = $data["summary"]->total_count;
		return $result;
	}
	private function getAgeDifference($user_id_1, $user_id_2) { // Works perfectly
		$data_1 = $this->fb->getProfile($user_id_1,"birthday")["birthday"]; // Gets data from FB API
		$data_2 = $this->fb->getProfile($user_id_2,"birthday")["birthday"]; // Gets data from FB API
		
		if (is_null($data_1) || is_null($data_2)) { // Checks if either of the birthday values are empty if so result is NULL
			$result = null;
		} else {
			$date1 = new  DateTime($data_1); // Turns first birthday into datetime object
			$date2 = new DateTime($data_2); // Turns second birthday into datetime object
			$interval = $date1->diff($date2); // Calculates difference between two dates
			$result = $interval->y;	// returns difference in years
		}
		return $result;
	}
	private function numberOfOccupationsDifference($user_id_1, $user_id_2) { // Works perfectly
		$data_1 = $this->fb->getProfile($user_id_1,"work{employer}"); // Gets data from FB API
		$data_2 = $this->fb->getProfile($user_id_2,"work{employer}"); // Gets data from FB API
		
		$counter_user_1 = 0;
		$counter_user_2 = 0;
	
		// Count the number of arrays labelled "employer" inside the data array for data_1
		$get_data_user_1 = $data_1["work"];
		$counter_user_1 = count($get_data_user_1);
		
		// Count the number of arrays labelled "employer" inside the data array for data_2
		$get_data_user_2 = $data_2["work"];
		$counter_user_2 = count($get_data_user_2);
	
		// $difference store the difference between
		// If $difference is a negative number then this will make sure a positive value is returned
		$difference = abs($counter_user_1 - $counter_user_2);
		
		return $difference;
	}
}

class Algorithm {
	public function __construct($user_list,$token) {
		$this->user_id = $user_list[0];
		$this->friends_list = array_slice($user_list, 1, count($user_list)-1); // Extracts the friends list from the array
		$this->friend_evals = new FriendEvals($this->friends_list);
		$this->token = $token;
		// / / / / / / / / /
		// / / / / / / / / /
		// Remove variables from this list for the algorithm to ignore them
		$this->variables = ["daysSinceLastCommunication","daysSinceFirstCommunication","differenceBetweenTimezones","distanceBetweenHometowns","politicalDifference","numberOfMutualFriends","participantNumberFriends","friendNumberFriends","ageDifference","numberOfOccupationsDifference"]; // Variables used in calculation
		// \ \ \ \ \ \ \ \ \
		// \ \ \ \ \ \ \ \ \
	}
	public function run() {
		if (count($this->friends_list) < 1) { // Verifys that friends list size is adequate
			echo "Error - Not enough friends selected.";
			return null;
		}
		
		$this->calculateFriendships($this->friends_list);
		//print_r($this->friend_evals->get());
		// echo "<br/>";
		$user_profile = $this->profileUser();
		$variables= array_keys($user_profile);
		/*
		
		echo "feilds: ";
		print_r($variables);
		echo "<br/>";
		
		*/
		$friend_evals = $this->friend_evals->get($variables);
		/*
		
		echo "<b>friend_evals<b> ";
		print_r($friend_evals);
		echo "<br>";
		
		*/
		return $this->calculateFriendshipValue($user_profile,$friend_evals);
	}
	private function profileUser() { // Creates a profile of user from friend data
		$user_profile = array_fill_keys($this->variables,0);
		$sample_size = array_fill_keys(array_keys($user_profile),0); // Each variable with a value of zero
		foreach ($this->friends_list as $id=>$friend_id) {
			$eval = $this->friend_evals->getId($friend_id);
			foreach ($eval as $var=>$value) { // Calculates the sum of each variable for all friends
				if (!is_null($value)) {
					$sample_size[$var] += 1;
				}
				$user_profile[$var] += $value;
			}
		}
		//echo "Sample sizes:<br/>----";
		//print_r($sample_size);
		//echo "<br/>";
		foreach ($user_profile as $var=>$value) { // Converts sums to averages for each variable
			if ($sample_size[$var] == 0) {
				unset($user_profile[$var]);
				continue;
			}
			if ($value == 0) {
				$value += 1;
			} else {
				$user_profile[$var] = $value / $sample_size[$var];
			}
		}
		//echo "User profile:<br/>----";
		//print_r($user_profile);
		//echo "<br/>";
		return $user_profile;
	}
	private function calculateFriendshipValues($user_profile,$friend_evals) { // Calculates final value for all friends
		$min_values = array_fill_keys(array_keys($user_profile), 9999999);
		$max_values = array_fill_keys(array_keys($user_profile), -1000000);
		//$friend_evals["902751509792346"]["daysSinceFirstCommunication"] = 20;
		foreach ($friend_evals as $friend_id=>$scores) {
			foreach ($scores as $var=>$value) {
				$friend_evals[$friend_id][$var] = $value - $user_profile[$var]; // Subtract average value
				if ($friend_evals[$friend_id][$var] > $max_values[$var]) $max_values[$var] = $friend_evals[$friend_id][$var];
				if ($friend_evals[$friend_id][$var] < $min_values[$var]) $min_values[$var] = $friend_evals[$friend_id][$var];
			}
		}
		
		foreach ($friend_evals as $friend_id=>$scores) {
			foreach ($scores as $var=>$value) {
				/*
				
				echo $max_values[$var] . " " . $min_values[$var] . "<br>";
				
				*/
				$denom = ($max_values[$var] - $min_values[$var]); // Denominator value
				if ($denom == 0)
					$denom = 1; // Prevents denominator's value from being zero
				$friend_evals[$friend_id][$var] = ($value - $min_values[$var]) / $denom; // Normalise value
			}
		}
		// / / / / / / / / /
		// / / / / / / / / /
		// Insert code here to offset values significance
		foreach ($friend_evals as $friend_id=>$scores) {
			/*
			echo "before----" . $friend_id . " ";
		 	print_r($scores);
		 	echo "<br/>";
		 	*/
			if (!is_null($scores["daysSinceLastCommunication"])) {
				$scores["daysSinceLastCommunication"] = log($scores["daysSinceLastCommunication"] + 1) * -1;
			}
			if (!is_null($scores["daysSinceFirstCommunication"])) {
				$scores["daysSinceFirstCommunication"] = log($scores["daysSinceFirstCommunication"] + 1) * 1;
			}
			if (!is_null($scores["differenceBetweenTimezones"])) {
				$scores["differenceBetweenTimezones"] = ($scores["differenceBetweenTimezones"] / 24) * 0.25;
			}
			if (!is_null($scores["distanceBetweenHometowns"])) {
				$scores["distanceBetweenHometowns"] = ($scores["distanceBetweenHometowns"] / 7926) * -7000; // 7926 is the equitorial circumference of the world (in miles)
			}
			if (!is_null($scores["politicalDifference"])) {
				$scores["politicalDifference"] = log($scores["politicalDifference"] + 1) * -0.25;
			}
			if (!is_null($scores["numberOfMutualFriends"])) {
				$scores["numberOfMutualFriends"] = log($scores["numberOfMutualFriends"] + 1) * -0.5;
			}
			if (!is_null($scores["participantNumberFriends"])) {
				$scores["participantNumberFriends"] = log($scores["participantNumberFriends"] + 1) * -1;
			}
			if (!is_null($scores["friendNumberFriends"])) {
				$scores["friendNumberFriends"] = log($scores["friendNumberFriends"] + 1) * -0.5;
			}
			if (!is_null($scores["ageDifference"])) {
				$scores["ageDifference"] = log($scores["ageDifference"] + 1) * -0.4;
			}
		 	if (!is_null($scores["numberOfOccupationsDifference"])) {
		 		$scores["numberOfOccupationsDifference"] = $scores["numberOfOccupationsDifference"] * -1;
		 	}
		 	/*
		 	echo "after-----" . $friend_id . " ";
		 	print_r($scores);
		 	echo "<br/>";
		 	*/
		}
		// \ \ \ \ \ \ \ \ \
		// \ \ \ \ \ \ \ \ \

		// echo "Friendship Values:<br/>";
		// foreach ($friend_evals as $friend_id=>$scores) {
		// 	echo "----" . $friend_id . " ";
		// 	print_r($scores);
		// 	echo "<br/>";
		// }
		return $friend_evals;
	}
	private function calculateFriendshipValue($user_profile,$friend_evals) {
		$friendship_values = $this->calculateFriendshipValues($user_profile,$friend_evals);
		$friendship_value = array();
		foreach ($friendship_values as $friend_id=>$values) {
			$sum = 0;
			foreach ($values as $key => $value) {
				$sum += $value;
			}
			if (count($values) > 0) {
				$average = $sum / count($values);
			} else {
				$average = 0;
			}
			$friendship_value[$friend_id] = $average;
		}
		return $friendship_value;
	}
	private function calculateFriendship($friend_id,$functions) { // Run friendship calculation functions
		/*
		
		echo "<br>Friend:" . $friend_id . "<br>";
		
		*/
		$values = array();
		$values = $functions->run($this->user_id,$friend_id);
		
		// Removes any indexes, which do not have any value
		foreach ($values as $key=>$value) {
			if (!isset($values[$key])) {
				unset($values[$key]);
			}
		}
		
		/*
		echo "----------<br><u>". $friend_id . " Results</u><br>";
		foreach ($values as $key=>$value) {
			echo $key . ": " . $value . "<br>";
		}
		if (count($values) == 0) {
			echo "none<br>";
		}
		echo "----------<br>";
		*/
		
		return $values;
	}
	private function calculateFriendships($friends_list) {
		$functions = new Functions($this->token, $this->variables);
		foreach ($friends_list as $id=>$value) {
			$data = $this->calculateFriendship($value,$functions);
			$this->friend_evals->setId($value,$data);
		}
	}
}

class FriendEvals {
	private $data = array();
	public function __construct($friends_list) {
		foreach ($friends_list as $key=>$value) {
			$this->data[$value] = array();
		}
	}
	private function run(){}
	public function add($id, $data) {
		$this->data = $this->data + [$id => $data];
	}
	public function get($variables) { // Returns variables for all friends, variables to return specified in $variables array
		$temp_data = $this->data;
		foreach ($this->data as $friend_id => $results) {
			foreach ($results as $variable => $value) {
				if (!in_array($variable,$variables)) {
					unset($temp_data[$friend_id][$variable]);
				}
			}
		}
		return $temp_data;
	}
	public function getId($id) {
		return $this->data[$id];
	}
	public function getKeys() {
		return array_keys($this->data);
	}
	public function set($data) {
		$this->data = $data;
	}
	public function setId($id,$data) {
		$this->data[$id] = $data;
	}
}



$token = $_POST["0"];
unset($_POST["0"]);

$users = Array();
$user_list = array();
foreach($_POST as $key => $value) {
	$user = Array();
	//$user["id"] = $key;
	$user["profilePic"] = $value;
	$user["score"] = 0;
	$users[$key] = $user;
	$user_list[] = $key;
}
/*

echo "User list in:<br/>";
print_r($user_list);
echo "<br><br>";

*/

//echo '<br/>note(user_list[0]=>"logged in user", user_list[1:end]=>"user friends")<br/>';

// echo "<hr>";
$algorithm = new Algorithm($user_list,$token); // Initialise Algorithm
$results = $algorithm->run(); // Run calculation
// echo "<hr>";
// echo "JSON:<br/>";
$temp[$user_list[0]] = array('0'=>0,'1'=>$users[$user_list[0]]["profilePic"]);
foreach ($results as $user_id=>$score) { // Compiles array of scores
	$user_name = getNameFromID($user_id);
	$temp[$user_id] = array('0'=>$score, '1'=>$users[$user_id]["profilePic"], '2'=>$user_name);
}
/*
echo "User list in:<br><pre>";
print_r($temp);
echo "</pre><br>";
*/
//header('Content-Type: application/json');
echo json_encode($temp); // Display json results
?>