<?php

require_once __DIR__ . '/vendor/autoload.php';




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

function createStylesheet($id,$imagefile) {
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

function createPDFBlob($stylesheet,$html) {
    
    // Add config for custom font folder
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 
        'format' => [282.3, 282.3],
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
            ]
        ]
        ]);
    $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
    
    return $mpdf->Output('', 'S');
}

function createArtHTML($djname = "", $subtitle = "", $dateline = "", $station = "") {
    if (!$djname) {
        $djname = "";
    } else {
        $djname = filter_var ( $djname, FILTER_SANITIZE_STRING); 
    }
    
    if (!$subtitle) {
        $subtitle = "";
    } else {
        $subtitle = filter_var ( $subtitle, FILTER_SANITIZE_STRING); 
    }
    
    if (!$dateline) {
        $dateline = "";
    } else {
        $dateline = filter_var ( $dateline, FILTER_SANITIZE_STRING); 
    }
    
    if (!$station) {
        $station = "";
    } else {
        $station = filter_var ( $station, FILTER_SANITIZE_STRING); 
    }
    
    // Get HTML template
    $template = file_get_contents('template.html');
    
    // Remove script tags
    $template = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $template);
    
    // Strip other tage for security
    $template = trim(strip_tags($template, ['body', 'div', 'h1', 'h2', 'h3', 'h4']));
    
    // replace show attribute tokens with defined values
    $template = str_replace("{{djname}}",$djname,$template);
    $template = str_replace("{{subtitle}}",$subtitle,$template);
    $template = str_replace("{{dateline}}",$dateline,$template);
    $template = str_replace("{{station}}",$station,$template);
                           
    return $template;
    
}

function pdfToBase64($blob,$format) {
    if ($format !== "jpg" && $format !== "png") {
        $format = "jpg";
    }

    $image = new Imagick();
    $image->readImageBlob($blob);
    $image->setResolution( 800, 800 );
    $image->setImageFormat( $format );
    $image->setImageCompressionQuality(80);
    $imageBase64 = "data:image/".$format.";base64, ".base64_encode($image->getImageBlob());
    
    return $imageBase64;
    
}

$brainzstyle = createStylesheet(1,"brainz.jpg");
$arthtml = createArtHTML("DJ Brainz",null,"Saturday x 1500 - 1700 GMT", "Sub.fm");                      
$pdfoutput = createPDFBlob($brainzstyle,$arthtml);
$art = pdfToBase64($pdfoutput,"jpg");

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
    <?php echo '<img src="'.$art.'" alt="" />'; ?>
</body>
</html>

<?php exit();