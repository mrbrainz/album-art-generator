<?php

function createPayload($id=1,$text1="",$text2="",$text3="",$text4="",$img="",$local=false) {

    $payloaddata = [
        'id' => $id,
        'text1' => urlencode($text1),
        'text2' => urlencode($text2),
        'text3' => urlencode($text3),
        'text4' => urlencode($text4)
    ];

    if ($local && file_exists($img)) {
        $img = curl_file_create($img);
        $payload['file'] = $img;
    } else {
        $payloaddata['img'] = urlencode($img);
    }

    $payload['payload'] = urlencode(json_encode($payloaddata));

    return $payload;
}

function decodePayload($payload) {
    $payload = urldecode($payload);
    $payload = ($payload) ? json_decode($payload) : false;

    if (!$payload) {
        return false;
    }

    $output = [];

    foreach ($payload as $pay => $load) {
        $output[$pay] = urldecode($load);
    }

    return $output;
}

function debugOutput($link = "", $payload = "", $output = "", $error = "", $errno = "", $response = "") {
    echo "                   <h3>getAlbumArt() Debug Output</h3>
                  <pre style='overflow-x: scroll;'>

I posted to: ".$link."

My Raw Payload was: 

".print_r($payload,true)."

... which contained:

";

print_r(decodePayload($payload['payload']));

echo "

-----------

The cURL response was:

";

        var_dump($output);

        echo "

Error: ".$error."
Error no: ".$errno."

---------------    


Server response JSON processed looks like:

";
        print_r($response);

        echo "                  </pre>";

        if ($response->img) {
            ?><h3>Generated Image:</h3><p><img src="<?php echo $response->img; ?>" alt="" /></p><?php
        }
}


function getAlbumArt($link,$payload, $debug = false) {

    //$header = array('Content-Type: multipart/form-data');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $link);
    //curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($ch);
    $error    = curl_error($ch);
    $errno    = curl_errno($ch);

    curl_close($ch);

    $response = false;

    if ($output) {
        $response = json_decode($output);
    }


    // Return cURL result if debug mode is false AND and image was returned from the server

    if (!$debug) {
        return (isset($response) && isset($response->img)) ? $response->img : false;
    } else {
        debugOutput($link, $payload, $output, $error, $errno, $response);
    }

}



function defaultActions() {

    $url_components = parse_url($_SERVER['REQUEST_URI']);

    $path = str_replace("curlexample.php","generate.php" , $url_components["path"]);

    $postlink = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://'.$_SERVER['HTTP_HOST']. $path; 

    $file = "djs/t1-brainz.jpg";

    $mimetype = mime_content_type($file);

    $img = "data:".$mimetype.";base64, ".base64_encode(file_get_contents($file));

    $payload = createPayload(1,"DJ BrainZ","With MC Whistles","Sat 12 Aug 2023","3-5PM", $img);

    getAlbumArt($postlink, $payload, true);
}

function defaultActionsWithBinaryImage() {

    $url_components = parse_url($_SERVER['REQUEST_URI']);

    $path = str_replace("curlexample.php","generate.php" , $url_components["path"]);

    $postlink = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://'.$_SERVER['HTTP_HOST']. $path; 

    $file = "djs/t1-brainz.jpg";

    $payload = createPayload(1,"DJ BrainZ","With MC Whistles","Sat 12 Aug 2023","3-5PM", $file, true);

    getAlbumArt($postlink, $payload, true);
}

/* $art = getAlbumArt($postlink, $payload);

echo ($art) ? '<p><img src="'.$art.'" alt="" /></p>' : ""; */

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Album Art Via cURL By BrainZ</title>
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
                <h1 class="header center black-text">cURL test for Album Art</h1>
                <h3 class="header center black-text">by <a href="https://x.com/mrbrainz">@MrBrainz</a></h3>
                <br>
                <div class="row">
                    <?php defaultActionsWithBinaryImage(); ?>
                </div>
            </div>
        </div>
    </body>
</html>