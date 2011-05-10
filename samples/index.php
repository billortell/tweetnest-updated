<?php session_start(); header('P3P: CP="CAO PSA OUR"'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Landing Page</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" value="landing page, twitter landing pages, tweet summaries" />
	<link rel="stylesheet" href="css/main.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print" />


    <script language="JavaScript" type="text/javascript">

        var ifrm_user = "sidhale";

        var ifrm_url = "http://tweetaculo.us/";

        /** show a part. user **/
        ifrm_url += (ifrm_user == "" ) ? "" : 'user/' + ifrm_user ;
        ifrm_url += "?loc="+window.location;

        var ifrm_name = "childframe";
        var ifrm_id = "childframe";
        var ffull = true;
        function addLoadEvent(func) {
            var oldonload = window.onload;
            if (typeof window.onload != 'function') {
                window.onload = func;
            } else {
                window.onload = function() {
                    if (oldonload) {
                        oldonload();
                    }
                    func();
                }
            }
        }

        // Firefox worked fine. Internet Explorer shows scrollbar because of frameborder
        // taken from ==> http://www.mattcutts.com/files/frame-example.html
        function resizeFrame(f) {
            if ( ffull === true ) {
                f = document.getElementById(ifrm_id);
                f.style.height = f.contentWindow.document.body.scrollHeight + "px";
            }
            return true;
        }


        addLoadEvent(function() {
        //    resizeFrame(document.getElementById('childframe'));
        })


        function makeFrame() {
            var ifrm_container = ifrm_id + "_container";
            document.write("<div id='" + ifrm_container + "'></div>");
            var fc = document.getElementById(ifrm_container);
            var ifrm = document.createElement("iframe");
            ifrm.setAttribute("src", ifrm_url);
            ifrm.setAttribute("frameborder", 0);
            ifrm.setAttribute("name", ifrm_name);
            ifrm.setAttribute("id", ifrm_id );
        //    document.body.appendChild(ifrm);
            fc.appendChild(ifrm);
        }

    </script>

    <style type="text/css">
        iframe#childframe {
            width: 600px;
            min-height: 500px;
            overflow-x:hidden;
        }
        body {
            width: 960px;
            margin: 0px auto;
        }
        div.logo {
            overflow: auto;
            width: 203px;
            height: 143px;
            background: transparent url(/img/004_cloud.png) no-repeat bottom left;
            z-index: 100;
        }
        #fixedPanel {
            position:fixed;
            top:1em;
            left:2em;
            width:340px;
            margin:0;
            padding:0;
            background:transparent;
            float: left;
        }
    </style>

</head>
<body id="front" onload="resizeFrame(document.getElementById('childframe'))">

<div id="container" style='margin: 50px auto;'>

    <section id="fixedPanel">
        <div class="logo"></div>
        <h2>Hello! You're finally here...<br><em>Welcome</em></h2>
        <h3>get yours! &raquo;</h3>
        <div style='clear:both;'></div>
    </section>

    <section style='width: 600px; float: right;margin: 0px;padding:0px 0px 0px 15px; border-left: 3px #eee dotted; '>
        <script type="text/javascript">makeFrame();</script>
        <div style='clear:both;'></div>
    </section>


    <div style="clear:both;"></div>
</div>


</body>
</html>