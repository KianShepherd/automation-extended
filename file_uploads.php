<?php
require "func/func.php";
require("smarty-3.1.39/libs/Smarty.class.php");
$smarty = new Smarty();

$time = $_POST['timestamp'];
$filename = $_FILES["file"]["name"];

assert_input($filename, 1);

$cur_time = time();
$hash = md5($filename . $time);
$target_dir = "tmp_uploads/$hash/";

$target_file = $target_dir . basename($filename);
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if ($fileType != "zip") {
    echo $target_file . " - Please only upload automation export mod zips.";
    die();
} // file extension is known to be .zip

$file_name_no_ext = substr(basename($filename), 0, strlen(basename($filename)) - 4);
$jbeam_folder = $target_dir . "vehicle_data/vehicles/$file_name_no_ext/";

mkdir($target_dir . "vehicle_data/", 0777, true);
move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

$zip = new ZipArchive;
$res = $zip->open($target_file);
if ($res === TRUE) {
  $zip->extractTo("$target_dir" . "vehicle_data/");
  $zip->close();
} else {
  echo 'Error extracting archive';
  die();
}

$smarty->assign('hash', $hash);
$smarty->assign('filename' , basename($filename));
$smarty->assign('engine', getEngineData(jbeam_to_json($jbeam_folder, "camso_engine.jbeam")));
$smarty->assign('front_tires', getTireData(jbeam_to_json($jbeam_folder, "wheels_front.jbeam")['wheels_front']['pressureWheels']));
$smarty->assign('rear_tires', getTireData(jbeam_to_json($jbeam_folder, "wheels_rear.jbeam")['wheels_rear']['pressureWheels']));

$smarty->display('templates/ajax_replace.tpl');


deleteOldTmpFiles();