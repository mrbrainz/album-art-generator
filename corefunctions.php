<?php

require_once __DIR__ . '/vendor/autoload.php';

function setOption($name,$value) {
	if (!isset ($GLOBALS['options'])) {
		$GLOBALS['options'] = [];
	}

	$GLOBALS['options'][$name] = $value;
}

function getOption($name) {
	if (!isset ($GLOBALS['options']) || !isset ($GLOBALS['options'][$name])) {
		return null;
	}
	return $GLOBALS['options'][$name];
}

function isPost() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      return true;
    }
        /* if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']){
          http_response_code(400);
          exit;
        }
        return true; */
    
    return false;
}

function readPostData() {
	
    $output = [];
    if (isset($_POST['payload'])) {
    	
        $output = json_decode(urldecode($_POST['payload']), true);
        foreach($output as $key=>$value) {
            $output[$key] = urldecode($value);
        }
    }

    if (isset($_FILES['file'])) {
    	$output['imgfile'] = $_FILES['file'];
    } else {
    	$output['imgfile'] = false;
    }

    return $output;
}

function sanitiseFilename($file) {
    // Remove anything which isn't a word, whitespace, number
    // or any of the following caracters -_~,;[]().
    $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
    // Remove any runs of periods
    $file = mb_ereg_replace("([\.]{2,})", '', $file);
    
    return $file;
}

 function is_base64($s) {
      return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
}

 function is_base64_image($s) {
     $s = explode(";base64,",$s);
    
    if (sizeof($s) == 2) {
    
        if(substr( $blobparts[0], 0, 11 ) != "data:image/") {
            return false;
        }  

        if (!is_base64($blobparts[1])) {
            return false;
        }
    } else {
        return false;
    }
    return true;
 }