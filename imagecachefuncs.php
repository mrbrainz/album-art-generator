<?php // Update Sub.FM album art cache functions

if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
header( 'HTTP/1.0 403 Forbidden', TRUE, 403 );
exit();
}

require_once "config.php";

require_once "vendor/wikia/simplehtmldom/simple_html_dom.php";

function getHTML($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $str = curl_exec($curl);
    curl_close($curl);

    return $str;
}

function getDJHTML() {

    $html = new simple_html_dom();

	$containerel = ".pt-cv-page > div";

    $url = 'https://www.sub.fm/djs/';

    $htmlresp = getHTML($url);

    $html->load($htmlresp);

    return $html->find($containerel);
}

function extractDJsFromHTML($html) {

	$djs = [];
    
    foreach ($html as $elem) {

    		$nameel = $elem->find("h4 a",0);
    		$name = $nameel->innertext;

    		$imgel = $elem->find("img",0);
    		$property = 'data-srcset';
    		$srcset = $imgel->$property;

    		$srcset = explode(', ',$srcset);
    		
    		foreach ($srcset as &$src) {
    			$src = explode(' ',$src);
    		}

    		$img = end($srcset)[0];

            $djs[] = ['name' => makeCleanString($name),
        			  'img' => $img];
    
    }

    return $djs;
} 

function checkDJImgCache() {

    $cachefile = getOption("dj_img_cachefile");

    $cacheduration = getOption("dj_img_cache_duration");

    return checkCache($cachefile,$cacheduration); 
} 

function writeDJImageCache($djs) {
	writeCache(getOption("dj_img_cachefile"), $djs);
}

function getDJImages() {

	$djs = false;

	if (!checkDJImgCache()) {
		$html = getDJHTML();
		$djs = extractDJsFromHTML($html);
		writeDJImageCache($djs);
	} 

	$cachefile = getOption("dj_img_cachefile");
    $djs = json_decode(file_get_contents($cachefile));

	return $djs;
	
}

function searchForDJ($dj, $array) {
   foreach ($array as $key => $val) {
   		
       if ($val->name === $dj) {
           return $key;
       }
   }
   return null;
}

function returnDJImages() {
    
    $djs = getDJImages();
    if ($djs) {
        returnJSONSuccess($djs);
    } else {
        returnError(['No image data found']);
    }

}