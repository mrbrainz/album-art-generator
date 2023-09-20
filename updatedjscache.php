<?php // Update Sub.FM album art cache

require_once "config.php";

require_once "vendor/wikia/simplehtmldom/simple_html_dom.php";

function getDJHTML() {

	$containerel = ".pt-cv-page > div";
    
    $html = file_get_html('https://www.sub.fm/djs/');

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
	} else {
		$cachefile = getOption("dj_img_cachefile");
        $djs = json_decode(file_get_contents($cachefile));
	}

	return $djs;
	
}

function returnDJImages() {
    
    $djs = getDJImages();
    if ($djs) {
        returnJSONSuccess($djs);
    } else {
        returnError(['No image data found']);
    }

}

returnDJImages();