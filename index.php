<?php

error_reporting(0);

 require('php-sdk/src/Facebook/autoload.php');

 function getProfilePicURL($myProfileID) {
 	$myProfilePicJSON = file_get_contents("https://graph.facebook.com/".$myProfileID."/picture?type=large&redirect=false");
	$myProfilePicArray = json_decode($myProfilePicJSON);
	$myProfilePicURL = $myProfilePicArray->data->url;

	return $myProfilePicURL;
 }

// import the specified classes to the current scope
use Facebook\FacebookSession;
use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;

// Facebook App ID
// FacebookSession::setDefaultApplication('1601241650112745', '100f579df0b4cb8eaa39214c0f8228e3');

FacebookSession::setDefaultApplication('1601241650112745', '100f579df0b4cb8eaa39214c0f8228e3');

// Facebook login object, try and retrieve active FB session
$jsHelper = new FacebookJavaScriptLoginHelper();

// Setting the session for the first time
try {
    $session = $jsHelper->getSession($facebookClient);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    if( isset($_SESSION['token']))
	{
	    // We have a token, is it valid? 
	    $session = new FacebookSession($_SESSION['token']); 
	    try
	    {
	        $session->Validate($appid ,$secret);
	    }
	    catch( FacebookAuthorizationException $ex)
	    {
	        // Session is not valid any more, get a new one.
	        $session ='';
	    }
	}
	else {
		header('Location: index.php');
	}
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    echo 'test';
} catch(Facebook\FacebookAuthorizationException $e) {
     echo '<p align="center">You\'ve been logged out or your session has expired!</p>';
}

// see if we have a session in $_Session[]

if(isset($session)) {
	// set the PHP Session 'token' to the current session token
    $_SESSION['token'] = $session->getToken();
    // $_SESSION['logoutURL'] = $session->getLogoutUrl();
    // SessionInfo 
    // $info = $session->getSessionInfo();  
    // // getAppId
    // echo "Appid: " . $info->getAppId() . "<br />"; 
    // // session expire data
    // $expireDate = $info->getExpiresAt()->format('Y-m-d H:i:s');
    // echo 'Session expire time: ' . $expireDate . "<br />"; 
    // // session token
    // echo 'Session Token: ' . $session->getToken() . "<br />"; 

	$friends = (new FacebookRequest( $session, 'GET', '/me/friends/' ))->execute()->getGraphObject()->asArray();

	// GET OWN Profile Information & Picture

	$myInfo = (new FacebookRequest( $session, 'GET', '/me/' ))->execute()->getGraphObject()->asArray();
	$myProfileID = $myInfo["id"];
	$myProfilePicURL = getProfilePicURL($myProfileID);
	
	// print_r($myProfilePicURL);

	//TEST CODE FOR REQUEST
	// $friends2 = (new FacebookRequest( $session, 'GET', '/me/friends/' ))->execute()->getGraphObject()->asArray();
	// $friends3 = (new FacebookRequest( $session, 'GET', '/me/inbox/' ))->execute()->getGraphObject()->asArray();
	// echo '<pre>';
	// print_r($friends);
	// echo '</pre>';
	//END

	$friendsArray = Array();
	$i = 0;

	foreach ($friends["data"] as $friendObj) {
		// echo $friendObj->name .'    ' .$friendObj->picture->data->url  .'<br/>';
		$friendsArray[$i] = Array();
		$friendsArray[$i]["title"] = $friendObj->name;
		$friendsArray[$i]["thumb"] = getProfilePicURL($friendObj->id);
		$friendsArray[$i]["text"] = $friendObj->name;
		$friendsArray[$i]["tags"] = $friendObj->name;
		// $friendsArray[$i]["loc"] = basename(__FILE__). "?addFriend=" . $friendObj->name;
		$friendsArray[$i]["loc"] = $friendObj->id;

		// $profilePicURL = $friendObj->picture->data->url;
		// $profilePicAfterSlash = substr($profilePicURL, strrpos($profilePicURL, '/') + 1);

		// $pos1 = strpos($profilePicAfterSlash, '_');
		// $pos2 = strpos($profilePicAfterSlash, '_', $pos1 + 1);

		// $friendID = substr($profilePicAfterSlash, $pos1 + 1, $pos2-$pos1);
		
		// echo $profilePicAfterSlash. ' '.$pos1.' '.$pos2 . ' ' .$friendID.' '.$friendsArray[$i]["title"]. '<br/>';

		$i++;
	}

	$friendsArrayJSON = '{"pages": [';
	foreach ($friendsArray as $jsonMerge) {
		$friendsArrayJSON .= json_encode($jsonMerge);
		$friendsArrayJSON .= ",";
	}

	$friendsArrayJSON .= ']}';

	// echo '<pre>';
	// print_r($friendsArrayJSON);
	// echo '</pre>';
}

