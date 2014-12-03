<!DOCTYPE HTML >
<html>
<head>
  <meta charset="UTF-8" />
  <title>League of Legends signature creator</title>
  <meta http-equiv='Content-language' content='en'>
  <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" charset="utf-8">
$(function(){
  $("select#champion").change(function(){
    $.getJSON("./get_skins_json.php",{id: $( "#champion option:selected" ).text(), ajax: 'true'}, function(j){
      var options = '';
      for (var i = 0; i < j.length; i++) {
        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
      }
      $("select#skin").html(options);
    })
  })
})
</script>
<body>
<div id="wrapper">
<?php
 
if (isset($_POST['sub']) && 
    isset($_POST['lolname']) &&
    isset($_POST['region']) &&
    isset($_POST['champion']) &&
    isset($_POST['skin']) &&
    isset($_POST['logo'])) 
{
    $name = $_POST['lolname'];
    $region = $_POST['region'];
    $champion = $_POST['champion'];
    $skin = $_POST['skin'];
    $logo = $_POST['logo'];
    $address = "http://broukej.cz/lol-signatures/{$name}_{$region}_{$champion}_{$skin}_{$logo}.png"; 
    echo "<div id='result'> <h1>League of Legends signature creator</h1><p><img src=\"{$address}\"> <br />"; 
    echo "Your image will be displayed above in a few seconds. <br/> 
             And here's the url for embedding it:<br />
             <span id='url'>{$address}</span></p>";
    echo "<h2>Signature in BBCode</h2>
          <p>[CENTER][URL='http://www.broukej.cz/lol-signatures/'][IMG]{$address}[/IMG][/URL][/CENTER]</p>";
    echo "<h2>Need more info?</h2><p>Have a look at the post <a href='http://redd.it/1wpwls'>on reddit</a>!</p>";
    echo "<h2>Not exactly what you wanted?</h2><p><a href='javascript:history.back()'>&larr; Go back and try again.</a></p>";      
    echo "</div>";
} else {
    // integer starts at 0 before counting
    $dir = 'sigs_cache/';
    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false){
            if (!in_array($file, array('.', '..')) && !is_dir($dir.$file)) 
                $i++;
        }
    } 
    // prints out how many were in the directory
    //echo "There were $i files";

    ?>
<div id='form'> 
<h1>League of Legends signature creator</h1>
    <form action="" id="data" method="POST">
        <input class="input-xlarge" type="text" placeholder="Summoner Name" name="lolname">
<!-- Not too sure of the other region codes -->
        <select name="region">
            <option value="na">NA</option>
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
                print '<option value="'.$id[0].'">'.$champion.'</option>
                ';
        }?>
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
 
        <select name="logo">
            <option value="logo">zG Logo</option>
            <option value="text">zG Text</option>
            <option value="nozg">No zG Watermark</option>
        </select> 
        <input type="submit" name="sub">
    </form>
    <p><strong>Welcome summoner</strong>, you have found yourself on the LoL signature maker, where you can make your own signature
    featuring the stats you have managed to achieve in the ranked games. Just like this:</p>
    <p><img src="http://broukej.cz/lol-signatures/Bjerg_na_238_1_nozg.png" title="Bjerg@NA" /></p>
    
    <p><strong><span style="color:red;">Known bugs/warnings:</span></strong>
    <ul style="text-align:left;margin-left:70px;color:red;">
      <li>The Riot API has been updated, please be patient until I get the script up to date</li>
	  <li>It only works if you are placed in a league in Season 4! (this is not a bug)</li>
      <li><strike>Spaces</strike> and special characters currently cause issues.</li>
      <li><strike>Win percentage doesn't show the percentage sign afterwards.</strike></li>
    </ul></p>
    <p><strong>TIPS:</strong> To find a champion quicker, open the selectbox and press the first few letters.</p>
    <p><strong>INFO:</strong> It will only work if you are placed in Season 4 ranked! The site currently fetches data from the EUNE, EUW and NA servers directly from the Riot API. 
    Your signature should load really fast, if you are from one of these. For the other regions, the old LoLKing.net fetcher is still being used. 
    <p><strong>STATS:</strong> There have been <?php print($i) ?> signatures generated in the last 24 hours!</p>
    <p><strong>REDDIT:</strong> If you have doubts, questions, ideas or bugs to report, do so here: <a href="http://redd.it/1wpwls">on reddit</a>!</p>
</div> 
<?php
}
?>
<hr/>
<div id="footer">
  Created for the League of Legends division of <a href="http://www.zealotgaming.com/">Zealot Gaming</a>.<br />
  Signature creator itself was written by <strong><a href="https://twitter.com/erthylol">Erthy</a></strong> 
  (<a href="http://www.lolking.net/summoner/eune/26174422">Erthainel</a>@EUNE).<br />
  Others: interface by <strong>Sun</strong>, props to <strong>[zG]Woods</strong>, champion numbers by <strong>Hobbesclone</strong> and skin numbers <strong>[zG]Viitrexx</strong>.<br/>
  The LoL Signature Generator isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.
</p>
<p style="color:gray;">DJ4pw1ue4qD84QN5ZeG7hvL8YZEqHHynNW<br/>give doge? many thanks</p>
<p>  
</div>
</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-41604637-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>
