<?php

require_once "config.php";

if (getOption('debug')) {
    ini_set("display_errors", "1");
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);

$startTime = microtime(true);  
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

    } else {
        $blob = "img/t".intval($id)."-default.jpg";
    }
        
    $stylesheet = str_replace("{{image}}", '"'.$blob.'"', $stylesheet);
        
    return $stylesheet;
}


function createStyleSheetFromLocal($id = 1,$file = "") {

    $id = intval($id);
    $stylesheet = file_get_contents('normalize.css');
    $stylesheet .= file_get_contents('template-'.$id.'.css');

    if (!file_exists($file)) { 
            $file = "img/t".$id."-default.jpg";
        } 
        
    $stylesheet = str_replace("{{image}}", '"'.$file.'"', $stylesheet);
    return $stylesheet;

}

function createPDFBlob($stylesheet,$html,$outputanddie = false) {
    
    // Add config for custom font folder
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata']; 

    //print_r($fontData); exit;

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
            'nimbus-sans' => [
                'R' => 'NimbusSanL-Reg.ttf',
                'B' => 'NimbusSanL-Bol.ttf'
            ]
        ]
        ]);
    
    //$mpdf->showImageErrors = true;
    $mpdf->simpleTables = true;
    $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
    
    if ($outputanddie) {
        $mpdf->Output('', 'I'); exit();
        //$mpdf->Output('tmp/temp.pdf', 'F'); exit();
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

function storeTempImage($file) {
    $tempfolder = __DIR__."/tmp";
    $path_info = pathinfo($file['name']);
    $filename = 'djpic-'.time().'-'.rand(0,99999).'.'.$path_info['extension'];
    move_uploaded_file($file['tmp_name'], $tempfolder."/".$filename);
    return $filename;
}

function createImageFromPost() {
    $postdata = readPostData();

    $id = (isset($postdata['id'])) ? $postdata['id'] : 1;
    $image = (isset($postdata['img'])) ? $postdata['img'] : false;
    $file = (isset($postdata['imgfile'])) ? $postdata['imgfile'] : false;

    //var_dump($file); exit();
    
    if (getOption('localimgpdf') && $file) {
        $filename = "tmp/".storeTempImage($file);
        if (file_exists($filename)) {
            $style = createStyleSheetFromLocal($id,$filename);
        } else {
            $style = createStyleSheetFromBase64($id,$image);
        }
    } else {
        $style = createStyleSheetFromBase64($id,$image);
    }

    

    $text1 = (isset($postdata['text1'])) ? $postdata['text1'] : "";
    $text2 = (isset($postdata['text2'])) ? $postdata['text2'] : "";
    $text3 = (isset($postdata['text3'])) ? $postdata['text3'] : "";
    $text4 = (isset($postdata['text4'])) ? $postdata['text4'] : "";

    $arthtml = createArtHTML($text1,$text2,$text3,$text4);
    $pdfoutput = createPDFBlob($style,$arthtml);
    if (getOption('localimgpdf') && $file && !getOption("retainfileupload")) {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
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

function brainzTest() {

    $file = "djs/t1-brainz.jpg";

    if (getOption('localimgpdf')) {
        $brainzstyle = createStyleSheetFromLocal(1,$file);
    } else {
        $mimetype = mime_content_type($file);
        $base64file = 'data:'.$mimetype.';base64,'.base64_encode(file_get_contents($file));
        $brainzstyle = createStyleSheetFromBase64(1,$base64file);
    }
    
    $arthtml = createArtHTML("DJ Brainz","With MC Whistles","12 Aug 2023", "3-5PM");

    $pdfoutput = createPDFBlob($brainzstyle,$arthtml);

    $art = pdfToBase64($pdfoutput,"jpg");

    return $art;

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

if (getOption('debug')) {
    $endTime = microtime(true);  
    $elapsed = $endTime - $startTime;
    echo "<!-- Execution time : $elapsed seconds -->";
}

exit();