if(isset($_GET['action']) && $_GET['action'] === 'logout') {
	$token = $_SESSION['token'];
	$url = 'https://www.facebook.com/logout.php?next=http://ifriends.website/&access_token='.$token;
	header('Location: '.$url);
}

// echo $_SESSION['token'] . '<br/>';

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>iFriends</title>
	<meta name="description" content="iFriends - just for you">
	<meta name="author" content="Group 6">

	<!-- Mobile specific meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

	<?php include('includes/styles.php');?>
</head>
<body>
	<div class="container">
		<div class="row clearfix">
			<div class="col-md-12 column">
				<div class="jumbotron">
					<div id="notify"></div>
					<?php if(isset($session)) { ?>
						<button class="logoutButton btn btn-lg btn-danger"><i class="glyphicon glyphicon-off"></i> Logout</button>
						<img class="welcomeImage img-circular" src="<?php echo $myProfilePicURL;?>" alt="" class="img-circular" />
						<span class="welcomeText">Welcome, <br/><b><span id="username"></span></b>!</span>
						<img src="img/logo.png" alt="" />

						<div id="evalData">
							<h2>Please select the friends to evaluate:</h2>
							<form action="index.php" method="get">
								<input type="text" name="q" id="tipue_drop_input" autocomplete="off" placeholder="Search for friends..." required>
								<div id="tipue_drop_content"></div>
							</form>
							<h2>Selected friends:</h2>
							<div id="selFriends">
								
								<div id="selError"></div>
								<button class="btn btn-info btn-lg" id="evaluate"><i class="glyphicon glyphicon-flag"></i> Evaluate!</button>
							</div>
						</div>

						<div id="evalResult">
							
						</div>
						<button class="btn btn-info btn-lg" id="startOver"><i class="glyphicon glyphicon-repeat"></i> Start over</button>
						<a href="terms-of-service.html" target="_blank">Terms of Service</a> | <a href="privacy-policy.html" target="_blank">Privacy Policy</a>
					<?php } else { ?>
						<img src="img/logo.png" alt="" />
						<h1>
							Hello, guest!
						</h1>
						<p>
							Welcome to iFriends, a tool that lets you find out who your best friends are!
						</p>
						<p>
							<div class="checkbox">
							    <label>
							      <input type="checkbox" id="agreeCheck"> I agree to the <a href="terms-of-service.html" target="_blank">Terms of Service</a> and to the <a href="privacy-policy.html" target="_blank">Privacy Policy</a>
							    </label>
							 </div>
							 <div id="selError"></div>
						</p>	
						<p>
							<button class="btn btn-primary btn-lg" id="loginWithFb"><i class="glyphicon glyphicon-edit"></i> Login with Facebook</button>
						</p>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>


<?php 
include('includes/scripts.php');

$sessionToken = $_SESSION['token'];
?>

<script type="text/javascript" src="tipuedrop/tipuedrop.js"></script>
<script>

var myProfileID = <?php echo json_encode($myProfileID); ?>;
var myProfilePicURL = <?php echo json_encode($myProfilePicURL); ?>;

var friendIDs = new Array();
var friendImgs = new Array();
var sessionToken = <?php echo json_encode($sessionToken); ?>;

jQuery(function($){
  $(document).ajaxStart(function() { 
      $("#evalResult").html('Please wait! Do not refresh the page, the evaluation results will be displayed automatically...<div id="waitForResults"></div>');
      $("#evaluate").css({"display" : "none"});
  });
});

$(document).ready(function() {

     $('#tipue_drop_input').tipuedrop({
          'show': 5,
          'newWindow': false
     });


     $("#tipue_drop_content").on('click', '.tipue_drop_item', function() {
     	$("#selError").html("");
     	var frID = $(this).prev().attr("id");

     	if(friendIDs.indexOf(frID) == -1) {
     		
	     	var frImg = $(this).find("img").attr("src");
	     	var frName = $(this).find(".tipue_drop_right_title").text();
	     	var htmlToAppend = '<div id="'+frID+'"><input type="hidden" value="'+frID+'" name="friend"/><img src="'+frImg+'" class="img-circular-friend" />'+ frName +'<a class="remove" id="'+frID+'" title="Remove friend"></a></div>';
	     	$("#selFriends").prepend(htmlToAppend);
	     	friendIDs.push(frID);
	     	friendImgs.push(frImg);
     	}

     });

     $("#selFriends").on('click', 'a.remove', function() {
     	var idToRemove = $(this).attr("id");
     	var imgToRemove = $("#selFriends").find("div#" + idToRemove).find("img").attr("src");
     	$("#selFriends").find("div#" + idToRemove).fadeOut(500, function() { $(this).remove(); });
 		var indexToRemove = friendIDs.indexOf(idToRemove);
     	friendIDs.splice(indexToRemove,1);
     	friendImgs.splice(indexToRemove,1);
     });


     $("#evaluate").click(function() {
     	if(friendIDs.length <= 1) {
     		$("#selError").html("Please select at least 2 friends!");
     	} else {
     		$("#selError").html("");
     		
     		// Send data to backend
     		console.log(friendIDs);
     		console.log(friendImgs);

     		var data = {};
     		data[0] = sessionToken;
     		console.log(data[0]);

     		data[myProfileID] = myProfilePicURL;

     		for (var i = 0; i < friendIDs.length; i++) {
     			data[friendIDs[i]] = friendImgs[i];
     		};

     		JSON.stringify(data);

     		console.log(data);

			$.ajax({
			    type: "POST",
			    url: 'backend_long.php',
			    async: true,
			    data: data,
			    success: function(data) {
			    	// $("#evalResult").html(data);
			    	$(document).off('ajaxStart');
			    	fixedData = data.replace(/[\x00-\x1F\x80-\xFF]/g, "");
			    	JSON.stringify(fixedData);
			    	$.ajax({
			    		type: "POST",
					    url: 'diagram.php',
					    async: true,
					    data: fixedData,
					    success: function(data) {
					    	$(document).off('ajaxStart');
					    	$("#evalResult").html(data);
					    	$("#evalData").css({"display" : "none"});
					    	$("#startOver").css({"display" : "block"});
					    }
			    	});
			    }
			 
			});
     	}

     });

	$("#startOver").click(function() {
		$("#evalResult").after('Please wait while the system reloads...<div id="waitForResults"></div>');
		$(this).css({"display": "none"});
		window.location = 'index.php';
	});

	$(".logoutButton").click(function() {
		bootbox.confirm("Are you sure you wish to logout?", function(result) {
			console.log(result);
			if(result == true) {
				window.location = 'index.php?action=logout';
			}
		}); 
	});
});
var tipuedrop = <?php echo $friendsArrayJSON; ?>;

$.ajaxSetup({'global':true});
</script>
</body>
</html>