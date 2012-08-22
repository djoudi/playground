
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>jQuery UI</title>
        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <!-- Styles --> 
        <link type="text/css" href="css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
        <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
        /* Override some defaults */
        html, body {
          background-color: #eee;
        }
        body {
          padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
        }
        .container {
            width: 820px;
        }
        </style>
    </head>
    <body>
        <!--body-->
        <div class="container">
            <h3>2 Datepickers with custom UI</h3>
            <p>
                jQuery <a href="http://jqueryui.com/">UI</a><br />
                jQuery <a href="http://addyosmani.github.com/jquery-ui-bootstrap/">Bootstrap UI</a>
            </p>
            <h6>Полезненькое, опции для настройки</h6>
            <!--@todo дополнить-->
            <ul>
                <li>
                    <strong>maxDate</strong>
                    <p>
                        $( ".selector" ).datepicker({ maxDate: "+1m +1w" });
                    </p>
                </li>
                <li>
                    <strong>minDate</strong>
                    <p>
                        $( ".selector" ).datepicker({ minDate: new Date(2007, 1 - 1, 1) });
                    </p>
                </li>
            </ul>
            <div id="datepickers">
                <input type="text" id="dp1" name="dp1" />
                <input type="text" id="dp2" name="dp2" />
            </div>
        </div>
        
        <!--scripts-->

        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript">
        $(function(){
            $('#dp1').datepicker({inline: true});
            $('#dp2').datepicker({inline: true});
        });    
        </script>
    </body>
</html>
