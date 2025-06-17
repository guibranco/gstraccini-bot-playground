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
            echo "<li class='list-group-item'><a href='" . $filePath . "'>" . preg_replace('/(?<!^)([A-Z])/', ' \\1', $file) . "</a></li>\r\n";
        } elseif (is_file($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (in_array($ext, $ignoredExtensions)) {
                continue;
            }
            echo "<li class='list-group-item'><a href='" . $filePath . "'>" . $file . "</a></li>\r\n";
        }
    }

    closedir($handle);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>GStraccini-bot | Playground</title>
    <link type="text/css" media="all" rel="stylesheet" href="bootstrap.min.css" />
    <link type="text/css" media="all" rel="stylesheet" href="styles.css" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="container-fluid">
        <div class="row text-center">
            <div class="row col-md-12" style="background:#0dd682;">
                <div class="col-xs-12 col-md-3">
                    <img src="https://bot.straccini.com/images/logo-white.png" alt="GStraccini-bot | Playground" />
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

    <footer class="footer">
        <div class="container-fluid">
            <div class="creator-info">
                <img src="https://guilherme.stracini.com.br/photo.png" alt="Guilherme Branco Stracini" class="creator-photo">
                <a href="https://guilherme.stracini.com.br/" target="_blank" class="creator-name">
                    Created by Guilherme Branco Stracini
                </a>
            </div>
            <div class="github-link">
                <a href="https://github.com/guibranco/gstraccini-bot-playground" target="_blank">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 5px;">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                    View on GitHub
                </a>
            </div>
        </div>
    </footer>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
    crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
    integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
    crossorigin="anonymous"></script>

</html>
