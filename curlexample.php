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
                    
<?php

function createPayload($id=1,$text1="",$text2="",$text3="",$text4="",$img="") {

    $payloaddata = [
        'id' => $id,
        'text1' => urlencode($text1),
        'text2' => urlencode($text2),
        'text3' => urlencode($text3),
        'text4' => urlencode($text4),
        'img' => urlencode($img)
    ];

    $payload = "payload=".urlencode(json_encode($payloaddata));

    return $payload;
}

function decodePayload($payload) {
    $payload = urldecode($payload);
    $payload = ($payload) ? explode("payload=",$payload) : false;
    $payload = ($payload) ? json_decode($payload[1]) : false;

    if (!$payload) {
        return false;
    }

    $output = [];

    foreach ($payload as $pay => $load) {
        $output[$pay] = urldecode($load);
    }

    return $output;
}

function getAlbumArt($link,$payload, $debug = false) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($ch);
    $error    = curl_error($ch);
    $errno    = curl_errno($ch);

    curl_close($ch);

    if ($output) {
        $response = json_decode($output);
    }


    // Return cURL result if debug mode is false AND and image was returned from the server

    if (!$debug) {
        return (isset($response) && isset($response->img)) ? $response->img : false;
    }

    // Following is for Debug output only

    if ($debug) {

        echo"                   <h3>getAlbumArt() Debug Output</h3>
                  <pre style='overflow-x: scroll;'>

I posted to: ".$link."

My Raw Payload was: 

".$payload."

... which contained:

";

print_r(decodePayload($payload));

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

}



$postlink = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/generate.php";

$file = "djs/t1-brainz.jpg";

$mimetype = mime_content_type($file);

$img = "data:".$mimetype.";base64, ".base64_encode(file_get_contents($file));

$payload = createPayload(1,"DJ BrainZ","With MC Whistles","Sat 12 Aug 2023","3-5PM", $img);

getAlbumArt($postlink, $payload, true);

/* $art = getAlbumArt($postlink, $payload);

echo ($art) ? '<p><img src="'.$art.'" alt="" /></p>' : ""; */

?>
                    
                </div>
            </div>
        </div>
    </body>
</html>