<?php
include_once("player.php");
include_once("playerlk.php");

function remoteFileExists($url) {
    $curl = curl_init($url);

    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_NOBODY, true);

    //do request
    $result = curl_exec($curl);
    $ret = false;

    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
        if ($statusCode == 200) {
            $ret = true;   
        }
    }

    curl_close($curl);
    return $ret;
}

/* -- Defining constants ---------------------------------------------------- */
const API_KEY = "";
const SERVER_CACHE = 43200; //12 hodin
const BROWSER_CACHE = 10800; //3 hodiny;

/* ---------------------------------------------------------------------------*/

if (isset($_GET["region"]) && isset($_GET["name"])){
    // get name and region
    $region = strtolower($_GET["region"]);
    $name = $_GET["name"];
    
    if (isset($_GET["champnum"])) {
        $champnum = $_GET["champnum"];
        if (isset($_GET["skinnum"])){
            $skinnum = $_GET["skinnum"];
        } else {
            $skinnum = 0;
        }
    }
    
    // get params for logo/text/nothing
    $params = "logo";
    if (isset($_GET["params"])) {
        if (strpos($_GET["params"],'nozg') !== false) {
            $params = "nozg";
        }
        elseif (strpos($_GET["params"],'text') !== false) {
            $params = "text";
        }
    }
    
    $sigs_cache_location = "sigs_cache/"; 
    $file = $sigs_cache_location.$name."_".$region."_".$champnum."_".$skinnum."_".$params.".png";
    if (file_exists($file) && (time() - filemtime($file) < SERVER_CACHE)) {
        $loading_from_cache = True;
        Header("Content-type: image/png");                      
        Header("Cache-Control: max-age=".BROWSER_CACHE);       // Browser caching
        readfile($file);
        exit();
    } else {
        $loading_from_cache = False;
    }
} else {
    Header("Content-type: image/png");                      
    Header("Cache-Control: max-age=0");       // Browser caching
    readfile("img/bad_parameters.png");
    exit();
}

/* ---------------------------------------------------------------------------*/

try {
    if ($region == "euw" || $region == "eune" || $region == "na") {
        $p = new Player($name, $region);
    } else {
        $p = new PlayerLK($name, $region);
    }
} catch (Exception $e) {
    Header("Content-type: image/png");                      
    Header("Cache-Control: max-age=0");       // Browser caching
    readfile("img/".$e->getMessage().".png");
    exit();
}

// basic info
$id = $p->id;
$name = $p->name;

// general ranked status
$league = $p->league;
$tier = $p->tier;
$rank = $p->rank;
$rank_roman = $p->rank_roman;
$lp = $p->lp;

// stats
$stats = $p->stats;
/* -------------------------------------------------------------------------- */

$show_image= true;

