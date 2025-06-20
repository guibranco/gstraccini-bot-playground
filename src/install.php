<?php

/**
 * Checks if the current environment is a test environment.
 *
 * This function determines whether the application is running in a test environment,
 * which can be useful for enabling or disabling certain features or behaviors during testing.
 *
 * @return bool Returns true if the environment is a test environment, false otherwise.
 */
function checkIfIsTestEnvironment()
{
    $postman = isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], "PostmanRuntime") !== false;
    $localhost = $_SERVER["HTTP_HOST"] === "localhost:8000";
    $debug = isset($_GET["debug"]) && $_GET["debug"] == "true";

    if ($postman || $localhost || $debug) {
        return true;
    }

    return false;
}

/**
 * Extracts the contents of a ZIP archive to a specified destination.
 *
 * @param string $file        The path to the ZIP file to be extracted.
 * @param string $destination The directory where the contents will be extracted.
 * @return bool               Returns true on success, false on failure.
 */
function unzip($file, $destination)
{
    $zip = new ZipArchive();
    $res = $zip->open($file);

    if ($res !== true) {
        return false;
    }

    $zip->extractTo($destination);
    $zip->close();
    return true;
}

/**
 * Recursively delete a directory and all of its contents 🧹
 *
 * @param string $dir The directory path to delete
 * @return bool True on success, false on failure
 */
function deleteDirectoryRecursive(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $items = scandir($dir);
    if ($items === false) {
        return false;
    }

    foreach ($items as $item) {

        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            if (!deleteDirectoryRecursive($path)) {
                return false;
            }
        } else {
            if (!unlink($path)) {
                return false;
            }
        }
    }

    return rmdir($dir);
}

/**
 * Entry point for the installation process.
 *
 * This function serves as the main execution point for the installation script.
 * It does not accept any parameters and does not return any value.
 *
 * @return void
 */
function main(): void
{
    if (checkIfIsTestEnvironment()) {
        http_response_code(400);
        die("This is a test environment. Please use the production environment.");
    }

    $deployFile = "deploy.zip";
    $installFile = "install.php";

    if (!file_exists($deployFile)) {
        http_response_code(404);
        die("Deploy file not found.");
    }

    deleteDirectoryRecursive("./features");
    deleteDirectoryRecursive("./pages");
    deleteDirectoryRecursive("./templates");
    deleteDirectoryRecursive("./tools");
    unzip($deployFile, "./");
    unlink($deployFile);
    unlink($installFile);
}

main();