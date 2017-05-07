  

    // Facebook App ID shit
    window.fbAsyncInit = function() {
        FB.init({
            appId: '1506880812915549',
            xfbml: true,
            version: 'v2.2'
        });
    };
     
    // Downloads the FB JS SDK (Fuck yeah acronyms!)
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "http://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
     
     var username = ""
    // FB login function duh!
    function Login() {
        FB.login(function(response) {
            if (response.authResponse) {
                FB.api('/me', function(response) {
                    var input = document.getElementById("loginbut");
                    input.parentNode.removeChild(input);
                    username = response.name;
                    document.getElementById("notify").innerHTML = "Succesfully logged in as " + response.name + "!";
                    console.log("Logged in!");
                });
            } else {
                document.getElementById("notify").innerHTML = "Login failed.";
            }
            // What permissions to request from the user.
        }, {
            scope: 'read_stream, read_mailbox'
        });
    }
     
     var longlikelist = [];

    function feedResponse(response) {
        if (response && !response.error) {
          var likelist = [];
          var likeindex = 0;
            for (var post in response.data) {
              if (response.data[post].likes){
                for (var like in response.data[post].likes.data) {
                    likelist[likeindex] = response.data[post].likes.data[like].name;
                    likeindex++;
                }
              }
            }
            Array.prototype.push.apply(longlikelist,likelist)

            if (response.paging && response.paging.next && longlikelist.length < 199) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        console.log("Next Page:");
                        // console.log("Data from next page: ", JSON.parse(xhr.responseText));
                        feedResponse(JSON.parse(xhr.responseText))
                    }
                }
                xhr.open('GET', response.paging.next, true);
                xhr.send(null);
     
            } else{
                sorted = sortByFrequency(longlikelist);
                sorted.splice(sorted.indexOf(username), 1);
                document.getElementById("one").innerHTML = sorted[0];
                document.getElementById("two").innerHTML = sorted[1];
                document.getElementById("three").innerHTML = sorted[2];
                document.getElementById("four").innerHTML = sorted[3];
                document.getElementById("five").innerHTML = sorted[4];
                document.getElementById("six").innerHTML = sorted[5];
                document.getElementById("seven").innerHTML = sorted[6];
                document.getElementById("eight").innerHTML = sorted[7];
                document.getElementById("nine").innerHTML = sorted[8];
                document.getElementById("ten").innerHTML = sorted[9];                
            }
            
        }
        
    }
     
     
    function Status() {
        FB.api("/me/feed", {
            limit: 25,
            fields: "likes"
        }, feedResponse);
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