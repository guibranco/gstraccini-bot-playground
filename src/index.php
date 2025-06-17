<?php
error_reporting(E_ALL);
$featuresDir = "features";
$fidoDir = "FIDO";
$pagesDir = "pages";
$templatesDir = "templates";

function read($dir)
{
$ignorePaths = array(".", "..");
$ignoredExtensions = array("sql", "txt", "css", "js");
$handle = opendir($dir);

while ($file = readdir($handle)) {
$filePath = $dir . "/" . $file;

if (in_array($file, $ignorePaths)) {
continue;
}

if (is_dir($filePath)) {
echo "<li class='list-group-item'><a href='" . $filePath . "'>" .
        preg_replace('/(?<!^)([A-Z])/', ' \\1', $file) . "</a></li>\r\n";
    } elseif (is_file($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (in_array($ext, $ignoredExtensions)) {
    continue;
    }
    echo "<li class='list-group-item'><a
            href='" . $filePath . "'>" . $file . "</a></li>\r\n";
    }
    }

    closedir($handle);
    }
    ?>

    <!DOCTYPE html>
    <html>

        <head>
            <meta charset="utf-8" />
            <meta name="viewport"
                content="width=device-width, initial-scale=1.0">
            <meta name="robots" content="noindex,nofollow">
            <title>GStraccini-bot | Playground</title>
            <link type="text/css" media="all" rel="stylesheet"
                href="bootstrap.min.css" />
            <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        </head>

        <body>
            <div class="container-fluid">
                <div class="row text-center">
                    <div class="row col-md-12" style="background:#0dd682;">
                        <div class="col-xs-12 col-md-3">
                            <img
                                src="https://bot.straccini.com/images/logo-white.png"
                                alt="GStraccini-bot | Playground" />
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <h1>Playground</h1>
                        </div>
                    </div>
                    <div class="row col-md-12" style="margin-top:50px;">
                        <div class="col-md-3">
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Features</h3>
                                </div>
                                <ul class="list-group">
                                    <?php read($featuresDir) ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-danger">
                                <div class="panel-heading">
                                    <h3 class="panel-title">FIDO</h3>
                                </div>
                                <ul class="list-group">
                                    <?php read($fidoDir) ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Pages</h3>
                                </div>
                                <ul class="list-group">
                                    <?php read($pagesDir) ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Templates</h3>
                                </div>
                                <ul class="list-group">
                                    <?php read($templatesDir) ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer>
                <div class="row col-md-12" style="background:#0dd682;">
                    Developed by <a
                        href="https://guilherme.stracini.com.br/?utm_campaign=project&amp;utm_media=bot-playground&amp;utm_source=straccini.com"
                        target="_blank" rel="noopener noreferrer"><img
                            alt="Guilherme Branco Stracini"
                            class="image-rounded image-responsive"
                            loading="lazy"
                            src="https://guilherme.stracini.com.br/photo.png"
                            style="width: 24px; height: 44px;"></a> <a
                        href="https://guilherme.stracini.com.br/?utm_campaign=project&amp;utm_media=bot-playground&amp;utm_source=straccini.com"
                        target="_blank" rel="noopener noreferrer">Guilherme
                        Branco
                        Stracini</a> | Repository <a
                        href="https://github.com/guibranco/gstraccini-bot-playground"
                        target="_blank" rel="noopener noreferrer"><svg
                            height="32px"
                            fill="#FFFFFF" role="img" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"><title>GitHub</title><path
                                d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"></path></svg></a><a
                        href="https://github.com/guibranco/gstraccini-bot-playground"
                        target="_blank"
                        rel="noopener noreferrer">GitHub</a>
                </div>
            </footer>
        </body>
        <script src="https://code.jquery.com/jquery-3.3.1.js"
            integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
            crossorigin="anonymous"></script>
        <script
            src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>

    </html>
