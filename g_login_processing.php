<?php 
session_start();


require_once 'google-api-php-client/autoload.php';

//echo phpinfo(); 

function WriteTolog($val1,$val2="") {
    error_log($val1.$val2);
}
//print_r($_REQUEST);
if(isset($_GET) && isset($_GET['androidverify']) && ($_GET['androidverify'] == "1")) {
    $client_id = 'client id';
    $client_secret = 'client sceret';
    $redirect_uri = 'postmessage';//'http://localhost/in/gplus/g_login.html';//$CFG->wwwpath.'gplus/g_login.html';
} else if(isset($_GET) && isset($_GET['localhost']) && ($_GET['localhost'] == "1")) {
    $client_id = '651174589723-v01qq4svvqquqiqu86gunuge59v105kv.apps.googleusercontent.com';
    $client_secret = 'VgtPIyePYEUNZ-2AIJHFWjJy';
    $redirect_uri = 'postmessage';
} else {
    $client_id = 'client id';
    $client_secret = 'client secret';
    $redirect_uri = 'postmessage';//'http://localhost/in/gplus/g_login.html';//$CFG->wwwpath.'gplus/g_login.html';

}
WriteTolog('gplus: Entering  for Login processing for WebUser');

/************************************************
  Make an API request on behalf of a user. In
  this case we need to have a valid OAuth 2.0
  token for the user, so we need to send them
  through a login flow. To do this we need some
  information from our API console project.
 ************************************************/
$client = new Google_Client();

$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);



/************************************************
  If we're logging out we just need to clear our
  local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
  WriteTolog('gplus: Logging out user');
}


/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
 ************************************************/

function check_authentication(){
    global $client, $client_id;
    $gplusId=-1;
     
    if (isset($_POST['code'])){
        if(isset($_POST['gplusid'])) {
            $gplusId = $_POST['gplusid'];
        }
        WriteTolog('gplus: got a valid code '.$_POST['code']);
        $client->authenticate($_POST['code']);
        
        $token = json_decode($client->getAccessToken());
         
        // Verify the token
        $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' .
              $token->access_token;
        $req = new Google_Http_Request($reqUrl);
         
        $tokenInfo = json_decode(
          $client->getAuth()->authenticatedRequest($req)->getResponseBody());
         
        // If there was an error in the token info, abort.
        if (isset($tokenInfo->error)) {
            WriteTolog('gplus: Error in token '.$tokenInfo->error);
            echo "$tokenInfo->error";
          return;
        }
        // Make sure the token we got is for the intended user.
        /*
        if ($tokenInfo->user_id != $gplusId) {
            echo "Token's user ID doesn't match given user ID";
            WriteTolog('gplus: Token\'s user ID doesn\'t match given user ID');
            return;
        }
        */
        //Make sure the token we got is for our app.
        if ($tokenInfo->audience != $client_id) {
            echo "Token's client ID does not match app's.";
            WriteTolog('gplus: Token\'s client ID does not match app\'s.');
            return;
        }

        // Store the token in the session for later use.
        $_SESSION['access_token']=json_encode($token);
        $response = 'Succesfully connected with token: ' . print_r($token, true);
        echo $response;
         

    } else {
       
        /************************************************
          If we have an access token, we can make
          requests, else we generate an authentication URL.
         ************************************************/
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            WriteTolog('gplus: got a valid access_token '.$_SESSION['access_token']);
            $client->setAccessToken($_SESSION['access_token']);
        }
       
        /************************************************
          If we're signed in and have a request to shorten
          a URL, then we create a new URL object, set the
          unshortened URL, and call the 'insert' method on
          the 'url' resource. Note that we re-store the
          access_token bundle, just in case anything
          changed during the request - the main thing that
          might happen here is the access token itself is
          refreshed if the application has offline access.
         ************************************************/
        if ($client->getAccessToken() && isset($_GET['storeToken'])) {
            WriteTolog('gplus: Action is storeToken');
            $_SESSION['access_token'] = $client->getAccessToken();
            echo "Store sunccessful";
        }
        
    }
}

check_authentication();


WriteTolog('gplus: Returned from g_login_processing.php'.__LINE__);

##################################################
?>