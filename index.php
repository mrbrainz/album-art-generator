<?php
$startTime = microtime(true);  

require_once __DIR__ . '/vendor/autoload.php';

function isPost() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']){
          $this->output->set_status_header(400, 'No Remote Access Allowed');
          exit; //just for good measure
        }
        return true;
    }
    return false;
}

function readPostData() {
    $output = [];
    if (isset($_POST['text1'])) {
        $output['text1'] = $_POST['text1'];
    } else {
        $output['text1'] = "";
    }
    if (isset($_POST['text2'])) {
        $output['text2'] = $_POST['text2'];
    } else {
        $output['text2'] = "";
    }
    if (isset($_POST['text3'])) {
        $output['text3'] = $_POST['text3'];
    } else {
        $output['text3'] = "";
    }
    if (isset($_POST['text4'])) {
        $output['text4'] = $_POST['text4'];
    } else {
        $output['text4'] = "";
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
    $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
    
    return $mpdf->Output('', 'S');
    //$mpdf->Output('', 'I'); exit();
}

function createArtHTML($text1 = "", $text2 = "", $text3 = "", $text4 = "") {
    if (!$text1) {
        $text1 = "";
    } else {
        $text1 = filter_var ( $text1, FILTER_SANITIZE_STRING); 
    }
    
    if (!$text2) {
        $text2 = "";
    } else {
        $text2 = filter_var ( $text2, FILTER_SANITIZE_STRING); 
    }
    
    if (!$text3) {
        $text3 = "";
    } else {
        $text3 = filter_var ( $text3, FILTER_SANITIZE_STRING); 
    }
    
    if (!$text4) {
        $text4 = "";
    } else {
        $text4 = filter_var ( $text4, FILTER_SANITIZE_STRING); 
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
    if ($format !== "jpg" && $format !== "png") {
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

function brainzTest() {

    $brainzstyle = createStyleSheetFromBase64(1,"data:image/jpg;base64,".base64_encode(file_get_contents("djs/t1-brainz.jpg")));
    // echo var_dump($brainzstyle); exit();
    $arthtml = createArtHTML("DJ Brainz","With MC Whistles","12 Aug 2023", "3-5PM");                      
    $pdfoutput = createPDFBlob($brainzstyle,$arthtml);
    $art = pdfToBase64($pdfoutput,"jpg");
    //$art = "performance";
    return $art;

}

function createImageFromPost() {
    $postdata = readPostData();
    $style = createStyleSheetFromBase64(1,$postdata['image']);
    // echo var_dump($brainzstyle); exit();
    $arthtml = createArtHTML($postdata['text1'],$postdata['text2'],$postdata['text3'], $postdata['text4']);                      
    $pdfoutput = createPDFBlob($style,$arthtml);
    $art = pdfToBase64($pdfoutput,"jpg");
    return $art;
}

if (isPost()) {
    header('Status: 200');
    echo createImageFromPost(); 
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