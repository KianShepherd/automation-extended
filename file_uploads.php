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

mkdir($target_dir);
mkdir($target_dir . "vehicle_data/");
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

//pprint($page_data); 
$engine_content = GetStrippedFileContent($jbeam_folder, "camso_engine.jbeam");
$engine_data = getEngineData($engine_content);
$gear_str = substr($engine_data['gear_ratios'], 1, count($engine_data['gear_ratios']) - 2);

$front_content = GetStrippedFileContent($jbeam_folder, "wheels_front.jbeam");
$front_data = getTireData($front_content);

$rear_content = GetStrippedFileContent($jbeam_folder, "wheels_rear.jbeam");
$rear_data = getTireData($rear_content);

$smarty->assign('hash', $hash);
$smarty->assign('filename' , basename($filename));
$smarty->assign('gear_str', $gear_str);
$smarty->assign('max_rpm', $engine_data['max_rpm']);
$smarty->assign('torque_at_max', $engine_data['torque_at_max']);
$smarty->assign('front', $front_data);
$smarty->assign('rear', $rear_data);

$smarty->display('templates/ajax_replace.tpl');


deleteOldTmpFiles();