<?php
//mb_internal_encoding("UTF-8");
class Player
{
    public $id;
    public $name;
    public $region;

    public $rank;
    public $rank_roman;
    public $lp;
    public $league;
    public $tier;

    public $stats;

    public $status;

    function __construct($name, $region)
    {
        $this->loadPlayer(rawurldecode($name), $region);
        $this->check(1);

        $this->loadRankedBasic();
        $this->check(2);

        $this->loadRankedStats();
        $this->check(3);
    }

    function check($loop) {
        /*
          404 - not found
          429 - too many requests
        */
        $status = $this->status;
        if ($status==404 && isset($this->id)){
            $status=404;
        }
        switch ($status) {
            case 404: throw new Exception($status); break;
            case 429: throw new Exception($status); break;
            case 4041: throw new Exception($status); break;
            case 503: throw new Exception($status); break;
        }
    }

    /*
      Gets the proper name and ID of a player under a specified name.
    */
    function loadPlayer($name, $region) {
        // SUMMONER 1.4
        $this->region = $region;

        // using name given to script - to get player instance
        //$addr = 'https://'.$regions[$this->region].'.api.pvp.net/api/lol/'.$region.'/v1.4/summoner/by-name/'.$name.'?api_key='.API_KEY;
        $addr = 'https://'.$this->region.'.api.riotgames.com/lol/summoner/v3/summoners/by-name/'.$name.'?api_key='.API_KEY;

        $data = $this->getData($addr);

        // !! check for returned status !!
        $j = json_decode($data, True);

        // get ID and proper name
        if (array_key_exists("id", $j) && array_key_exists("name", $j)) {
            $this->name = $j["name"];
            $this->id = $j["id"];
        } else {
            $this->status = 404;
        }
    }

    function getOldRegion($region) {
        $old_regions = array("eun1"=>"eune", "euw1"=>"euw", "na1"=>"na", "oc1"=>"oce", "la2"=>"las", "la1"=>"lan", "jp1"=>"jp", "br1"=>"br", "tr1"=>"tr", "ru"=>"ru", "kr"=>"kr",);
        return $old_regions[$region];
    }

    function loadRankedBasic() {
        // get ranked stats by ID - league, division name, ...
        // LEAGUE 2.5
        // not being bloody deprecated so REGION codes are not globally the same... rito pls.

        $region = $this->getOldRegion($this->region);
        $id = $this->id;

        $addr = 'https://'.$region.'.api.pvp.net/api/lol/'.$region.'/v2.5/league/by-summoner/'.$id.'?api_key='.API_KEY;

        $data = $this->getData($addr);

        $j = json_decode($data, True);
        if (isset($j["status"]["statuscode"])) {
            if($j["status"]["statuscode"]==503) {
                throw new Exception(503);
            }
        }
        $this->rank_roman = 0;
        $this->lp = 0;

        if (array_key_exists(strval($this->id), $j)) {
            foreach($j[strval($this->id)] as $table) {
                if ($table["queue"] == "RANKED_SOLO_5x5") {
                    $this->league = $table["name"];
                    $this->tier = $table["tier"];

                    foreach($table["entries"] as $num) {
                        if ($num["playerOrTeamId"]==$id){
                            $this->rank_roman = $num["division"];
                            $this->lp = $num["leaguePoints"];
                            $this->rank = $this->r2a($this->rank_roman);
                        }
                    }
                }
            }
        } else {
            $this->status = 404;
        }

    }

    function loadRankedStats() {
        // STATS 1.3

        $id = $this->id;
        $region = $this->getOldRegion($this->region);
        $stats = array();


        // get detailed ranked stats by ID
        $addr = 'https://'.$region.'.api.pvp.net/api/lol/'.$region.'/v1.3/stats/by-summoner/'.$id.'/ranked?api_key='.API_KEY;

        $data = $this->getData($addr);

        $j = json_decode($data, True);
        if (isset($j) && array_key_exists("champions", $j)) {
            foreach ($j["champions"] as $champion) {
    			if ($champion["id"]=="0") {
    				$combined = $champion;
    			}
    		}
            $s = $combined["stats"];

            $wins = $s["totalSessionsWon"];
            $losses = $s["totalSessionsLost"];

            $wratio = round(100*$wins/($wins+$losses),1);

            array_push($stats, array("Pentas", $s["totalPentaKills"]));
            array_push($stats, array("Win ratio", $wratio." %"));
            array_push($stats, array("Kills", $s["totalChampionKills"]));
            array_push($stats, array("Assists", $s["totalAssists"]));
            array_push($stats, array("Max Spree", $s["maxLargestKillingSpree"]));
        } else {
            array_push($stats, array("Riot cancelled the", ""));
            array_push($stats, array("stats API. I'm work-", ""));
            array_push($stats, array("ing on a replace-", ""));
            array_push($stats, array("ment, but it will", ""));
            array_push($stats, array("take a while.", ""));
        }

        $this->stats = $stats;
    }

    function getData($url) {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($handle,CURLOPT_SSL_VERIFYPEER, false);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        $this->status = $httpCode;

        curl_close($handle);

        $this->check(0);

        return $response;
    }

    function getData2($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    function r2a($roman) {
        switch ($roman) {
            case "I": return 1;
            case "II": return 2;
            case "III": return 3;
            case "IV": return 4;
            case "V": return 5;
            default: return 0;
        }
    }
}
?>