if ($show_image) {
  $division = ucfirst(strtolower($tier))." ".$rank;
  if ($lp) {
      $division = $division.", ".$lp." LP";
  }
  
  $div_name = strtolower($tier);
  
  /* setting image size */ 
  $width = 450;
  $height = 80;
  $ratio = $width/$height;
  
  /* creating background and filling it with transparent color */
  $mask = imagecreatetruecolor( $width, $height );
  $transparent = imagecolorallocatealpha( $mask, 0, 0, 0, 127 ); 
  imagefill( $mask, 0, 0, $transparent );
  
  /* if champion isn't set, leave transparent background */
  if ($champnum==0) {
      imagealphablending($mask, 0);
      imagesavealpha($mask, 1);
  } else { 
      $local_path = 'back_cache/'.$champnum.'_'.$skinnum.'.png';
      $remote_path = 'http://img.lolking.net/shared/images/champion_headers/'.$champnum.'_'.$skinnum.'.jpg';
      
      if (file_exists($local_path)) {
          // check if background is cached
          $back = imagecreatefrompng($local_path);
      }
      elseif (remoteFileExists($remote_path)) {
          // try to get the background from lolking.net
          $back = imagecreatefromjpeg($remote_path);
          // cache it
          imagepng($back, $local_path);
      } else {
          // skin does not exist, reverting to transparent background
          imagealphablending($mask, 0);
          imagesavealpha($mask, 1);
      }           

      // if a custom background exists
      if (isset($back)) {
        imagealphablending($back, 0);
        imagesavealpha($back, 1);
      
        // merging mask and back
        imagecopyresampled($mask, $back, 0, 0, 60, 0, $width, $height, 142*$ratio, 142);
      
        $stat_back = imagecreatetruecolor(10, 10);
        $transparent_black = imagecolorallocatealpha( $stat_back, 0, 0, 0, 55); 
        imagefill( $stat_back, 0, 0, $transparent_black );
      
        imagealphablending($stat_back, 0);
        imagesavealpha($stat_back, 1);
      
        imagecopyresampled($mask, $stat_back, $width*0.75, $height*0.07, 0, 0, $width*0.24,$height*(1-0.12), 10, 10);

	imagedestroy($back);
      }
  }
  
  /* adding stats to the image*/ 
  $flag = imagecreatefrompng('meds/'.$div_name.'_1.png');
  
  imagealphablending($flag, 0);
  imagesavealpha($flag, 1);
  
  imagecopyresampled($mask, $flag, 7, 2, 20, 20,$height,$height, 162, 162);
  
  $black = imagecolorallocate($mask,0,0,0);
  $white = imagecolorallocate($mask,255,255,255);
  
  /* font importing */
  $font = 'fonts/latin_becker_compress.ttf';
  $font_big = 'fonts/GFSNeohellenic.ttf';
  $font_tahoma = 'fonts/tahoma.ttf';
  
  $name_margin = 0;
  
  if ($params == "text") {
      imagettftext($mask, 7, 0, $width*(0.005), $height*0.95, $white, $font_tahoma, "zG");
  } 
  elseif ($params == "logo"){
      $flag = imagecreatefrompng('./img/logo.png');
    
      imagealphablending($flag, 0);
      imagesavealpha($flag, 1);
      $m=1.3;
      imagecopyresampled($mask, $flag, $height*1.1, $height*0.1, 0, 0,15*$m,12*$m, 60*(15/12), 60);
      
      $name_margin = 15*$m+2;
  }
  
  /* adding black stats (shadows) */
  imagettftext($mask, 17, 0, $height*(0.75)+1, $height*(0.8)+1, $black, $font, $rank_roman); // division number
  imagettftext($mask, 16, 0, $height*(1.1)+1+$name_margin, 22+1, $black, $font_big, $name); // name of the player
  imagettftext($mask, 13, 0, $height*(1.1)+1, 48+1, $black, $font_big, $division); // division tier 
  imagettftext($mask, 13, 0, $height*(1.1)+1+1, 65+1, $black, $font_big, $league); // league name
  
  /* adding white stats */
  imagettftext($mask, 17, 0, $height*(0.75), $height*(0.8), $white, $font, $rank_roman); // division number 
  imagettftext($mask, 16, 0, $height*(1.1)+$name_margin, 22, $white, $font_big, $name); // name of the player
  imagettftext($mask, 13, 0, $height*(1.1), 48, $white, $font_big, $division); // division tier
  imagettftext($mask, 13, 0, $height*(1.1)+1, 65, $white, $font_big, $league); // league name

  for ($i=0;$i<5;$i++) {
      imagettftext($mask, 8, 0, $width*(0.77), 20+$i*12, $white, $font_tahoma, $stats[$i][0]);
      imagettftext($mask, 8, 0, $width*(0.90), 20+$i*12, $white, $font_tahoma, $stats[$i][1]);  
  } 
  
  $region_img = $region;
  for ($i=strlen($region_img);$i<4;$i++) {
      if (strlen($region_img)==2) {
          $region_img = $region_img." ";
      }
      $region_img = " ".$region_img;
  }
  
  imagettftext($mask, 7, 0, $width*(0.073), 15, $white, $font_tahoma, strtoupper($region_img));  
  
  // cache generated image
  imagepng($mask, $file);
  
  Header("Content-type: image/png");                      // Server caching
  Header("Cache-Control: max-age=".BROWSER_CACHE);       // Browser caching
  imagepng($mask);
  
  imagedestroy($mask);
  imagedestroy($flag);
  imagedestroy($stat_back);
  
  if (filesize($file) <= 30000) {
    if(is_file($file)) {
      unlink($file);
    }
  }
  
  $path = 'sigs_cache/';
  if ($handle = opendir($path)) {
     while (false !== ($file = readdir($handle))) {
        if ((time()-filectime($path.$file)) > 86400) {  
            if (preg_match('/\.png$/i', $file)) {
               unlink($path.$file);         
           }
        }
        if (filesize($path.$file) <= 30000) {
          if(is_file($path.$file)) {
            unlink($path.$file);
          }
        }
     }
   } 
}
