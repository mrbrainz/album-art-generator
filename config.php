<?php

require_once "corefunctions.php";

/*********************
*
* Brainz Album Art Generator
* Configuration Options
*
*********************/


// Show PHP error messages
setOption("debug",true);

// Show Base64 copy/paste options
setOption("base64web",false);

// Store images locally for PDF generation; If false, will use Base64
setOption("localimgpdf", true);

// Doesn't delete file from /tmp after upload
setOption("retainfileupload", false);

setOption("google_creds","google-access-token.json");

setOption("cal_cachefile", __DIR__ . "/tmp/calendercache.json");

setOption("cal_cache_duration", 3600);