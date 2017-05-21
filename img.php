<?php
header('Content-type: text/html; charset=utf-8');
mb_internal_encoding("UTF-8");

/* gets the data from a URL */
function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}
/* checks if a remote file exists */
function remoteFileExists($url) {
    $curl = curl_init($url);

    //don't fetch the actual page, you only want to check the connection is ok
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

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
    return $ret;
}

/* -- Defining constants ---------------------------------------------------- */
include_once("secrets/const.secret.php");

if (isset($_GET["debug"])) {
    $debug = True;
} else {
    $debug = False;
}

/* ---------------------------------------------------------------------------*/

if (isset($_GET["region"]) && isset($_GET["name"])){
    // get name and region
    $region = strtolower($_GET["region"]);
    $name = str_replace(" ", "", $_GET["name"]);

    if (isset($_GET["champnum"])) {
        $champnum = $_GET["champnum"];
        if (isset($_GET["skinnum"])){
            $skinnum = $_GET["skinnum"];
        } else {
            $skinnum = 0;
        }
    }

    $sigs_cache_location = "sigs_cache/";
    $file = $sigs_cache_location.$name."_".$region."_".$champnum."_".$skinnum.".png";

    // if i'm allowed to cache
    if (CACHE && $debug == False) {
        // should I cache?
        if (file_exists($file) && (time() - filemtime($file) < SERVER_CACHE)) {
            Header("Content-type: image/png");
            Header("Cache-Control: max-age=".BROWSER_CACHE);       // Browser caching
            readfile($file);
            exit();
        }
    }
} else {
    Header("Content-type: image/png");
    Header("Cache-Control: max-age=0");       // Browser caching
    readfile("img/bad_request.png");
    exit();
}


/* ---------------------------------------------------------------------------*/

try {
    if (isset($region)) {
        $json = get_data('http://localhost:9090/?summonername=Ruzgud&region=eun1');
        $j = json_decode($json);
    }
} catch (Exception $e) {
    if ($debug) {
        print($e->getMessage());
        exit();
    } else {
        Header("Content-type: image/png");
        Header("Cache-Control: max-age=0");       // Browser caching
        readfile("img/".$e->getMessage().".png");
        exit();
    }
}

// basic info
$id = $j->Summoner->id;
$name = $j->Summoner->name;

// general ranked status
$league = $j->Ranked->Division;
$tier = $j->Ranked->Tier;
$rank = $j->Ranked->LeagueNum;
$rank_roman = $j->Ranked->LeagueNumRoman;
$lp = $j->Ranked->LP;

// stats
$stats = array(
    array("Pentas", $j->Stats->PentaKills),
    array("Winrate", round((float)$j->Stats->Winrate * 100, 1 ) . ' %'),
    array("Kills", $j->Stats->Kills),
    array("KDA", round($j->Stats->KDA, 2)),
    array("Max Spree", $j->Stats->LargestKillingSpree),
);

if ($debug) {
    print("<br/>id = ".$id);
    print("<br/>name = ".$name);
    print("<br/>league = ".$league);
    print("<br/>tier = ".$tier);
    print("<br/>rank = ".$rank);
    print("<br/>rank_roman = ".$rank_roman);
    print("<br/>lp = ".$lp);
}

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
      $remote_path = 'http://www.lolking.net/shared/images/champion_headers/'.$champnum.'_'.$skinnum.'.jpg';

      if (file_exists($local_path)) {
          // check if background is cached
          $back = imagecreatefrompng($local_path);
      }
      elseif (remoteFileExists($remote_path)) {

          // try to get the background from lolking.net
          $back = imagecreatefromstring(get_data($remote_path));
          // cache it
          imagepng($back, $local_path);
          //print("fuck");exit();
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
    // medal loading and caching
    $remote_path_meds = 'http://lkimg.zamimg.com/images/medals/'.$div_name.'_'.$rank.'.png';
    $local_path_meds =  'meds_cache/'.$div_name.'_'.$rank.'.png';

    if (file_exists($local_path_meds)) {
        $flag = imagecreatefrompng($local_path_meds);
    } elseif (remoteFileExists($remote_path_meds)) {
        $flag = imagecreatefromstring(get_data($remote_path_meds));
        imagealphablending($flag, 0);
        imagesavealpha($flag, 1);
        imagepng($flag, $local_path_meds);
    }

  imagealphablending($flag, 0);
  imagesavealpha($flag, 1);

  imagecopyresampled($mask, $flag, 7, 2, 20, 20,$height,$height, 162, 162);

  $black = imagecolorallocate($mask,0,0,0);
  $white = imagecolorallocate($mask,255,255,255);

  if($debug) {print("<br/>importing fonts");}

  /* font importing */
  $font = 'fonts/latin_becker_compress.ttf';
  $font_big = 'fonts/GFSNeohellenic.ttf';
  $font_tahoma = 'fonts/tahoma.ttf';

  $name_margin = 0;
  if($debug) {print("<br/>adding lolsigs text...");}
  imagettftext($mask, 6, 0, $width*(0.005), $height*0.97, $white, $font_tahoma, "lolsigs.com");

  // sem se dá teoreticky dát summoner icon
  /*
  elseif ($params == "logo"){
      $flag = imagecreatefrompng('./img/logo.png');

      imagealphablending($flag, 0);
      imagesavealpha($flag, 1);
      $m=1.3;
      imagecopyresampled($mask, $flag, $height*1.1, $height*0.1, 0, 0,15*$m,12*$m, 60*(15/12), 60);

      $name_margin = 15*$m+2;
  }*/

  if($debug) {print("<br/>adding stats text...");}

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

  if (!$debug) {
    Header("Content-type: image/png");                      // Server caching
    Header("Cache-Control: max-age=".BROWSER_CACHE);       // Browser caching
    imagepng($mask);
  } else {
    print("<br/>I should dump image...");
  }

  imagedestroy($mask);
  imagedestroy($flag);

  if (filesize($file) <= 30000) {
    if(is_file($file)) {
      unlink($file);
    }
  }

  $path = 'sigs_cache/';
  if ($handle = opendir($path)) {
     while (false !== ($file = readdir($handle))) {
        if (file_exists($path.$file) && (time()-filectime($path.$file)) > 86400) {
            if (preg_match('/\.png$/i', $file)) {
               unlink($path.$file);
           }
        }
        // Probably mopping of failed imgs?
        if(is_file($path.$file)) {
            if (filesize($path.$file) <= 30000) {
                if (preg_match('/\.png$/i', $file)) {
                    unlink($path.$file);
            }
          }
        }
     }
   }
}
?>
