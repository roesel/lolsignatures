<!DOCTYPE HTML >
<html>
    <head>
        <meta charset="UTF-8" />
        <title>League of Legends signature creator</title>
        <meta http-equiv='Content-language' content='en'>
        <link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
        <link rel='stylesheet' type='text/css' href='style.css'>
        <link rel='stylesheet' type='text/css' href='form.css'>        
    </head>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <!--<script type="text/javascript" src="jquery.js"></script>-->
    <script type="text/javascript" charset="utf-8">
        $(function () {
            $("select#champion").change(function () {
                $.getJSON("./get_skins_json.php", {id: $("#champion option:selected").text(), ajax: 'true'}, function (j) {
                    var options = '';
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                    $("select#skin").html(options);
                })
            })
        });


        $(document).on('click', 'pre', function () {

            if (this.select) {
                this.select();
            }
            else if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(this);
                range.select();
            } else if (window.getSelection) {
                var range = document.createRange();
                range.selectNode(this);
                window.getSelection().addRange(range);
            }
        });
    </script>
    <body>
        <?php
        include_once('secrets/const.secret.php');
        ?>
        <div id="background-top">
            <div id="wrapper-top">
                <div id="content-top">
                    <div id='form'> 
                        <h1 id="page-title"><a href="<?php echo WEB ?>">League of Legends signature creator</a></h1>
                        <form action="" id="data" method="POST">
                            <input class="input-xlarge" type="text" placeholder="Summoner Name" name="lolname" autofocus >
                            <!-- Not too sure of the other region codes -->
                            <select name="region">
                                <option value="" disabled selected class="select-placeholder">Region</option>
                                <option value="na" >NA</option>
                                <option value="euw">EUW</option>
                                <option value="eune">EUNE</option>
                                <option value="oce">OCE</option>
                                <option value="las">LAS</option>
                                <option value="lan">LAN</option>
                                <option value="br">BR</option>
                                <option value="tr">TUR</option>
                                <option value="ru">RU</option>
                            </select> 
                            <select name="champion" id="champion">
                                <option value="0">Transparent</option>
                                <?php
                                include("champion_array.php");
                                foreach ($champion_array as $champion => $id) {
                                    print '<option value="' . $id[0] . '">' . $champion . '</option>
                ';
                                }
                                ?>
                            </select>
                            <select name="skin" id="skin">
                                <option value="0">Default Skin</option>
                                <option value="1">Skin 1</option>
                                <option value="2">Skin 2</option>
                                <option value="3">Skin 3</option>
                                <option value="4">Skin 4</option>
                                <option value="5">Skin 5</option>
                                <option value="6">Skin 6</option>
                                <option value="7">Skin 7</option>
                                <option value="8">Skin 8</option>
                                <option value="9">Skin 9</option>
                                <option value="10">Skin 10</option>
                            </select>
<!--                                <select name="logo">
                                <option value="logo">zG Logo</option>
                                <option value="text">zG Text</option>
                                <option value="nozg">No zG Watermark</option>
                            </select>-->
                            <input class="blue" type="submit" name="sub">
                        </form>                                                
                    </div>
                </div>
            </div>
        </div>
        <div id="background-center">
            <div id="wrapper-center">
                <div id="content-center">

                    <?php
// integer starts at 0 before counting
                    $i = 0;
                    $dir = 'sigs_cache/';
                    if ($handle = opendir($dir)) {
                        while (($file = readdir($handle)) !== false) {
                            if (!in_array($file, array('.', '..')) && !is_dir($dir . $file))
                                $i++;
                        }
                    }

                    if (isset($_POST['sub']) &&
                            isset($_POST['lolname']) &&
                            isset($_POST['region']) &&
                            isset($_POST['champion']) &&
                            isset($_POST['skin'])) {
                        $name = $_POST['lolname'];
                        $region = $_POST['region'];
                        $champion = $_POST['champion'];
                        $skin = $_POST['skin'];
                        $address = WEB . "{$name}_{$region}_{$champion}_{$skin}.png";
                        ?>
                        <div id="result">
                            <div id="signature"><img src="<?php echo $address ?>"> </div>
                            <p>Your image will be displayed above in a few seconds.</p>
                            <br>
                            <p>And here's the url for embedding it:</p>
                            <pre id="url" ><?php echo $address ?></pre>
                            <h4>Signature in BBCode</h4>
                            <pre>[CENTER][URL='<?php echo WEB ?>'][IMG]<?php echo $address ?>[/IMG][/URL][/CENTER]</pre>
                            <br><br>
                            <div>
                                <div style="display:inline-block; margin: 0 64px;">
                                    <h3>Need more info?</h3>
                                    <p>Have a look at the post <a href='http://redd.it/1wpwls'>on reddit</a>!</p>
                                </div>
                                <div style="display:inline-block; margin: 0 64px;">
                                    <h3>Not exactly what you wanted?</h3>
                                    <p><a href='javascript:history.back()'>&larr; Go back and try again.</a></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <p id="introduction"><strong>Welcome summoner</strong>, you have found yourself on the LoL signature maker, where you can make your own signature
                            featuring the stats you have managed to achieve in the ranked games. Just like this:</p>                        
                        <br>                      
                        <div id="signature"><img src="<?php print(WEB); ?>Torrda_eune_238_1.png" title="Torrda@EUNE"/></div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div id="background-bottom">                
            <div id="wrapper-bottom">

                <div id="content-bottom">
                    <div class="warning">
                        <h3>WARNING:</h3>
                        <p>This site uses the Riot API for all the data, ranked S4 only.</p>
                        <p>It only works if you are placed in a league in Season 4!</p>
                    </div>
                    <div class="flexcontainer-center">
                        <div>
                            <h4>TIP:</h4><p> To find a champion quicker, open the selectbox and press the first few letters.</p>
                        </div>
                        <div>
                            <h4>INFO:</h4><p> It will only work if you are placed in Season 4 ranked!
                                Your signature should load really fast, if you are from one of these. 
                        </div>
                        <div>
                            <h4>STATS:</h4><p> There have been <?php print($i) ?> signatures generated in the last 24 hours!</p>
                        </div>
                        <div>
                            <h4>REDDIT:</h4><p> If you have doubts, questions, ideas or bugs to report, do so here: <a href="http://redd.it/1wpwls">on reddit</a>!</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="background-footer">
            <div id="wrapper-footer">
                <div id="content-footer">
                    <div id="footer">
                        Signature creator itself was written by <strong><a href="https://twitter.com/erthylol">Erthy</a></strong> 
                        (<a href="http://www.lolking.net/summoner/eune/26174422">Erthainel</a>@EUNE).<br>
                        Others: interface by <strong>Sun</strong>, props to <strong>[zG]Woods</strong>, champion numbers by <strong>Hobbesclone</strong> and skin numbers <strong>[zG]Viitrexx</strong>.<br>
                        <br>The LoL Signature Generator isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.
                        <br>
                        <p style="color:gray;">DJ4pw1ue4qD84QN5ZeG7hvL8YZEqHHynNW<br/>give doge? many thanks</p>  
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">

            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-41604637-1']);
            _gaq.push(['_trackPageview']);

            (function () {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
            })();

        </script>
    </body>
</html>
