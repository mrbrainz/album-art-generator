<?php

require_once __DIR__ . '/vendor/autoload.php';

function isPost() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return true;
    }
    return false;
}

function readPostData() {
    $output = [];
    if (isset($_POST['djname'])) {
        $output['djname'] = $_POST['djname'];
    } else {
        $output['djname'] = "";
    }
    if (isset($_POST['subtitle'])) {
        $output['subtitle'] = $_POST['subtitle'];
    } else {
        $output['subtitle'] = "";
    }
    if (isset($_POST['dateline'])) {
        $output['dateline'] = $_POST['dateline'];
    } else {
        $output['dateline'] = "";
    }
    if (isset($_POST['station'])) {
        $output['station'] = $_POST['station'];
    } else {
        $output['station'] = "";
    }
    if (isset($_POST['image']) && is_base64_image($_POST['image'])) {
        $output['image'] = $_POST['image'];
    } else {
        $output['image'] = ""; 
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
    
        if(!str_starts_with($s[0],"data:image/")) {
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
    
        if(!str_starts_with($blobparts[0],"data:image/")) {
            $blob = "img/t".intval($id)."-default.jpg";
        }  

        if (!is_base64($blobparts[1])) {
            $blob = "img/t".intval($id)."-default.jpg";
        }
    } else {
        $blob = "img/t".intval($id)."-default.jpg";
    }
        
    $stylesheet = str_replace("{{image}}", $blob, $stylesheet);
        
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

function brainzTest() {

    $brainzstyle = createStyleSheetFromBase64(1,"data:image/jpg;base64,".base64_encode(file_get_contents("djs/t1-brainz.jpg")));
    // echo var_dump($brainzstyle); exit();
    $arthtml = createArtHTML("DJ Brainz",null,"Saturday x 1500 - 1700 GMT", "Sub.fm");                      
    $pdfoutput = createPDFBlob($brainzstyle,$arthtml);
    $art = pdfToBase64($pdfoutput,"jpg");

    return $art;

}

function createImageFromPost() {
    $postdata = readPostData();
    $style = createStyleSheetFromBase64(1,$postdata['image']);
    // echo var_dump($brainzstyle); exit();
    $arthtml = createArtHTML($postdata['djname'],$postdata['subtitle'],$postdata['dateline'], $postdata['station']);                      
    $pdfoutput = createPDFBlob($style,$arthtml);
    $art = pdfToBase64($pdfoutput,"jpg");
    return $art;
}

if (isPost()) {
    echo createImageFromPost(); exit();
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

<?php exit();