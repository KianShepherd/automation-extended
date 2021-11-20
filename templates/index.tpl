<html>
<head>
    <link rel="stylesheet" href="skin/skin.css">
    <title>Automation Extended</title>
    <script src="include/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div id="upload_form" style="display: block;">
        <form enctype="multipart/form-data" name="vehicle_data" id="vehicle_data" method="post">
            <input type="hidden" name="timestamp" id="timestamp" value="-1" />
            <input type="file" id="file" name="file">
            <div class="submit-container">
                <input class="gear-ratio" type="submit" onclick="upload_file(); return false;">
            </div>
        </form>
    </div>
    <div id="ajax_replace" style="width: 100%;">
    </div>

    <div class="navbar" style="display: none;">
        <a class="active" onclick="submit_form(); return false;"><h1 style="margin: 0px;">Download Updated File</h1></a>
    </div>
</body>
</html>

<script>
{literal}
    function submit_form() {
        document.getElementById("submit-button").click();
    }

    function show_download() {
        $(".navbar").css({"display": "block"});
        console.log("show")
    }

    function upload_file() {
        $("#timestamp").val(Date.now());
        var form = new FormData($("#vehicle_data")[0]);         

        $.ajax({
            url: '/file_uploads.php',
            data: form,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function(data){
                show_download();
                $("#upload_form").css('display', 'none');
                $("#ajax_replace").html(data);
            }
        });
    }
{/literal}
</script>