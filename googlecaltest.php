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

function makeCleanString($string) {
   $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

   return strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', $string)); // Removes special chars.
}


function buildDJList($calobj) {
    $djs = [];

    foreach ($calobj as $dj) {

        // Ignore available slots and archives
        if (str_starts_with($dj->summary,"Available Slot") || $dj->summary === "Archives") {
            continue;
        }

        $gstarttime = $dj->getStart();
        $gendtime = $dj->getEnd();

        $starttime = strtotime($gstarttime->dateTime);
        $endtime = strtotime($gendtime->dateTime);

        $starttime = (date("i", $starttime) !== "00") ? date("g:ia", $starttime) : date("ga", $starttime);

        $endtime = (date("i", $endtime) !== "00") ? date("g:ia", $endtime) : date("ga", $endtime);

        $item = ['name' => $dj->summary,
                 'starttime' => $starttime,
                 'endtime' => $endtime,
                 'timerange' => $starttime. ' - '.$endtime.' GMT',
                 'cleanname' => makeCleanString($dj->summary)
             ];

        $djs[] = $item;
    }

    return $djs;
}

function getNextShows() {
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

    return $results;
}

function writeCalCache($data) {
    file_put_contents( getOption("cal_cachefile"), json_encode($data));
}

function checkCalCache() {

    $cachefile = getOption("cal_cachefile");

    if (!file_exists($cachefile)) {
        return true;
    }

    if (time()-filemtime($cachefile) > 10) {
        return true;
    } else {
        return false;
    }

}


function googleTest() {

    if (checkCalCache()) {
        $shows = getNextShows();
        $djs = buildDJList($shows);
        writeCalCache($djs);
        echo "<h2>Written new cache file</h2>";
    } 

    $cachefile = getOption("cal_cachefile");

    $djfile = file_get_contents($cachefile);

    echo filemtime($cachefile);

    print_r(json_decode($djfile));

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