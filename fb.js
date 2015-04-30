window.fbAsyncInit = function () {
    FB.init({
        appId: '686769744767790', // Set YOUR APP ID
        channelUrl: 'localhost/fb.js', // Channel File
        status: true, // check login status
        cookie: true, // enable cookies to allow the server to access the session
        xfbml: true // parse XFBML
    });

    FB.Event.subscribe('auth.authResponseChange', function (response) {
        if (response.status === 'connected') {
            document.getElementById("message").innerHTML += "<br>Connected to Facebook";
        } else if (response.status === 'not_authorized') {
            document.getElementById("message").innerHTML += "<br>Failed to Connect";

            //FAILED
        } else {
            document.getElementById("message").innerHTML += "<br>Logged Out";

            //UNKNOWN ERROR
        }
    });

};

function Login() {

    FB.login(function (response) {
        if (response.authResponse) {
            getUserInfo();
            getLikes();
            getPhoto();

            $('#notin').hide();
            $('#in').show();

        } else {
            console.log('User cancelled login or did not fully authorize.');
        }
    }, {
        scope: 'email,user_photos,user_videos,user_likes'
    });

}

function getUserInfo() {

    FB.api('/me', function (response) {
        $('#username').html(response.name);
        setCookie("id", response.id, 1);
        
    });
}

function getLikes() {

    FB.api('/' + getCookie("id") + '/likes', function (response) {
        var j = JSON.parse(JSON.stringify(response));
        var a = [];

        for (var i = 0; i < j.data.length; i++) {
            a.push(j.data[i].name);
        }
        $.get("saveLikes.php", {
            likes: a,
            id: getCookie("id")
        }).done(function () {;
        });
        alert("Your Facebook Likes\n\n" + a.join('\n'));
    });

}

function getPhoto() {
    FB.api('/me/picture?type=normal', function (response) {

        $('#profilepic').attr("src", response.data.url);

    });

}

function Logout() {
    FB.logout(function () {
        document.location.reload();
    });
    setCookie("id", "0",30);
}

// Load the SDK asynchronously
(function (d) {
    var js, id = 'facebook-jssdk',
        ref = d.getElementsByTagName('script')[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement('script');
    js.id = id;
    js.async = true;
    js.src = "http://connect.facebook.net/en_US/all.js";
    ref.parentNode.insertBefore(js, ref);
}(document));