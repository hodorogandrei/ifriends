<?php

require_once( 'Facebook/FacebookSession.php' );
require_once( 'Facebook/FacebookRedirectLoginHelper.php' );
require_once( 'Facebook/FacebookRequest.php' );
require_once( 'Facebook/FacebookResponse.php' );
require_once( 'Facebook/FacebookSDKException.php' );
require_once( 'Facebook/FacebookRequestException.php' );
require_once( 'Facebook/FacebookAuthorizationException.php' );
require_once( 'Facebook/GraphObject.php' );
require_once( 'use Facebook\GraphNodes\GraphUser');
 
use Facebook\GraphNodes\GraphUser;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;

 
// init app with app id (APPID) and secret (SECRET)
FacebookSession::setDefaultApplication('1506880812915549','29eee943763e9b1316c3588d6ab861d3');
 
session_start();
//check for existing session and validate it
if (isset($_SESSION['token'])) {
  $session = new FacebookSession($_SESSION['token']);
  if (!$session->Validate('APP-ID', 'APP-SECRET')) {
    unset($session);
  }
}
 
//get new session
if (!isset($session)) {
  try {
    $helper = new FacebookJavaScriptLoginHelper();
    $session = $helper->getSession();
    $_SESSION['token'] = $session->getToken();
  } catch(FacebookRequestException $e) {
    unset($session);
    echo $e->getMessage();
  }
}
 
//do some api stuff
if (isset($session)) {
  $me = (new FacebookRequest(
    $session, 'GET', '/me'
  ))->execute()->getGraphObject(GraphUser::className());
  echo $me->getName();
  echo '<script>console.log("woo")</script>';
}