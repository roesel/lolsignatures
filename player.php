<?php 
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
        $this->loadPlayer(rawurlencode($name), $region);
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
        }
    }
    
    /*
      Gets the proper name and ID of a player under a specified name.
    */
function loadPlayer($name, $region) {
        // using name given to script - to get player instance
        $addr = 'http://prod.api.pvp.net/api/lol/'.$region.'/v1.3/summoner/by-name/'.$name.'?api_key='.API_KEY;
        
        $data = $this->getData($addr);
        
        // !! check for returned status !!
        $j = json_decode($data, True);
        
        // get ID and proper name
        $this->id = $j[strtolower($name)]["id"];
        $this->name = $j[strtolower($name)]["name"];
        $this->region = $region;
    }

    function loadRankedBasic() {
        // get ranked stats by ID - league, division name, ...
        $id = $this->id;
        $addr = 'http://prod.api.pvp.net/api/lol/'.$this->region.'/v2.3/league/by-summoner/'.$id.'?api_key='.API_KEY;
        
        $data = $this->getData($addr);
        
        $j = json_decode($data, True);
        
        $this->rank_roman = 0;
        $this->lp = 0;

        foreach($j[0]["entries"] as $num) {
            if ($num["playerOrTeamId"]==$id){
                $this->rank_roman = $num["rank"];
                $this->lp = $num["leaguePoints"];
				$this->rank = $this->r2a($this->rank_roman);
				$this->league = $num["leagueName"];
				$this->tier = $num["tier"];
            }
        }
        
    }
    
    function loadRankedStats() {
        $id = $this->id;
        
        // get detailed ranked stats by ID
        $addr = 'http://prod.api.pvp.net/api/lol/'.$this->region.'/v1.2/stats/by-summoner/'.$id.'/ranked?season=SEASON4&api_key='.API_KEY;
        
        $data = $this->getData($addr);
        
        $j = json_decode($data, True);
        
		foreach ($j["champions"] as $champion) {
			if ($champion["name"]=="Combined") {
				$combined = $champion;
			}
		}        
        $s = $combined["stats"];
        
        $stats = array();
        
        $wins = $s["totalSessionsWon"];
        $losses = $s["totalSessionsLost"];
        
        $wratio = round(100*$wins/($wins+$losses),1);
        
        array_push($stats, array("Pentas", $s["totalPentaKills"]));  
        array_push($stats, array("Win ratio", $wratio." %"));
        array_push($stats, array("Kills", $s["totalChampionKills"]));
        array_push($stats, array("Assists", $s["totalAssists"]));   
        array_push($stats, array("Max Spree", $s["maxLargestKillingSpree"]));
        
        $this->stats = $stats;    
    }
    
    function getData($url) {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
        
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);
        
        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        $this->status = $httpCode;
        
        curl_close($handle);
        
        return $response;
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