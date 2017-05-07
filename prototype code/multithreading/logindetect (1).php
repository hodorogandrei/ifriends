<?php session_start();?>
<html lang='en'>
	<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
		<title>Like List</title>
    <link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
	</head>
  <body>
    <?php

      $username_value = $_POST['maybe'];

      require 'autoload.php';

      use Facebook\FacebookSession;
      use Facebook\FacebookJavaScriptLoginHelper;
      use Facebook\FacebookRequest;
      use Facebook\FacebookRequestException;
      use Facebook\FacebookResponse;
      use Facebook\FacebookSDKException;
      use Facebook\FacebookAuthorizationException;
      use Facebook\GraphObject;
      use Facebook\GraphUser;

      FacebookSession::setDefaultApplication('1506880812915549', '29eee943763e9b1316c3588d6ab861d3');

      $helper = new FacebookJavaScriptLoginHelper();
      try {
         $session = $helper->getSession();
      } catch(FacebookRequestException $ex) {
        // When Facebook returns an error
        echo "PHP failure! (Old session?)";
      } catch(\Exception $ex) {
        echo "PHP failure! Did you log in on the previous page?";
      }
      if ($session) {
        echo "Retrieving data... <br><br>";
        TopTenStatus($session);
      }

      function GetUserName($session) {
        $endname = "";
        if($session) {
          try {
            $user_profile = (new FacebookRequest(
              $session, 'GET', '/me'
            ))->execute()->getGraphObject(GraphUser::className());
            $endname = $user_profile->getName();
          } catch(FacebookRequestException $e) {
            echo "Exception occured, code: " . $e->getCode();
            echo " with message: " . $e->getMessage();
          }   
        }
        return $endname;
              }

      function TopTenStatus($session) {
        if($session) {

          try {
            $namelist = [];
            $next_page = "/me/feed?fields=likes&limit=25";
            $page_exists = true;

            while (count($namelist) < 600 && $page_exists == true) {
              
              $request = new FacebookRequest(
                $session,
                'GET',
                $next_page
              );
              $response = $request->execute();
              $graphObject = $response->getGraphObject();

              $assoc_posts = $graphObject->getPropertyAsArray('data');
              if ($graphObject->getProperty('paging') !== NULL){
                $assoc_page = $graphObject->getProperty('paging')->asArray();
              }
              if (count($assoc_posts) < 1) {$page_exists = false;}

              $next_page = $assoc_page['next'];
              
              $splode = explode("/", $next_page);
              array_shift($splode);
              array_shift($splode);
              array_shift($splode);
              $splode[0] = "";
              $next_page = implode("/", $splode);

              foreach ($assoc_posts as $post) {
                $assoc_post = $post->asArray(); 
                $post_id = $assoc_post['id'];
                $created_time = $assoc_post['created_time'];
                if ($post->getProperty('likes')) {
                  $likes = $post->getProperty('likes');
                  $assoc_likes = $likes->getPropertyAsArray('data');
                  foreach ($assoc_likes as $like) {
                    $assoc_like = $like->asArray();
                    $namelist[] = $assoc_like['name'];
                  }
                }
              } 
            }

            $alltentemp = SortNamesby10($namelist);
            $na = GetUserName($session);
            $allten3 = array_diff($alltentemp,array($na));
            $allten = array_values($allten3);

            echo "<br><br>";
            echo "Your top 10 (data prevailing) are as follows:";
            echo "<br>";
            echo "<ol>";
            echo "<li>".$allten[0]."</li>";
            echo "<li>".$allten[1]."</li>";
            echo "<li>".$allten[2]."</li>";
            echo "<li>".$allten[3]."</li>";
            echo "<li>".$allten[4]."</li>";
            echo "<li>".$allten[5]."</li>";
            echo "<li>".$allten[6]."</li>";
            echo "<li>".$allten[7]."</li>";
            echo "<li>".$allten[8]."</li>";
            echo "<li>".$allten[9]."</li>";
            echo "</ol>";
            
          } catch(FacebookRequestException $e) {
            echo "Exception occured, code: " . $e->getCode();
            echo " with message: " . $e->getMessage();
          }   
        }
      }

      function SortNamesby10($namelist) {
        $count = array_count_values($namelist);
        arsort($count);
        // $highest10 = array_slice($count, 0, 10);
        $hi10flip = array_flip($count);
        $sorted = array_slice($hi10flip, 0);
        return $sorted;
      }
    ?>
	</body>
</html>

