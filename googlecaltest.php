<?php
function googleTest() {
	echo "Google Test";
} ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Google Calendar Test By BrainZ</title>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
        <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection" />
        <link href="css/generator.css" type="text/css" rel="stylesheet" media="screen,projection" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
        <nav class="grey darken-4" role="navigation">
            <div class="nav-wrapper container">
                <a id="logo-container" href="https://sub.fm" class="brand-logo">
                    <img src="img/sublogo.png" alt="sub.fm" />
                </a>
            </div>
        </nav>
        <div class="section no-pad-bot" id="index-banner">
            <div class="container">
                <h1 class="header center black-text">Google Calendar Test</h1>
                <h3 class="header center black-text">by <a href="https://x.com/mrbrainz">@MrBrainz</a></h3>
                <br>
                <div class="row">
                    <?php googleTest(); ?>
                </div>
            </div>
        </div>
    </body>
</html>