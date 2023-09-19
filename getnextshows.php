<?php require_once "config.php"; 

$creds = getOption("google_creds");

if (!$creds || !file_exists($creds)) {
	returnError(["No Google OAuth creds found."]);
}

putenv("GOOGLE_APPLICATION_CREDENTIALS=".$creds);

if (getOption('debug')) {
    ini_set("display_errors", "1");
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
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

    $cacheduration = intval(getOption("cal_cache_duration"));

    if (time()-filemtime($cachefile) > $cacheduration) {
        return true;
    } else {
        return false;
    }

}

function returnNextShows() {
    $djs = false;

    if (checkCalCache()) {
        $shows = getNextShows();
        $djs = buildDJList($shows);
        writeCalCache($djs);
    } else {
        $cachefile = getOption("cal_cachefile");
        $djs = json_decode(file_get_contents($cachefile));
    }

    if ($djs) {
        returnJSONSuccess($djs);
    } else {
        returnError(['No DJ data found']);
    }

}

returnNextShows();