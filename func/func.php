<?php
function getEngineData($jbeam_content) {
    $engine_data = [];
    $engine_data['torque_curve'] = $jbeam_content['Camso_Engine']['mainEngine']['torque'];
    $engine_data['gear_ratios'] = $jbeam_content['Camso_Transmission']['gearbox']['gearRatios'];
    $engine_data['gear_ratio_str'] = implode(", ", $engine_data['gear_ratios']);
    $engine_data['max_rpm'] = end($jbeam_content['Camso_Engine']['mainEngine']['torque'])[0];
    $engine_data['torque_at_max'] = end($jbeam_content['Camso_Engine']['mainEngine']['torque'])[1];
    $engine_data['has_turbo'] = array_key_exists('Camso_Turbo', $jbeam_content);
    
    return $engine_data;
}

function getTireData($jbeam_content) {
    $tire_data = [];
    foreach ($jbeam_content as $line) {
        if (array_key_exists('treadCoef', $line)) {     
            $tire_data['treadcoef'] = $line['treadCoef'];
        }
        if (array_key_exists('slidingFrictionCoef', $line)) {     
            $tire_data['slidingcoef'] = $line['slidingFrictionCoef'];
        }
        if (array_key_exists('frictionCoef', $line)) {     
            $tire_data['friccoef'] = $line['frictionCoef'];
        }
    }

    return $tire_data;
}


function GetStrippedFileContent($jbeam_folder, $filename) {
    $fileStr = file_get_contents($jbeam_folder . $filename);
    $string = "";
    $commentTokens = array(T_COMMENT, T_DOC_COMMENT);
    $tokens = token_get_all($fileStr);

    foreach ($tokens as $token) {    
    if (is_array($token)) {
        if (in_array($token[0], $commentTokens)) {
            continue;
        }
        
        $token = $token[1];
    }
    $string .= $token;
    }

    $jbeam_content = "";
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $string) as $line) {
        $line = preg_replace("/\/\/.*$/", "", $line);
        if (trim($line) == "") {
            continue;
        }
        $jbeam_content .= $line . "\n";
    }
    return $jbeam_content;
}

function jbeam_to_json($jbeam_folder, $filename) {
    $file_content = preg_split("/\n/", GetStrippedFileContent($jbeam_folder, $filename));
    $full_content = "";
    foreach($file_content as $line) {
        $_line = trim($line);
        $full_content .= preg_replace("/,,/",",", ($_line . ","));
    }
    $full_content = preg_replace("/,]/","]", $full_content);
    $full_content = preg_replace("/,}/","}", $full_content);
    $full_content = preg_replace("/\[,\]/","[]", $full_content);
    $full_content = preg_replace("/{,/","{", $full_content);
    $full_content = preg_replace("/\[,/","[", $full_content);
    $full_content = preg_replace("/:,/",":", $full_content);
    $full_content = preg_replace("/,,/",",", ($full_content));
    $full_content = preg_replace("/(\d) {/","$1, {", ($full_content));
    $full_content = preg_replace("/\"\s?{/","\", {", ($full_content));
    $full_content = substr($full_content, 0, -1);

    return json_decode($full_content, true);
}


function deleteOldTmpFiles() {
    $scan = scandir('tmp_uploads/');
    foreach($scan as $folder) {
        if ($folder == "." || $folder == "..") {
            continue;
        }
        if (is_dir("tmp_uploads/$folder")) {
            $time = filemtime("tmp_uploads/$folder");
            $timediff = time() - $time; // time difference in seconds
            if ($timediff > 1 * 60) { // x * 60 to delete after x minutes
                deleteDir("tmp_uploads/$folder");
            }
        }
    } 
}


function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        echo "error";
        die();
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}


function assert_input($user_input, $max_dots) {
    if (substr_count($user_input, "/") > 0 || substr_count($user_input, ".") > $max_dots) {
        echo "please dont mess with hidden tags.";
        die();
    }
}


function pprint($output) {
    echo "<pre>" . var_export($output, true) . "</pre>";
}