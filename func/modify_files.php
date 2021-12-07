<?php
function modifyTireData($jbeam_content, $friction_value, $sliding_friction_value, $offroad_value) {
    $liness = preg_split("/((\r?\n)|(\r\n?))/", $jbeam_content);
    $len = count($liness);
    $i = 1;
    $second_match = false;
    
    $lines = [];
    foreach ($liness as $line) {
        $lines[] = trim($line);
    }

    $tire_data = $lines[0];
    while (true) {
        $matches = [];
        $tire_data .= "\n";

        preg_match("/({\"frictionCoef\":)(\d\.?\d*)/", $lines[$i], $matches);
        if (count($matches) > 2) {
            if ($second_match == false) {
                $matches = [];
                $second_match = true;
            } else {
                $tire_data .= "{\"frictionCoef\":" . $friction_value . "},";
                $i += 1;
                continue;
            }
        }

        preg_match("/({\"slidingFrictionCoef\":)(\d\.?\d*)/", $lines[$i], $matches);
        if (count($matches) > 2) {
            $tire_data .= "{\"slidingFrictionCoef\":" . $sliding_friction_value . "},";
            $i += 1;
            continue;
        }

        preg_match("/({\"treadCoef\":)(\d\.?\d*)/", $lines[$i], $matches);
        if (count($matches) > 2) {
            $tire_data .= "{\"treadCoef\":" . $offroad_value . "},";
            $i += 1;
            continue;
        }

        $tire_data .= $lines[$i];
        $i += 1;
        if ($i >= $len) {
            break;
        }
    }

    return $tire_data;
}

