<?php
class MBLMember
{
	public $services = array();
	public $bio;
	public $location;
	
	public static $mbl_services = array(
		"bebo" => array("http://bebo.com/Profile.jsp?MemberId=%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ibebo_id.png"),
	 	"blogger" => array("http://www.blogger.com/profile/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iblogger_id.png"),
		"del.icio.us" => array("http://del.icio.us/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/idelicious.png"),
		"digg" => array("http://digg.com/users/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/idigg.png"),
		"ebay" => array("http://myworld.ebay.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iebay.png"),
		"facebook" => array("http://www.facebook.com/profile.php?id=%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ifacebook.png"),
		"flickr" => array("http://www.flickr.com/photos/%/","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iflickr.png"),
		"friendster" => array("http://%.blogs.friendster.com","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ifriendster.png"),
		//"google calendar" => array("http://www.google.com/calendar/embed?src=%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/igooglecalendar.png"),
		"jaiku" => array("http://%.jaiku.com","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ijaiku.png"),
		"jumpcut" => array("http://www.jumpcut.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ijumpcut.png"),
		"kiva" => array("http://www.kiva.org/lender/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ikiva.png"),
		"linkedin" => array("http://www.linkedin.com/in/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ilinkedin.png"),
		"last.fm" => array("http://www.last.fm/user/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ilastfm.png"),
		"myspace" => array("http://www.myspace.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/imyspace.png"),
		"netflix" => array("http://rss.netflix.com/QueueRSS?id=%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/inetflix.png"),
		"openid" => array("%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iopenid.png"),
		"plaxo" => array("mailto:%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iplaxo.png"),
		"pownce" => array("http://www.pownce.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ipownce.png"),
		//"second life" => array("","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/isecond_life.png"),
		"shelfari" => array("http://www.shelfari.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ishelfari.png"),
		"stumbleupon" => array("http://%.stumbleupon.com/","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/istumbleupon.png"),
		"technorati" => array("http://www.technorati.com/people/technorati/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/itechnorati.png"),
		"textamerica" => array("http://%.textamerica.com/","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/itextamerica.png"),
		"the dj list" => array("http://www.thedjlist.com/djs/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/ithedjlist.png"),
		"30 boxes" => array("%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/i30boxes.png"),
		"twitter" => array("http://twitter.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/itwitter.png"),
		"typekey" => array("http://profile.typekey.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/itypekey.png"),
		"upcoming" => array("http://upcoming.yahoo.com/user/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iupcoming.png"),
		"wakoopa" => array("http://www.wakoopa.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iwakoopa.png"),
		"wink" => array("http://wink.com/profile/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iwink.png"),
		"yelp" => array("http://www.yelp.com/user_details?userid=%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iyelp.png"),
		"youtube" => array("http://youtube.com/user/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/iyoutube.png"),
		"zorpia" => array("http://www.zorpia.com/%","http://us.i1.yimg.com/us.yimg.com/i/us/mbl/services/izorpia.png")
	);
	
	function __construct($m)
	{
		if (is_array($m)) {
			if (isset($m['profile']['services'])&&is_array($m['profile']['services']['service'])) {
				foreach ($m['profile']['services']['service'] as $service) {
					if ($service['name']=="flickr")
						$this->services[$service['name']] = $service['nsid'];
					else
						$this->services[$service['name']] = $service['id'];
				}
			}
			
			if (isset($m['profile']['location'])) {
				$location = $m['profile']['location'];
				$this->location = $location['city'] . " " . $location['region'] . " " . $location['country'] . " " . $location['zip'];
				if (isset($location['bio'])&&$location['bio']!="")
					$this->bio = $location['bio'];
			}
				
		}
	}
	
	function getServiceLink($service, $url = false)
	{
		if (array_key_exists($service,$this->services) && array_key_exists($service,self::$mbl_services)) {
			if (!$url)
				if ($service=="openid")
					return "<a href=\"" . str_replace("%",$this->services[$service],self::$mbl_services[$service][0]) . "\"><img src=\"" . self::$mbl_services[$service][1] . "\" /></a>";
				else
					return "<a href=\"" . str_replace("%",urlencode($this->services[$service]),self::$mbl_services[$service][0]) . "\"><img src=\"" . self::$mbl_services[$service][1] . "\" /></a>";
			else
				return "<a href=\"" . $url . "\"><img src=\"" . self::$mbl_services[$service][1] . "\" /></a>";
		}
		else
			return "";
	}
	
	function getServiceLinks($url = false, $subset = false, $delimiter = " ")
	{
		$links = "";
		foreach ($this->services as $service=>$id) {
			if (is_array($subset))
				if (!in_array($service,$subset))
					continue;
					
			if ($url)
				$newurl = $url . "#$service";
			else
				$newurl = false;
			$link = $this->getServiceLink($service, $newurl);
			if ($link!="") {
				if (!$links=="")
					$links .= $delimiter;
				$links .= $link;
			}
		}
		return $links;
	}
}


class ServicesDelicious
{	
	public $user_id;
	public $links;
	public $link;
	
	function __construct($id)
	{
		$this->user_id = urlencode($id);
		$this->populate();
	}
	
	function populate()
	{
		$url = "http://feeds.delicious.com/feeds/json/{$this->user_id}?raw";
		$response = geturl($url);
		$response = preg_replace("/\\\'/","'",$response);	//del.icio.us incorrectly escapes single quotes.
		$delicious = json_decode($response);
		if (count($delicious)>0) {
			$this->links = $delicious;
		}
		$this->link = "http://del.icio.us/" . $this->user_id;
	}
	
	function display($num=5)
	{
		if (!isset($this->links))
			return false;
		else {
			$returntext .= "<h3>del.icio.us links</h3><ul>";
			foreach ($this->links as $index=>$link) {
                $returntext .= "<li><a href=\"{$link->u}\" target=\"newDeliciousWindow\">{$link->d}</a>";
                $returntext .= " (tags: <em>" . join(", ", $link->t) . "</em>)</li>";
				if ($index>=$num-1)
					break;
			}
			$returntext .= "</ul><br/>";
			return $returntext;
		}
	}
}

function geturl($url, $username = "", $password = "", $useragent = "")
{
	if(function_exists("curl_init"))
	{
		$ch = curl_init();
		if(!empty($username) && !empty($password))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' .  base64_encode("$username:$password")));
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		if (!empty($useragent))
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		$html = curl_exec($ch);
		curl_close($ch);
		return $html;
	}
	elseif(ini_get("allow_url_fopen") == true)
	{
		if(!empty($username) && !empty($password))
			$url = str_replace("://", "://$username:$password@", $url);
		$html = file_get_contents($url);
		return $html;
	}
	else
	{
		// Cannot open url. Either install curl-php or set allow_url_fopen = true in php.ini
		return false;
	}
}
?>