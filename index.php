<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <p>Hello world! This is HTML5 Boilerplate.</p>
        
        <form enctype="multipart/form-data" method="post" action="accept-file.php">
            <div class="row">
              <label for="fileToUpload">Select a File to Upload</label><br />
              <input type="file" name="fileToUpload" id="fileToUpload" />
            </div>
            <div class="row">
              <input type="submit" value="Upload" />
            </div>
        </form>

        <div class="pokedex">
            <div class="screen"></div>
            <div class="btn-a"></div>
            <div class="btn-b"></div>
        </div>

        <script src="js/vendor/jquery-1.9.0.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>

        <script type="text/javascript">
            function getUrlVars() {
                var vars = {};
                var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                    vars[key] = value;
                });
                return vars;
            }
            var filename = getUrlVars()["file"];
            if (filename !== undefined)
            {
                var qr_url = "proxy.php?url=http://localhost:8080/qr/decode?u=http://localhost/artedex/uploads/" + filename;
                // var userdata_url = "http://localhost/artedex/api/users/1";

                $.get(qr_url, function(qrdata) {
                    console.log(qrdata);
                    var userdata_url = "http://localhost/artedex/api/users/" + qrdata;
                    $.get(userdata_url, function(userdata) {
                        console.log(userdata);
                    });
                    // console.log(userdata_url);
                });


            }

        </script>
    </body>
</html>
