<?php

require_once "corefunctions.php";

/*********************
*
* Brainz Album Art Generator
* Configuration Options
*
*********************/


// Show PHP error messages
setOption("debug",false);

// Show Base64 copy/paste options
setOption("base64web",false);

// Store images locally for PDF generation; If false, will use Base64
setOption("localimgpdf", true);
