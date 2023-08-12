<?php

require_once __DIR__ . '/vendor/autoload.php';

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$templatehtml = file_get_contents('art.html');

$stamp = time();
$tempdir = "temp";
$prefix = "sub-art-";
$outputfile = $tempdir."/".$prefix.$stamp;
$stylesheet = file_get_contents('normalize.css');
$stylesheet .= file_get_contents('style.css');

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
$mpdf->WriteHTML($templatehtml,\Mpdf\HTMLParserMode::HTML_BODY);
/* $mpdf->Output($outputfile.".pdf", 'F'); */

$pdfoutput = $mpdf->Output('', 'S');


$image = new Imagick();
$image->readImageBlob($pdfoutput);
$image->setResolution( 1080, 1080 );
$image->setImageFormat( "jpg" );
$image->setImageCompressionQuality(80);
//$image->writeImage($outputfile.'.png');
$imageBlob = "data:image/jpg;base64, ".base64_encode($image->getImageBlob());
echo '<img src="'.$imageBlob.'" alt="" />';
exit();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
</body>
</html>