function modifyEngineData($jbeam_content, &$posted, $filename_no_ext, $hash, $engine_data) {
    $liness = preg_split("/((\r?\n)|(\r\n?))/", $jbeam_content);
    $len = count($liness);
    $i = 1;
    $matched_cooling_rad = ($posted['mcooling'] == 'on') ? false : true;
    $matched_cooling_oil = ($posted['mcooling'] == 'on') ? false : true;
    $matched_added_power = ($posted['msuper'] == 'on' || $posted['mnos'] == 'on') ? false : true;
    
    
    $lines = [];
    foreach ($liness as $line) {
        $lines[] = trim($line);
    }

    $engine_data = $lines[0];
    while (true) {
        $matches = [];
        $engine_data .= "\n";
        //pprint($lines[$i]);
        if (isset($posted['gears'])) {
            preg_match("/(\"gearRatios\":)(\[[\-\d,\. ]+\])/", $lines[$i], $matches);
            if (count($matches) > 0) {
                $engine_data .= "\"gearRatios\":[$posted[gears]],";
                $i += 1;
                unset($posted['gears']);
                continue;
            }
        }

        if (isset($posted['mrpm'])) {
            preg_match("/maxRPM/", $lines[$i], $matches);
        
            if (count($matches) > 0) {
                $i += 1;
                $engine_data .= "\"maxRPM\":" . (((int)$posted['mrpm']) + 50) . ",";
                if ($engine_data['has_turbo'] == false) {
                    unset($posted['mrpm']);
                }
                continue;
            }
        }
        
        if (isset($posted['atorque']) || isset($posted['mtorque']) || isset($posted['mrpm'])) {
            preg_match("/\"rpm\", \"torque\"/", $lines[$i], $matches);
        
            if (count($matches) > 0) {
                $engine_data .= $lines[$i];
                // match torque curve
                $i += 1; 
                $torque_values = [];
                while (true) {
                    $matches = [];
                    if ($lines[$i] == "],") { $i += 1; break; }
                    $torque_value = substr($lines[$i], 1, strlen($lines[$i]) - 3);
                    $torque_value = preg_replace("/ /", "", $torque_value);
                    $torque_value = explode(",", $torque_value);
                    $torque_value = [(int)$torque_value[0], (float)$torque_value[1]];
                    $torque_values[] = $torque_value;
                    $i += 1;
                }
                modifyEngineTorque($engine_data, $torque_values, $posted);
                continue;
            }
        }

        if ($engine_data['has_turbo'] && isset($posted['mrpm'])) {  
            preg_match("/engineDef/", $lines[$i], $matches);

            if (count($matches) > 0) {
                $engine_data .= $lines[$i] . "\n";
                $i += 1;
                while (true) {
                    $matches = [];

                    preg_match("/1\.[0]+,\s1\.[0]+/", $lines[$i], $matches);
                    if (count($matches) > 0) {
                        $engine_data .= $lines[$i] . "\n";
                        $i += 1;
                        $engine_data .= "[$posted[mrpm], 1.000000, 1.000000],\n";
                        while (true) {
                            $matches = [];
                            preg_match("/^\],/", $lines[$i], $matches);
                            if (count($matches) > 0) {
                                $engine_data .= $lines[$i] . "\n";
                                $i += 1;
                                unset($posted['mrpm']);
                                break;
                            }
                            $i += 1;
                        }
                        break;
                    }

                    preg_match("/^\],/", $lines[$i], $matches);
                    if (count($matches) > 0) {
                        $engine_data .= $lines[$i] . "\n";
                        $i += 1;
                        unset($posted['mrpm']);
                        break;
                    }
                    $engine_data .= $lines[$i] . "\n";
                    $i += 1;
                }
            }
        }

        if ($matched_added_power == false) {
            preg_match("/\[\"type\", \"default\", \"description\"\]/", $lines[$i], $matches);

            if (count($matches) > 0) {
                $engine_data .= $lines[$i] . "\n";
                if ($posted['msuper'] == 'on') {
                    $engine_data .= '["Customizable_Supercharger","Customizable_Supercharger","Supercharger Tuner"]';
                    copy("addons/camso_super.jbeam", "tmp_uploads/$hash/vehicle_data/vehicles/$filename_no_ext/camso_super.jbeam");
                }
                if ($posted['mnos'] == 'on') {
                    if ($posted['msuper'] == 'on') { $engine_data .= "\n"; }
                    $engine_data .= '["n2o_system","", "Nitrous Oxide System"]';
                    copy("addons/_n2o.jbeam", "tmp_uploads/$hash/vehicle_data/vehicles/$filename_no_ext/_n2o.jbeam");
                }
                $matched_added_power = true;
                $i += 1;
                continue;
            }   
        }

        if ($matched_cooling_rad == false) {
            preg_match("/radiatorEffectiveness/", $lines[$i], $matches);
            if (count($matches) > 0) {
                $i += 1;
                $engine_data .= "\"radiatorEffectiveness\":800000,";
                $matched_cooling_rad = true;
                continue;
            }
        }

        if ($matched_cooling_oil == false) {
            preg_match("/oilRadiatorEffectiveness/", $lines[$i], $matches);
            if (count($matches) > 0) {
                $i += 1;
                $engine_data .= "\"oilRadiatorEffectiveness\":50000,";
                $matched_cooling_oil = true;
                continue;
            }
        }
        
        if (isset($posted['mfuel'])) {
            preg_match("/burnEfficiency/", $lines[$i], $matches);

            if (count($matches) > 0) {
                $engine_data .= $lines[$i];
                $i += 1;
                while (true) {
                    if ($lines[$i] == "],") { $i += 1; break; }
                    $i += 1;
                }
                $engine_data .= "\n[0.00, 1.00],\n";
				$engine_data .= "[0.50, 1.00],\n";
				$engine_data .= "[1.00, 1.00],\n";
                $engine_data .= "],";
                unset($posted['mfuel']);
                continue;
            }
        }

        $engine_data .= $lines[$i];
        $i += 1;
        if ($i >= $len) {
            break;
        }
    }
    //pprint($engine_data);
    return $engine_data;
}

