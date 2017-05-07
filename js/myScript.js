  $("#loginWithFb").click(function() {
    $("#selError").html('<div id="waitForResults"></div>');
    Login();
   });

 window.fbAsyncInit = function() {
    FB.init({
      appId      : '1601241650112745',
      xfbml      : true,
      cookie     : true,
      version    : 'v2.2'
    });

    FB.getLoginStatus(function(response) {
      if (response.status === 'connected') {
        var uid = response.authResponse.userID;
        var accessToken = response.authResponse.accessToken;
        FB.api('/me', function(response) {
            username = response.name;
            // Uncomment for debug
            // console.log("Logged in!");

            $("#username").text(username);
        });

      }
    });

  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));

  // Initialize
    var username = "";
    // FB login 
    function Login() {
        if($('input#agreeCheck:checked').length > 0) {
          FB.login(function(response) {
              if (response.authResponse) {
                var accessToken = response.authResponse.accessToken;
                window.location.replace("index.php");
              } else {
                  // Catch users mistakes
                  $("#selError").html("Login failed.");
              }
              // What permissions to request from the user.
          }, {
              auth_type: 'reauthenticate',
              scope: 'user_about_me, user_birthday, user_hometown, user_likes, user_photos, user_work_history, user_religion_politics, user_groups, read_stream, read_mailbox, user_friends'
          });
        }
        else {
          $("#selError").html("In order to use our app, you have to agree to these!");
        }
    }
