<?php
require __DIR__ . '/vendor/autoload.php';

// Start the session
session_start();

// function getOAuthCredentialsFile()
// {
//   // oauth2 creds
//   $oauth_creds = 'credentials.json';

//     echo "heyyy";
//   if (file_exists($oauth_creds)) {
//     return $oauth_creds;
//   }
// }

// /*************************************************
//  * Ensure you've downloaded your oauth credentials
//  ************************************************/
// if (!$oauth_credentials = getOAuthCredentialsFile()) {
//   echo "No Credential Found";
//   return;
// }

/************************************************
 * The redirect URI is to the current page, e.g:
 * http://localhost:8080/simple-file-upload.php
 ************************************************/
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$client = new Google\Client();
//$client = new Google_Client();
//$client->setAuthConfig($oauth_credentials);
$client->setAuthConfig('credentials.json');
$client->setRedirectUri($redirect_uri);
//$client->addScope("https://www.googleapis.com/auth/drive");
$client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
//$service = new Google\Service\Drive($client);
$service = new Google_Service_Calendar($client);


// add "?logout" to the URL to remove a token from the session
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['upload_token']);
}

/************************************************
 * If we have a code back from the OAuth 2.0 flow,
 * we need to exchange that with the
 * Google\Client::fetchAccessTokenWithAuthCode()
 * function. We store the resultant access token
 * bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);

  // store in the session also
  $_SESSION['upload_token'] = $token;

  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

// set the access token as part of the client
if (!empty($_SESSION['upload_token'])) {
  $client->setAccessToken($_SESSION['upload_token']);
  if ($client->isAccessTokenExpired()) {
    unset($_SESSION['upload_token']);
  }
} else {
  $authUrl = $client->createAuthUrl();
}


if ($client->getAccessToken()) {
// Print the next 10 events on the user's calendar.
$calendarId = 'primary';
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => true,
  'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);
$events = $results->getItems();

if (empty($events)) {
    echo "No upcoming events found.\n";
} else {
    echo "Upcoming events:\n";
    foreach ($events as $event) {
        $start = $event->start->dateTime;
        if (empty($start)) {
            $start = $event->start->date;
        }
        printf("%s (%s)\n", $event->getSummary(), $start);
    }
}
}
?>

<div class="box">
<?php if (isset($authUrl)): ?>
  <div class="request">
    <a class='login' href='<?= $authUrl ?>'>Connect Me!</a>
  </div>
<?php else: ?>
<?php endif ?>
</div>