function modifyEngineTorque(&$engine_data, $torque_values, &$posted) {
    $torque_to_add = (isset($posted['atorque'])) ? $posted['atorque'] : 0;
    $torque_to_mul = (isset($posted['mtorque'])) ? $posted['mtorque'] : 1;
    $cur_max_rpm = $torque_values[count($torque_values) - 1][0];
    
    $i = 2;
    $engine_data .= ",\n[" . $torque_values[0][0] . ", " . $torque_values[0][1] . "],\n";
    $torque_values[1][1] = ($torque_values[1][1] + $torque_to_add) * $torque_to_mul;
    $engine_data .= "[" . $torque_values[1][0] . ", " . $torque_values[1][1] . "],\n";
    while (true) {
        $torque_values[$i][1] = ($torque_values[$i][1] + $torque_to_add) * $torque_to_mul;
        if (isset($posted['mrpm'])) {
            $torque_values[$i][0] = ($torque_values[$i][0] / $cur_max_rpm) * $posted['mrpm'];
        }
        $engine_data .= "[" . (int)$torque_values[$i][0] . ", " . (int)$torque_values[$i][1] . "],\n";
        $i += 1;
        if ($i >= count($torque_values)) {
            break;
        }
    }
    $engine_data .= "],";
    unset($posted['atorque']);
    unset($posted['mtorque']);
}

/*
array (
  'hash' => 'a63b40a0392192058180cd48365ffc4b',
  'filename' => 'kruptoss_car_test.zip',
  'gears' => '-4.46, 0, 4.25, 2.69, 1.92, 1.45, 1.14, 0.92, 0.75',
  'mrpm' => '5800',
  'atorque' => '0',
  'mtorque' => '1.0',
  'msuper' => 'on',
  'mnos' => 'on',
  'mcooling' => 'on',
  'mfuel' => 'on',
  'mshiftspeed' => 'on',
)
*/
if ($_POST['mrpm'] == $engine_data['max_rpm']) {
    unset($_POST['mrpm']);
}
if ($_POST['atorque'] == '0') {
    unset($_POST['atorque']);
}
if ($_POST['mtorque'] == '1.0') {
    unset($_POST['mtorque']);
}
if ("[" . $_POST['gears'] . "]" == $engine_data['gear_ratios']) {
    unset($_POST['gears']);
}

$filename = $_POST['filename'];
$target_dir = "tmp_uploads/$_POST[hash]/";
$file_name_no_ext = substr(basename($filename), 0, strlen(basename($filename)) - 4);
$jbeam_folder = $target_dir . "vehicle_data/vehicles/$file_name_no_ext/";
$engine_content = GetStrippedFileContent($jbeam_folder, "camso_engine.jbeam");
$engine_data = getEngineData($engine_content);
$front_tire_content = GetStrippedFileContent($jbeam_folder, "wheels_front.jbeam");
$rear_tire_content = GetStrippedFileContent($jbeam_folder, "wheels_rear.jbeam");

unlink($jbeam_folder . "camso_engine.jbeam");
unlink($jbeam_folder . "wheels_front.jbeam");
unlink($jbeam_folder . "wheels_rear.jbeam");

$new_engine_jbeam = modifyEngineData($engine_content, $_POST, $file_name_no_ext, $_POST['hash'], $engine_data);
$new_tire_jbeam_front = modifyTireData($front_tire_content, $_POST['fmfric'], $_POST['fmsfric'], $_POST['fmofric']);
$new_tire_jbeam_rear = modifyTireData($rear_tire_content, $_POST['rmfric'], $_POST['rmsfric'], $_POST['rmofric']);

$myfile = fopen($jbeam_folder . "camso_engine.jbeam", "w");
fwrite($myfile, $new_engine_jbeam);
fclose($myfile);

$myfile = fopen($jbeam_folder . "wheels_front.jbeam", "w");
fwrite($myfile, $new_tire_jbeam_front);
fclose($myfile);

$myfile = fopen($jbeam_folder . "wheels_rear.jbeam", "w");
fwrite($myfile, $new_tire_jbeam_rear);
fclose($myfile);
