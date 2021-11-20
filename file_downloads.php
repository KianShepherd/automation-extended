<?php
require "func/func.php";
require "func/modify_files.php";

assert_input($_POST['hash'], 0);
assert_input($_POST['filename'], 1);

$filePath_ = "tmp_uploads/$_POST[hash]/$_POST[filename]";
$fileName = $_POST['filename'];

unlink($filePath_);

$zip = new ZipArchive();
$zip->open($filePath_, ZipArchive::CREATE);
$rootPath = realpath("tmp_uploads/$_POST[hash]/vehicle_data");

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();
//die();
header("Content-disposition: attachment; filename=" . $fileName);
header("Content-type: application/zip");
header('Content-Transfer-Encoding: Binary');
header("Content-length: " . filesize($filePath_));
header("Pragma: no-cache"); 
header("Expires: 0"); 
readfile($filePath_);
