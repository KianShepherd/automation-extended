<?php
function modifyTireData($jbeam_filename, $jbeam_target, $friction_value, $sliding_friction_value, $offroad_value) {
    $tire_data = jbeam_to_json($jbeam_folder, $jbeam_filename);
    unlink($jbeam_filename);

    for ($i = 0; $i < count($tire_data[$jbeam_target]['pressureWheels']); $i++) {
        if (array_key_exists('treadCoef', $tire_data[$jbeam_target]['pressureWheels'][$i])) {     
            $tire_data[$jbeam_target]['pressureWheels'][$i]['treadCoef'] = $offroad_value;
        }
        if (array_key_exists('slidingFrictionCoef', $tire_data[$jbeam_target]['pressureWheels'][$i])) {     
            $tire_data[$jbeam_target]['pressureWheels'][$i]['slidingFrictionCoef'] = $sliding_friction_value;
        }
        if (array_key_exists('frictionCoef', $tire_data[$jbeam_target]['pressureWheels'][$i])) {     
            $tire_data[$jbeam_target]['pressureWheels'][$i]['frictionCoef'] = $friction_value;
        }
    }

    $myfile = fopen($jbeam_filename, "w");
    fwrite($myfile, json_encode($tire_data, JSON_PRETTY_PRINT));
    fclose($myfile);
} 

function modifyEngineData($jbeam_filename,  &$posted, $filename_no_ext, $hash) {
    $engine_data = jbeam_to_json($jbeam_folder, $jbeam_filename);
    unlink($jbeam_filename);

    if ($posted['msuper'] == 'on') {
        array_push($engine_data['Camso_Engine']['slots'], ["Customizable_Supercharger","Customizable_Supercharger","Supercharger Tuner"]);
        copy("addons/camso_super.jbeam", "tmp_uploads/$hash/vehicle_data/vehicles/$filename_no_ext/camso_super.jbeam");
    }
    if ($posted['mnos'] == 'on') {
        array_push($engine_data['Camso_Engine']['slots'], ["n2o_system","", "Nitrous Oxide System"]);
        copy("addons/_n2o.jbeam", "tmp_uploads/$hash/vehicle_data/vehicles/$filename_no_ext/_n2o.jbeam");
    }
    if ($posted['mcooling'] == 'on') {
        $engine_data['Camso_Engine']['mainEngine']['radiatorEffectiveness'] = 999999;
        $engine_data['Camso_Engine']['mainEngine']['oilRadiatorEffectiveness'] = 999999;
    }
    if (isset($posted['mfuel'])) {
        $engine_data['Camso_Engine']['mainEngine']['burnEfficiency'] = [[0.00, 1.00], [0.5, 1.0], [1.0, 1.0]];
    }
    $engine_data['Camso_Transmission']['gearbox']['gearRatios'] = array_map('floatval', preg_split("/([\s]+)?,([\s]+)?/", $posted['gears']));
    $engine_data['Camso_Engine']['mainEngine']['maxRPM'] = intval($posted['mrpm']) + 50;
    $engine_data['Camso_Engine']['mainEngine']['inertia'] = floatval($posted['inertia']);
    $mod_power = modifyEngineTorque($engine_data, $posted);
    $engine_data['Camso_Engine']['mainEngine']['torque'] = $mod_power['new_torque'];
    if (array_key_exists("Camso_Turbo", $engine_data)) {
        $engine_data['Camso_Turbo']['turbocharger']['engineDef'] = $mod_power['new_psi'];
    }
    
    $myfile = fopen($jbeam_filename, "w");
    fwrite($myfile, json_encode($engine_data, JSON_PRETTY_PRINT));
    fclose($myfile);
}

