<?php

ini_set("display_errors", "1");
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

$startTime = microtime(true);  

require_once __DIR__ . '/vendor/autoload.php';

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
        $output = json_decode($_POST['payload'], true);
        // var_dump($output);
        foreach($output as $key=>$value) {
            $output[$key] = urldecode($value);
        }
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

function createStylesheetFromLocal($id,$imagefile) {
    $stylesheet = file_get_contents('normalize.css');
    $stylesheet .= file_get_contents('template-'.intval($id).'.css');
    $imagefile = sanitiseFilename($imagefile);
    if (file_exists("djs/".$imagefile)) {
        $imagefile = "djs/".$imagefile;
    } else {
        $imagefile = "img/t".intval($id)."-default.jpg";
    }
        
    $stylesheet = str_replace("{{image}}", $imagefile, $stylesheet);
        
    return $stylesheet;
}

function createStyleSheetFromBase64($id,$blob) {
    $stylesheet = file_get_contents('normalize.css');
    $stylesheet .= file_get_contents('template-'.intval($id).'.css');
    
    $blobparts = explode(";base64,",$blob);

    if (sizeof($blobparts) == 2) { 


        if(substr( $blobparts[0], 0, 11 ) != "data:image/") {
            echo substr( $blobparts[0], 0, 11 );
            $blob = "img/t".intval($id)."-default.jpg";
        }  

        /* if (!is_base64($blobparts[1])) {
            $blob = "img/t".intval($id)."-default.jpg";
        }*/
    } else {
        $blob = "img/t".intval($id)."-default.jpg";
    }
        
    $stylesheet = str_replace("{{image}}", '"'.$blob.'"', $stylesheet);
        
    return $stylesheet;
}

function createPDFBlob($stylesheet,$html,$outputanddie = false) {
    
    // Add config for custom font folder
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => __DIR__."/tmp",
        'mode' => 'utf-8', 
        'dpi' => 72,
        'img_dpi' => 72,
        'format' => [381, 381],
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/fonts',
        ]),
        'fontdata' => $fontData +[ 
            'pragmatica' => [
                'R' => 'PragmaticaMedium.ttf'
            ],
            'nimbus-sans-ultralight' => [
                'R' => 'NimbusSansNovusT-UltraLight.ttf'
            ]
            ,
            'nimbus-sans-regular' => [
                'R' => 'NimbusSanL-Reg.ttf'
            ],
            'nimbus-sans-bold' => [
                'R' => 'NimbusSanL-Bol.ttf'
            ]
        ]
        ]);
    
    //$mpdf->showImageErrors = true;
    $mpdf->simpleTables = true;
    $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
    
    if ($outputanddie) {
        $mpdf->Output('', 'I'); exit();
    }

    return $mpdf->Output('', 'S');

    // $filename = $mpdf->tempDir.'/'.'djpic-'.time().'-'.rand(0,99999).'.pdf';

    //$mpdf->Output($filename, 'F');

    // return $filename;
}

function createArtHTML($text1 = "", $text2 = "", $text3 = "", $text4 = "") {
    if (!$text1) {
        $text1 = "";
    } else {
        $text1 = htmlspecialchars( $text1 ); 
    }
    
    if (!$text2) {
        $text2 = "";
    } else {
        $text2 = htmlspecialchars( $text2 ); 
    }
    
    if (!$text3) {
        $text3 = "";
    } else {
        $text3 = htmlspecialchars(  $text3 ); 
    }
    
    if (!$text4) {
        $text4 = "";
    } else {
        $text4 = htmlspecialchars(  $text4 ); 
    }
    
    // Get HTML template
    $template = file_get_contents('template.html');
    
    // Remove script tags
    $template = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $template);
    
    // Strip other tage for security
    $template = trim(strip_tags($template, ['body', 'div', 'h1', 'h2', 'h3', 'h4']));
    
    // replace show attribute tokens with defined values
    $template = str_replace("{{text1}}",$text1,$template);
    $template = str_replace("{{text2}}",$text2,$template);
    $template = str_replace("{{text3}}",$text3,$template);
    $template = str_replace("{{text4}}",$text4,$template);
                           
    return $template;
    
}

function pdfToBase64($blob,$format) {
    if ($format !== "jpg" && $format !== "png" && $format !== "jpeg") {
        $format = "jpg";
    }

    $image = new Imagick();
    $image->readImageBlob($blob);
    $image->setResolution( 1080, 1080 );
    $image->setImageFormat( $format );
    $image->setImageCompressionQuality(80);
    $imageBase64 = "data:image/".$format.";base64, ".base64_encode($image->getImageBlob());
    
    return $imageBase64;
    
}

function pdfToTempImage($path,$format) {

    if ($format !== "jpg" && $format !== "png" && $format !== "jpeg") {
        $format = "jpeg";
    }

    if ($format === "jpg") {
        $format = "jpeg";
    }



}

function brainzTest() {

    $file = "djs/t1-brainz.jpg";
    $mimetype = mime_content_type($file);
    //$mimetype = ($mimetype === "image/jpeg") ? "image/jpg" : $mimetype;

    $base64file = 'data:'.$mimetype.';base64,'.base64_encode(file_get_contents($file));

    $brainzstyle = createStyleSheetFromBase64(1,$base64file);

    //var_dump($brainzstyle); exit();
    
    $arthtml = createArtHTML("DJ Brainz","With MC Whistles","12 Aug 2023", "3-5PM");
    print_r($arthtml); exit();                   
    $pdfoutput = createPDFBlob($brainzstyle,$arthtml,true);
    $art = pdfToBase64($pdfoutput,"jpg");
    //$art = "performance";
    return $art;

}

function createImageFromPost() {
    $postdata = readPostData();

    $id = (isset($postdata['id'])) ? $postdata['id'] : 1;
    $image = (isset($postdata['img'])) ? $postdata['img'] : false;
    $style = createStyleSheetFromBase64($id,$image);

    $text1 = (isset($postdata['text1'])) ? $postdata['text1'] : "";
    $text2 = (isset($postdata['text2'])) ? $postdata['text2'] : "";
    $text3 = (isset($postdata['text3'])) ? $postdata['text3'] : "";
    $text4 = (isset($postdata['text4'])) ? $postdata['text4'] : "";

    $arthtml = createArtHTML($text1,$text2,$text3,$text4);
    $pdfoutput = createPDFBlob($style,$arthtml);
    $art = pdfToBase64($pdfoutput,"jpg");
    return $art;
}

if (isPost()) {
    http_response_code(200);
    try {
        echo json_encode(['img' => createImageFromPost()]); 
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        http_response_code(400);
    }
    exit();
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="normalize.css">
</head>
<body>
    <?php echo '<img src="'.brainzTest().'" alt="" />'; ?>
</body>
</html>

<?php 

$endTime = microtime(true);  
    $elapsed = $endTime - $startTime;
    echo "<!-- Execution time : $elapsed seconds -->";

exit();