<?php require_once "config.php"; 

if (getOption('debug')) {
    ini_set("display_errors", "1");
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Promo Pic Generator By BrainZ</title>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
        <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection" />
        <link href="css/generator.css" type="text/css" rel="stylesheet" media="screen,projection" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body class="<?php echo (!getOption("base64web")) ? 'nobase64 ' : ''; ?><?php echo (getOption("localimgpdf")) ? 'localimgpdf ' : ''; ?>loaded">
        <nav class="grey darken-4" role="navigation">
            <div class="nav-wrapper container">
                <a id="logo-container" href="https://www.sub.fm" class="brand-logo">
                    <img src="img/sublogo.png" alt="sub.fm" />
                </a>
            </div>
        </nav>
        <div class="section no-pad-bot" id="index-banner">
            <div class="container">
                <h1 class="header center black-text">Promo Pic Generator</h1>
                <h3 class="header center black-text">by <a href="https://x.com/mrbrainz">@MrBrainz</a></h3>
                <br>
                <div class="row">
                    <form class="col s12 m6" id="djcreds"<?php echo (getOption("localimgpdf")) ? '  enctype="multipart/form-data"' : ""; ?>>
                        <input type="hidden" name="templateid" value="1" />
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="DJ Name" id="text1" type="text" class="validate" name="text1">
                                <label for="text1">DJ Name</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="e.g. Collab" id="text2" name="text2" type="text" class="validate">
                                <label for="text2">Second line</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="<?php echo date("D d M Y"); ?>" id="text3" name="text3" type="text" class="validate">
                                <label for="text3">Date of Show</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="3-5PM" id="text4" name="text4" type="text" class="validate">
                                <label for="text4">Time of Show</label>
                            </div>
                        </div>
                         <div class="row">
                            <div class="file-field input-field">
                              <div class="btn">
                                <span>Image <i class="material-icons right">attachment</i></span>
                                <input type="file" name="djimage" id="djimage" accept="image/*">
                              </div>
                              <div class="file-path-wrapper">
                                <input class="file-path validate" type="text">
                              </div>
                              <span class="helper-text">JPG & PNG accepted</span>
                            </div>
                        </div>
                        <div class="row">
                            <button class="btn waves-effect waves-light" id="artsubmit" type="submit" name="action">Generate <i class="material-icons right">rocket</i>
                            </button>
                        </div>
                    </form>
                    <div class="col s12 m6" id="prev-col">
                        <div class="row">
                            <div id="image-preview">
                                <div id="throbber"></div>
                                <img id="art-preview" alt="Art Preview" src="img/t1-default.jpg" /></div>
                            <div id="base64-code" class="input-field col s12">
                                <textarea id="base64-art" rows="10" readonly></textarea>
                                <span class="helper-text">Base64 Output</span>
                            </div>
                        </div>

                            <button class="btn waves-effect waves-light" id="copy-base64">Copy to Clipboard <i class="material-icons right">content_paste_go</i>
                            </button>  <button class="btn waves-effect waves-light" id="download">Download <i class="material-icons right">download</i>
                            </button>      
                    </div>
                </div>
            </div>
        </div>
        <div id="converter">
            <img id="converter-img" src="img/t1-default.jpg" alt="" />
        </div>
        <footer class="page-footer grey darken-4">
            <div class="container">
                <div class="row">
                    <div class="col l6 s12">
                        <h5 class="white-text" id="wbm"><a href="https://www.sub.fm" target="_blank">Sub.FM - Where Bass Matters</a></h5>
                    </div>
                    <div class="col l3 s12"></div>
                    <div class="col l3 s12"></div>
                </div>
            </div>
            <div class="footer-copyright">
                <div class="container"> 
                    <div class="row">
                        <div class="col s12">
                        Made by <a class="white-text" href="http://djbrainz.com">DJ BrainZ</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <script src="js/materialize.min.js"></script>
        <script type="text/javascript">
            
            <?php echo (getOption("localimgpdf")) ? 'let localimgpdf = true;' : 'let localimgpdf = false;' ?>

        </script>
        <script src="js/generator.js"></script>
    </body>
</html>
