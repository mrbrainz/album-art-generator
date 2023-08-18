<!DOCTYPE html>
<html>
    <head>
        <title>Album Art Generator By BrainZ</title>
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
                <br>
                <h1 class="header center black-text">Album Art Generator By BrainZ</h1>
                <div class="row">
                    <form class="col s12 m4">
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="DJ Name" id="text-1" type="text" class="validate" name="text-1">
                                <label for="text-1">DJ Name</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="e.g. Collab" id="text-2" name="text-2" type="text" class="validate">
                                <label for="text-2">Second line</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="12 Aug 2023" id="text-3" name="text-3" type="text" class="validate">
                                <label for="text-3">Date of Show</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <input placeholder="3-5PM" id="text-4" name="text-4" type="text" class="validate">
                                <label for="text-4">Time of Show</label>
                            </div>
                        </div>

                        <button class="btn waves-effect waves-light" type="submit" name="action">Generate <i class="material-icons right">rocket</i>
                        </button>
                        
                    </form>
                    <div class="col s12 m8" id="prev-col">
                        <div id="image-preview"><img id="art-preview" alt="Art Preview" src="img/t1-default.jpg" /></div>
                        <div id="base64-code" class="input-field col s12">
                            <textarea id="base64-art" class="materialize-textarea"></textarea>
                            <button class="btn waves-effect waves-light" id="copy-base64">Copy to Clipboard <i class="material-icons right">content_paste_go</i>
                        </button>                     
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="page-footer grey darken-4">
            <div class="container">
                <div class="row">
                    <div class="col l6 s12">
                        <h5 class="white-text">Sub.FM</h5>
                        <p class="white-text">Where Bass Matters</p>
                    </div>
                    <div class="col l3 s12"></div>
                    <div class="col l3 s12"></div>
                </div>
            </div>
            <div class="footer-copyright">
                <div class="container"> Made by <a class="white-text" href="http://djbrainz.com">DJ BrainZ</a>
                </div>
            </div>
        </footer>
        <script src="js/materialize.min.js"></script>
        <script src="js/generator.js"></script>
    </body>
</html>