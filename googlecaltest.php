<?php require_once "config.php"; 

$creds = getOption("google_creds");

if (!$creds || !file_exists($creds)) {
	echo "No Google OAuth creds found.";
}

putenv("GOOGLE_APPLICATION_CREDENTIALS=".$creds);

if (getOption('debug')) {
    ini_set("display_errors", "1");
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
    }

function googleTest() {
	$maxEvents = 20;
    date_default_timezone_set('Europe/London');
	$today = date("c");
    $tomorrow = date("c", mktime(23, 59, 59, date("m"), date("d")+1, date("Y")));
    //echo $today."<br>".$tomorrow; exit(); 
	$calendarId = 'subfmradio@gmail.com';
	$scope = 'https://www.googleapis.com/auth/calendar.readonly';
	$client = new Google_Client();
	$client->useApplicationDefaultCredentials();
	$client->setScopes($scope);
	$service = new Google_Service_Calendar($client);

	$options = array(
	    'maxResults' => $maxEvents,
	    'orderBy' => 'startTime',
	    'singleEvents' => TRUE,
	    'timeMin' => $today,
        'timeMax' => $tomorrow
	);

	$results = $service->events->listEvents($calendarId, $options);
	
    foreach ($results->items as $dj) {
        if (str_starts_with($dj->summary,"Available Slot") || $dj->summary === "Archives") {
            continue;
        }
        echo "<li>".$dj->summary."</li>";
    }

    //echo '<pre>' . print_r($results, true) . '</pre><br>';

} ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Google Calendar Test By BrainZ</title>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
        <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection" />
        <link href="css/generator.css" type="text/css" rel="stylesheet" media="screen,projection" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
        <nav class="grey darken-4" role="navigation">
            <div class="nav-wrapper container">
                <a id="logo-container" href="https://sub.fm" class="brand-logo">
                    <img src="img/sublogo.png" alt="sub.fm" />
                </a>
            </div>
        </nav>
        <div class="section no-pad-bot" id="index-banner">
            <div class="container">
                <h1 class="header center black-text">Google Calendar Test</h1>
                <h3 class="header center black-text">by <a href="https://x.com/mrbrainz">@MrBrainz</a></h3>
                <br>
                <div class="row">
                    <?php googleTest(); ?>
                </div>
            </div>
        </div>
    </body>
</html>