function modifyEngineTorque($engine_torque, $posted) {
    $new_torque = $engine_torque['Camso_Engine']['mainEngine']['torque'];
    $new_PSI = $engine_torque['Camso_Turbo']['turbocharger']['engineDef'];
    
    $cur_max_rpm = end($new_torque)[0];

    if (intval($posted['mrpm']) != end($new_torque)[0]) {
        for ($i = (intval($posted['mrpm']) > end($new_torque)[0]) ? 3 : 2; $i < count($new_torque); $i++) {
            $new_torque[$i][0] = round(($new_torque[$i][0] / $cur_max_rpm) * intval($posted['mrpm']));
        }
        if (array_key_exists('Camso_Turbo', $engine_torque)) {
            for ($i = (intval($posted['mrpm']) > end($new_PSI)[0]) ? 1 : 0; $i < count($new_PSI); $i++) {
                $new_PSI[$i][0] = round(($new_PSI[$i][0] / $cur_max_rpm) * intval($posted['mrpm']));
            }
        }
    }

    if ($posted['mtorque'] != '1.0' || $posted['atorque'] != '0' || $posted['vvlrpm'] != '0') {
        for ($i = 2; $i < count($new_torque); $i++) {
            $new_torque[$i][1] = round((floatval($new_torque[$i][1]) + floatval($posted['atorque'])) * floatval($posted['mtorque']), 2);
            if (intval($new_torque[$i][0]) >= intval($posted['vvlrpm'])) {
                $new_torque[$i][1] = round((floatval($new_torque[$i][1]) + floatval($posted['vvlatorque'])) * floatval($posted['vvlmtorque']), 2);
            }
        }
    }

    return array("new_torque" => $new_torque, "new_psi" => $new_PSI);
}

function modifyBrakeData($jbeam_filename, $jbeam_target, $brake_torque, $parking_torque, $vent_coef) {
    $brake_data = jbeam_to_json($jbeam_folder, $jbeam_filename);
    unlink($jbeam_filename);

    for ($i = 0; $i < count($brake_data[$jbeam_target]['pressureWheels']); $i++) {
        if (array_key_exists('brakeTorque', $brake_data[$jbeam_target]['pressureWheels'][$i])) {     
            $brake_data[$jbeam_target]['pressureWheels'][$i]['brakeTorque'] = floatval($brake_torque);
        }
        if (array_key_exists('parkingTorque', $brake_data[$jbeam_target]['pressureWheels'][$i])) {     
            $brake_data[$jbeam_target]['pressureWheels'][$i]['parkingTorque'] = floatval($parking_torque);
        }
        if (array_key_exists('brakeVentingCoef', $brake_data[$jbeam_target]['pressureWheels'][$i])) {     
            $brake_data[$jbeam_target]['pressureWheels'][$i]['brakeVentingCoef'] = floatval($vent_coef);
        }
    }
    
    $myfile = fopen($jbeam_filename, "w");
    fwrite($myfile, json_encode($brake_data, JSON_PRETTY_PRINT));
    fclose($myfile);
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
$file_name_no_ext = substr(basename($_POST['filename']), 0, strlen(basename($_POST['filename'])) - 4);

modifyEngineData("tmp_uploads/$_POST[hash]/vehicle_data/vehicles/$file_name_no_ext/camso_engine.jbeam", $_POST, $file_name_no_ext, $_POST['hash']);
modifyTireData("tmp_uploads/$_POST[hash]/vehicle_data/vehicles/$file_name_no_ext/wheels_front.jbeam", "wheels_front", $_POST['fmfric'], $_POST['fmsfric'], $_POST['fmofric']);
modifyTireData("tmp_uploads/$_POST[hash]/vehicle_data/vehicles/$file_name_no_ext/wheels_rear.jbeam", "wheels_rear", $_POST['rmfric'], $_POST['rmsfric'], $_POST['rmofric']);
modifyBrakeData("tmp_uploads/$_POST[hash]/vehicle_data/vehicles/$file_name_no_ext/suspension_F.jbeam", "Camso_brake_F", $_POST['fbraketorque'], $_POST['fbrakeparking'], $_POST['fbrakevent']);
modifyBrakeData("tmp_uploads/$_POST[hash]/vehicle_data/vehicles/$file_name_no_ext/suspension_R.jbeam", "Camso_brake_R", $_POST['rbraketorque'], $_POST['rbrakeparking'], $_POST['rbrakevent']);
