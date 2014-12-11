<?php 
class PlayerLK
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
        $this->loadPlayer($name, $region);
    }
    
    /*
      Gets the proper name and ID of a player under a specified name.
    */
    function loadPlayer($name, $region) {
        $stats = array();
        
        $addr = 'http://www.lolking.net/search?name='.str_replace(' ', '+', $name);
        $html = $this->getData($addr);
        preg_match_all('/summoner\/'.$region.'\/([0-9]*)">'.$name.'<\/a>/', $html, $matches, PREG_SET_ORDER);
        $id = $matches[0][1];
        
        $this->id = $id;
        
        preg_match_all('/summoner\/'.$region.'.*?DA2;">(.*?)<\/span>.*?Average KDA/s', $html, $matches1, PREG_SET_ORDER);
        $kda_array = explode("/",$matches1[0][1]);
        
        array_push($stats, array("KDA", round(($kda_array[0]+$kda_array[2])/$kda_array[1], 2) ));
        
        /*--------------------------------------------------------------------*/
        $addr = "http://www.lolking.net/summoner/".$region."/".$id;
  
        $html = $this->getData($addr);
        
        preg_match_all('/<iframe id="league_iframe" src="(.*)" frameborder="0"/', $html, $matches, PREG_SET_ORDER);
        $addr = 'http://www.lolking.net'.$matches[0][1];
        
        preg_match_all('/<title>(.*?) - /', $html, $matches_name, PREG_SET_ORDER);
        $name = $matches_name[0][1];
        
        $this->name = $name;
        
        preg_match_all('/Penta Kills.*?<td class="lifetime_stats_val" style="">(.*?)</s', $html, $matches, PREG_SET_ORDER);
        array_push($stats, array("Pentas", $matches[0][1]));
        
        //preg_match_all('/Ranked.*?\>Quadra Kills.*?<td class="lifetime_stats_val" style="">(.*?)</s', $html, $matches, PREG_SET_ORDER);
        //$stats[1] = $matches[0][1];
        preg_match_all('/Ranked.*?Penta.*?\>Kills.*?<td class="lifetime_stats_val" style="">(.*?)</s', $html, $matches, PREG_SET_ORDER);
        array_push($stats, array("Kills", $matches[0][1]));
        
        preg_match_all('/Ranked.*?Penta.*?\>Assists.*?<td class="lifetime_stats_val" style="">(.*?)</s', $html, $matches, PREG_SET_ORDER);
        array_push($stats, array("Assists", $matches[0][1]));
        
        preg_match_all('/Ranked.*?\>Largest Killing Spree.*?<td class="lifetime_stats_val" style="">(.*?)</s', $html, $matches, PREG_SET_ORDER);
        array_push($stats, array("Max Spree", $matches[0][1]));
        
        $html = $this->getData($addr);
        
        preg_match_all('/<div style="font: bold 28px \'Source Sans Pro\', \'Trebuchet Ms\';">(.*?)<\/div>/', $html, $matches, PREG_SET_ORDER);
        $this->league = $matches[0][1];
        
        preg_match_all('/<div style="font: 20px \'Source Sans Pro\'; color: #999;">(.*?)<\/div>/', $html, $matches, PREG_SET_ORDER);
        $this->tier = $matches[0][1];
        
        $this->rank = substr($this->tier, -1, 1);
        
        $this->rank_roman = $this->a2r($this->rank);
        
        $div_arr = explode(",", $this->tier);
        $this->tier = strtolower($div_arr[0]);
         
        /*--------------------------------------------------------------------*/
         
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
    
    function a2r($arabic) {
        switch ($arabic) {
            case 1: return "I";
            case 2: return "II";
            case 3: return "III";
            case 4: return "IV";
            case 5: return "V";
            default: return "";
        }
    } 
}
?>