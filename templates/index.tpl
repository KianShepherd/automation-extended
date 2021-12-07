<html>
<head>
    <link rel="stylesheet" href="skin/skin.css">
    <title>Automation Extended</title>
    <script src="include/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div id="loader" class="loader" style="margin: auto; display: none;"></div>
    <div id="upload_form" style="display: block;">
        <form enctype="multipart/form-data" name="vehicle_data" id="vehicle_data" method="post">
            <input type="hidden" name="timestamp" id="timestamp" value="-1" />
            <input type="file" id="file" name="file" style="display: none;">
            <div class="navbar" style="display: block;">
                <a id="file_upload_button" class="active" onclick="upload_file(); return false;" style="display: none;"><h1 style="margin: 0px;">Upload File</h1></a>
                <a id="file_choose_button" class="active" onclick="choose_file(); return false;"><h1 style="margin: 0px;">Choose File To Upload</h1></a>
            </div>
        </form>
    </div>
    <div id="ajax_replace" style="width: 100%;">
    </div>

    <div id="download_button_nav" class="navbar" style="display: none;">
        <a class="active" onclick="submit_form(); return false;"><h1 style="margin: 0px;">Download Updated File</h1></a>
    </div>
</body>
</html>

<script>
{literal}
    function choose_file() {
        $("#file_upload_button").css('display', 'block');
        $("#file_choose_button").css('display', 'none');
        $("#file").click();
    }

    function submit_form() {
        $("#submit-button").click();
    }

    function show_download() {
        $("#download_button_nav").css({"display": "block"});
        console.log("show")
    }

    function upload_file() {
        $("#timestamp").val(Date.now());
        var form = new FormData($("#vehicle_data")[0]);         
        $("#upload_form").css('display', 'none');
        $("#loader").css('display', 'block');

        $.ajax({
            url: '/file_uploads.php',
            data: form,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function(data){
                show_download();
                $("#ajax_replace").html(data);
                $("#loader").css('display', 'none');
            }
        });
    }
{/literal}
</script>