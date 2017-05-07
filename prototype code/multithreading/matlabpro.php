<!DOCTYPE html > 
<html lang=”en”>
	<head>
		<title>MATLAB Pro</title>
    <link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
	</head>
	<body style="background-color: #D8D8D8; font-family: 'Oswald', sans-serif;">
<!-- <img  src="logo.png" style="position: absolute; top: -50px;right: 1px;"> -->
		
<input id="loginbut" type="button" value="Login First" onclick="Login();" />
<p id="notify">Not Logged in.</p><br>
<p id="ch">Top 5 "Likers" of your content (Comments, posts etc.). API limits so only recent(ish) data.</p>
<input id="friendbut" type="button" value="Analyse (Slow)" onclick="LoopTopstatus();"/>
<ol>
	<li id ="one"></li>
	<li id ="two"></li>
	<li id ="three"></li>
	<li id ="four"></li>
	<li id ="five"></li>
</ol><br>
<p id="mess">Top 10 people you message via fb chat. API limits so only recent(ish) data.</p>
<input id="messagebut" type="button" value="Analyse (Slow)" onclick="Topmessage();"/>
<ol>
  <li id ="qone"></li>
  <li id ="qtwo"></li>
  <li id ="qthree"></li>
  <li id ="qfour"></li>
  <li id ="qfive"></li>
  <li id ="qsix"></li>
  <li id ="qseven"></li>
  <li id ="qeight"></li>
  <li id ="qnine"></li>
  <li id ="qten"></li>
</ol>
	</body>

<script>
// Facebook App ID shit
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '1506880812915549',
      xfbml      : true,
      version    : 'v2.2'
    });
  };

// Downloads the FB JS SDK (Fuck yeah acronyms!)
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "http://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));


// FB login function duh!
  function Login() {
    FB.login(function(response) {
    	if (response.authResponse) {
     		FB.api('/me', function(response) {
    			var input = document.getElementById("loginbut");

          input.parentNode.removeChild(input)

    			document.getElementById("notify").innerHTML = "Succesfully logged in as "+ response.name + "!";
          console.log("Logged in!");
    		}); 
   		} 
   		else{
     document.getElementById("notify").innerHTML = "Login failed."
   }
   // What permissions to request from the user.
 	}, {scope: 'read_stream, read_mailbox'});
  }

// Reads API data from feed concerning 'likes'
function LoopTopstatus(){
  var likeindex = 0;
  var likelist = [];
  var moffset = 0;
  var empty = false;
  var loop = [];
    // while (empty == false)

  for (var i=0; i < 10; i++ ){
     Topstatus(likelist, likeindex, moffset, empty, function(likelist, likeindex, moffset, empty){

        console.log(likelist, likeindex, moffset, empty) 
        likeindex = loop[1];
        Array.prototype.push.apply(likelist,loop[0]);
        // likelist = likelist + loop[0];
        moffset = loop[2];
        console.log(loop[2]);
        empty = loop[3];

    document.getElementById("ch").innerHTML = "Done!";
      // Sorts the likelist array into descending order without dupes.
    console.log(likelist.toString())
    var a = sortByFrequency(likelist);
    console.log(a.toString())
    
      // Display the first 5 elements in the sorted array to the user.
    document.getElementById("one").innerHTML = a[0];
    document.getElementById("two").innerHTML = a[1];
    document.getElementById("three").innerHTML = a[2];
    document.getElementById("four").innerHTML = a[3];
    document.getElementById("five").innerHTML = a[4];
     });
    }


}

  function Topstatus(likelist, likeindex, moffset, empty, callback) {
    FB.api(
    "/me/feed", {limit: 25, fields:"likes", offset:moffset},
    function (response) {
      if (response && !response.error) {      
        // var obj2 = JSON.parse(response); 
        // var likelist = [];
        // var likeindex = 0;
        var dlen = response.data.length;
        if (dlen < 1){empty = true;}
        for (var i = 0; i < response.data.length; i++) {
          if (response.data[i].likes){
            for (var j = 0; j < response.data[i].likes.data.length; j++) {
              likelist[likeindex] = response.data[i].likes.data[j].name;
              likeindex++;
            }   
          }
        }
        moffset = moffset + 25;
        
        callback(likelist, likeindex, moffset, empty);

      }
    }
    );
  }

function sortByFrequency(array) {
    var frequency = {};
    array.forEach(function(value) { frequency[value] = 0; });
    var uniques = array.filter(function(value) {
        return ++frequency[value] == 1;
    });
    return uniques.sort(function(a, b) {
        return frequency[b] - frequency[a];
    });
}

  function Topmessage() {
    FB.api(
    "/me/inbox", {limit: 5000, fields:"id,comments"},
    function (response) {
      if (response && !response.error) {
        document.getElementById("mess").innerHTML = "Done!";
        var messlist = [];
        var messindex = 0;
        for (var i = 0; i < response.data.length; i++) {
          if (response.data[i].comments){
            for (var j = 0; j < response.data[i].comments.data.length; j++) {
              if (response.data[i].comments.data[j].from){
                messlist[messindex] = response.data[i].comments.data[j].from.name;
                messindex++;
              }
            }
          }
        }
        // Sorts the messindex array into descending order without dupes.
        a = sortByFrequency(messlist);

        console.log(messlist.toString());
        console.log(a.toString());
        // Display the first 5 elements in the sorted array to the user.
        document.getElementById("qone").innerHTML = a[1];
        document.getElementById("qtwo").innerHTML = a[2];
        document.getElementById("qthree").innerHTML = a[3];
        document.getElementById("qfour").innerHTML = a[4];
        document.getElementById("qfive").innerHTML = a[5];
        document.getElementById("qsix").innerHTML = a[6];
        document.getElementById("qseven").innerHTML = a[7];
        document.getElementById("qeight").innerHTML = a[8];
        document.getElementById("qnine").innerHTML = a[9];
        document.getElementById("qten").innerHTML = a[10];
      }
    }
);
  }
</script>
</html>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', 'UA-49522622-1', 'benl007.com');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');
</script>