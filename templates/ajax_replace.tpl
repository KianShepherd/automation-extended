<form name="new_data" id="new_data" action="/file_downloads.php" method="post">
    <input type="hidden" name="hash" id="hash" value="{$hash}">
    <input type="hidden" name="filename" id="filename" value="{$filename}">
    <table style="left: 0px; position: absolute; top: 0; width: 100%; background-color: #919191;">
        <tr>
            <td id="t_engine" onclick="changeTo('engine-editor', 't_engine'); return false;"
                style="width: 50%; padding: 15px; background-color: #4fedff; text-align: center;">
                <h1 class="header-data">Engine</h1>
            </td>
            <td  id="t_tires" onclick="changeTo('tire-editor', 't_tires'); return false;"
                style="width: 50%; padding: 15px; text-align: center; background-color: #919191;">
                <h1 class="header-data">Tires</h1>
            </td>
        </tr>
    </table>
    <div style="padding-bottom:100px;">
    </div>
    <div id="engine-editor" style="display: block;">
        <table style="margin: auto;">
            <tr>
                <td style="height: 75px; padding: 20px; text-align: center"><h2 style="margin: auto; font-family: 'Courier New', monospace;">Gear Ratios</h2></td>
                <td>
                    <table>
                        <tr>
                            <div id="gear_ratio_replace">
                                <td>
                                    <textarea rows="1" cols="100" name="gears" id="gears"
                                        style="resize: none;">{$engine.gear_ratio_str}</textarea>
                                </td>
                            </div>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="height: 75px; text-align: center"><h2 style="margin: auto; font-family: 'Courier New', monospace;">RPM</h2></td>
                <td style="padding: 20px;">
                    <table>
                        <tr>
                            <td style="text-align: center; font-family: 'Courier New', monospace; font-size:18px;  font-family: 'Courier New', monospace;"><b>RPM Limit</b></td>
                            <td style="padding: 20px;">
                                <textarea rows="1" cols="3" name="mrpm" id="mrpm"
                                    style="resize: none;">{$engine.max_rpm}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-family: 'Courier New', monospace; font-size:18px;  font-family: 'Courier New', monospace;"><b>Flat torque increase<br>(current value at rpm limit: {$engine.torque_at_max})</b></td>
                            <td style="padding: 20px;">
                                <textarea rows="1" cols="3" name="atorque" id="atorque"
                                    style="resize: none;">0</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-family: 'Courier New', monospace; font-size:18px;  font-family: 'Courier New', monospace;"><b>multiplicative torque increase<br>(applied after flat increase)</b></td>
                            <td style="padding: 20px;">
                                <textarea rows="1" cols="3" name="mtorque" id="mtorque"
                                    style="resize: none;">1.0</textarea>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="height: 75px; text-align: center"><h2 style="margin: auto; font-family: 'Courier New', monospace;">More Power</h2></td>
                <td style="padding: 20px;">
                    <table>
                        <tr>
                            <td><input name="msuper" type="checkbox"></td>
                            <td style="font-size:18px;  font-family: 'Courier New', monospace;"><b>Supercharger</b></td>
                            <td  style="padding: 20px;  font-family: 'Courier New', monospace;" rowspan="2">
                                Note these will have to be put on the car in beam.NG within<br>
                                the vehicle config/customization section under the engine category.<br>
                                They can then be fine tuned within the tuning section of the same menu.
                            </td>
                        </tr>
                        <tr>
                            <td><input name="mnos" type="checkbox"></td>
                            <td style="font-size:18px;  font-family: 'Courier New', monospace;"><b>NOS</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="height: 75px; text-align: center"><h2 style="margin: auto; font-family: 'Courier New', monospace;">Extras</h2></td>
                <td style="padding: 20px;">
                    <table>
                        <tr>
                            <td><input name="mcooling" type="checkbox"></td>
                            <td style="font-size:18px;  font-family: 'Courier New', monospace;"><b>Extra Cooling</b></td>
                        </tr>
                        <tr>
                            <td><input name="mfuel" type="checkbox"></td>
                            <td style="font-size:18px;  font-family: 'Courier New', monospace;"><b>Max Fuel Efficency</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div id="tire-editor" style="display: none;">
        <table style="margin: auto;">
            <tr>
                <td style="height: 90px; padding: 20px;">
                </td>
                <td style="padding: 20px;">
                    <h2 style="margin: auto; font-family: 'Courier New', monospace;">Front</h2>
                </td>
                <td>
                    <h2 style="margin: auto; font-family: 'Courier New', monospace;">Rear</h2>
                </td>
            </tr>
            <tr>
                <td style="padding: 20px; text-align: center"><h3 style="margin: auto; font-family: 'Courier New', monospace;">Friction coefficient</h3></td>
                <td style="padding: 20px;">
                    <textarea rows="1" cols="8" name="fmfric" id="fmfric" style="resize: none;">{$front_tires.friccoef}</textarea>
                </td>
                <td>
                <textarea rows="1" cols="8" name="rmfric" id="rmfric" style="resize: none;">{$rear_tires.friccoef}</textarea>
            </td>
            </tr>
            <tr>
                <td style="padding: 20px; text-align: center"><h3 style="margin: auto; font-family: 'Courier New', monospace;">Sliding friction coefficient</h3></td>
                <td style="padding: 20px;">
                    <textarea rows="1" cols="8" name="fmsfric" id="fmsfric" style="resize: none;">{$front_tires.slidingcoef}</textarea>
                </td>
                <td>
                    <textarea rows="1" cols="8" name="rmsfric" id="rmsfric" style="resize: none;">{$rear_tires.slidingcoef}</textarea>
                </td>
            </tr>
            <tr>
                <td style="padding: 20px; text-align: center"><h3 style="margin: auto; font-family: 'Courier New', monospace;">Grip loss offroad</h3></td>
                <td style="padding: 20px;">
                    <textarea rows="1" cols="8" name="fmofric" id="fmofric" style="resize: none;">{$front_tires.treadcoef}</textarea>
                </td>
                <td>
                    <textarea rows="1" cols="8" name="rmofric" id="rmofric" style="resize: none;">{$rear_tires.treadcoef}</textarea>
                </td>
            </tr>
        </table>
    </div>
    <button id="submit-button" type="submit" hidden>Download Updated File</button>
</form>

<script>
    {literal}
        function resetAll() {
            $('#engine-editor').css("display", "none");
            $('#tire-editor').css("display", "none");
            $('#t_engine').css({"padding" : "15px", "background-color": "#919191"});
            $('#t_tires').css({"padding" : "15px", "background-color": "#919191"});
        }

        function changeTo(div_id, selector_id) {
            resetAll();
            $('#' + div_id).css("display", "block");
            $('#' + selector_id).css({"padding" : "15px", "background-color": "#4fedff"});
        }
    {/literal}
</script>