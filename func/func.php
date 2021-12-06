<?php
function getEngineData($jbeam_content) {
    $liness = preg_split("/((\r?\n)|(\r\n?))/", $jbeam_content);
    $len = count($liness);
    $i = 0;
    $matched_gear_ratio = false;
    
    $engine_data = [];
    $lines = [];
    foreach ($liness as $line) {
        $lines[] = trim($line);
    }

    while (true) {
        $matches = [];
        
        preg_match("/(\"gearRatios\":)(\[[\-\d,\. ]+\])/", $lines[$i], $matches);
        if (count($matches) > 2 && $matched_gear_ratio == false) {
            $matched_gear_ratio = true;
            $engine_data['gear_ratios'] = $matches[2]; $i += 1; continue;
        }

        preg_match("/\[\"rpm\", \"torque\"\]/", $lines[$i], $matches);
        //pprint($matches);
        if (count($matches) > 0) {
            // match torque curve
            $i += 1; 
            $torque_values = [];
            while (true) {
                $matches = [];
                if ($lines[$i] == "],") { $i += 1; break; }
                $torque_values[] = substr($lines[$i], 0, strlen($lines[$i]) - 1);
                $i += 1;
            }
            $engine_data['torque_curve'] = $torque_values;
            continue;
        }

        
        $i += 1;
        if ($i >= $len) {
            break;
        }
    }
    $torque_curve = $engine_data['torque_curve'];
    $matches = [];
    preg_match("/([\d]+)/", $torque_curve[count($torque_curve) - 1], $matches);
    $max_rpm = $matches[1];
    $engine_data['max_rpm'] = $max_rpm;

    $matches = [];
    preg_match("/([\d]+\.[\d]+)/", $torque_curve[count($torque_curve) - 1], $matches);
    $torque_at_max = $matches[1];
    $engine_data['torque_at_max'] = $torque_at_max;
    //pprint($jbeam_content);
    return $engine_data;
}


function getTireData($jbeam_content) {
    $liness = preg_split("/((\r?\n)|(\r\n?))/", $jbeam_content);
    $len = count($liness);
    $i = 0;
    $second_match = false;
    
    $tire_data = [];
    $lines = [];
    foreach ($liness as $line) {
        $lines[] = trim($line);
    }

    while (true) {
        $matches = [];
        
        preg_match("/({\"frictionCoef\":)(\d\.?\d*)/", $lines[$i], $matches);
        if (count($matches) > 2) {
            if ($second_match == false) {
                $matches = [];
                $second_match = true;
            } else {
                $tire_data['friccoef'] = $matches[2]; $i += 1; continue;
            }
        }

        preg_match("/({\"slidingFrictionCoef\":)(\d\.?\d*)/", $lines[$i], $matches);
        if (count($matches) > 2) {
            $tire_data['slidingcoef'] = $matches[2];
            $i += 1;
            continue;
        }

        preg_match("/({\"treadCoef\":)(\d\.?\d*)/", $lines[$i], $matches);
        if (count($matches) > 2) {
            $tire_data['treadcoef'] = $matches[2];
            $i += 1;
            continue;
        }
        
        $i += 1;
        if ($i >= $len) {
            break;
        }
    }
    
    //pprint($jbeam_content);
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