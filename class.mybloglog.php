<?php
/*
 * phpMyBlogLog
 * (c) 2008 Sitening, LLC
 * http://sitening.com
 * 
 * This is a PHP wrapper class for the MyBlogLog API
 * Any questions can be sent to Jason Tan via email: [first name] AT sitening.com
 *
 */

class MyBlogLog
{
	private $app_id;
	private $community_id;
	
	public $last_response;
	public $last_response_raw;
	public $first;
	public $total;
	public $returned;
	
	public $request_url_base = "http://mybloglog.yahooapis.com/v1/";
	public $timeout = 10;  // 10 seconds

	public $cache_dir = "";
	public $cache_minutes = 60;
	
	function __construct($app_id,$community_id=NULL)
	{
		$this->app_id = $app_id;
		$this->community_id = $community_id;
	}
	
	private function reset()
	{
		unset($this->last_response);
		unset($this->last_response_raw);
		unset($this->first);
		unset($this->total);
		unset($this->returned);
	}
	
	private function makeCall($url, $parameters=NULL)
	{
		$this->reset();
		if (!is_array($parameters))
			$parameters = array();
		$parameters["format"] = "php";
		$parameters["appid"] = $this->app_id;
		$parameters = array_map("urlencode",$parameters);
		
		if ($this->cache_dir && file_exists($this->cache_dir) && is_dir($this->cache_dir) && is_writable($this->cache_dir))
		{
			$cache_file = $this->cache_dir . "/" . md5(serialize(func_get_args()));
			if (file_exists($cache_file) && (filemtime($cache_file) > time()-($this->cache_minutes*60))) {
				$result = file_get_contents($cache_file);
			}
		}
		
		if (!isset($result)||$result=="") {
			$first = true;
			$postfields = "";
			foreach ($parameters as $parameter=>$value) {
				if (!$first)
					$postfields .= "&";
				$first = false;
				$postfields .= "$parameter=$value";
			}
			$url .= "?" . $postfields;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->request_url_base . $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
#			curl_setopt($ch, CURLOPT_POST, 1);
#			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			$result = curl_exec($ch);
			$temp = @unserialize($result);  //check to make sure the result is a serialized array
			if (is_array($temp) && isset($cache_file)) {
				file_put_contents($cache_file, $result);
			}
		}
		
		$this->last_response_raw = $result;
		$u_result = @unserialize($result);
		if(!is_array($u_result)) {
			// if there's an error, try using the cache, even if it's expired
			if ($this->cache_dir && file_exists($this->cache_dir) && is_dir($this->cache_dir))
			{
				$cache_file = $this->cache_dir . "/" . md5(serialize(func_get_args()));
				if (file_exists($cache_file)) {
					$result = file_get_contents($cache_file);
					$u_result = @unserialize($result);
				}
			}
		}
		if(is_array($u_result)) {
			$result = $u_result;
			if (isset($result["yahoo:first"])) {
				$this->first = $result["yahoo:first"];
				$this->total = $result["yahoo:total"];
				$this->returned = $result["yahoo:returned"];
			}
		}
		else
			$result = false;
		
		$this->last_response = $result;
		return $result;
	}
	
	private static function handlePaging($count,$start)
	{	
		$parameters = array();
		if ($count>100)
			$count = 100;
		if ($count>0)
			$parameters["count"] = $count;
		if ($start>0)
			$parameters["start"] = $start;
		return $parameters;
	}
	
	private static function cleanResponse($result,$mapping)
	{
		$return = array();
		foreach ($mapping as $raw=>$clean) {
			$return[$clean] = $result[$raw];
		}
		return $return;
	}
	
	/*********** API Calls ***********/
	
	
	/********* community.authors ***********/
	
	// community.authors.list
	function communityAuthorsList($community_id=NULL, $count=NULL, $start=NULL)
	{	
		if (!$community_id)
			$community_id = $this->community_id;
			
		$url = "community/$community_id/authors";
		$mapping = array("id"=>"id","nickname"=>"nickname","pict"=>"pict");
		$parameters = self::handlePaging($count,$start);
			
		if ($this->makeCall($url,$parameters)) {
			$raw_users = $this->last_response['author'];
			$users = array();
			foreach ($raw_users as $user)
				$users[] = self::cleanResponse($user,$mapping);
			return $users;
		}
		else
			return false;
	}
	
	
	/********* community.find ***********/
	
	//community.find.byid
	function communityFindById($community_id=NULL)
	{
		if (!$community_id)
			$community_id = $this->community_id;
			
		$url = "community/$community_id";
		$mapping = array(
			"id"=>"id",
			"member_count"=>"member_count",
			"author_count"=>"author_count",
			"name"=>"name",
			"description"=>"description",
			"url"=>"url",
			"site_url"=>"site_url",
			"pict"=>"pict",
			"yahoo:created"=>"created"
		);
		
		if ($result = $this->makeCall($url)) {
			return self::cleanResponse($result, $mapping);
		}
		else
			return false;
	}
	
	//community.find.byname
	function communityFindByName($name)
	{
		$name = urlencode($name);
		$url = "community/name/$name";
		$mapping = array(
			"id"=>"id",
			"name"=>"name",
			"url"=>"url",
			"yahoo:created"=>"created"
		);
		
		if ($result = $this->makeCall($url)) {
			return self::cleanResponse($result, $mapping);
		}
		else
			return false;
	}
	
	//community.find.bytag
	function communityFindByTag($tag, $count=NULL, $start=NULL)
	{	
		$tag = urlencode($tag);
		$url = "community/tag/$tag";
		$mapping = array(
			"id"=>"id",
			"name"=>"name",
			"url"=>"url",
			"site_url"=>"site_url",
			"pict"=>"pict",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);
		
		if ($result = $this->makeCall($url,$parameters)) {
			$raw_communities = $this->last_response['community'];
			$communities = array();
			foreach ($raw_communities as $community)
				$communities[] = self::cleanResponse($community,$mapping);
			return $communities;
		}
		else
			return false;
	}
	
	
	/********* community.members ***********/
	
	//community.members.list
	function communityMembersList($community_id=NULL, $count=NULL, $start=NULL)
	{
		if (!$community_id)
			$community_id = $this->community_id;
		
		$url = "community/$community_id/members";			
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"pict"=>"pict",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);
			
		if ($this->makeCall($url,$parameters)) {
			$raw_users = $this->last_response['user'];
			$users = array();
			foreach ($raw_users as $user)
				$users[] = self::cleanResponse($user,$mapping);
			return $users;
		}
		else
			return false;
	}
	
	
	/********* community.readers ***********/
	
	//community.readers.list
	function communityReadersList($community_id=NULL, $count=NULL, $start=NULL)
	{
		if (!$community_id)
			$community_id = $this->community_id;
		
		$url = "community/$community_id/readers";
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"url"=>"url",
			"pict"=>"pict",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_users = $this->last_response['users']['user'];
			$users = array();
			foreach ($raw_users as $user)
				$users[] = self::cleanResponse($user,$mapping);
			return $users;
		}
		else
			return false;
	}
	
	//community.readers.list.compare
	function communityReadersListCompare($filter_community_id, $community_id=NULL, $count=NULL, $start=NULL)
	{
		if (!$community_id)
			$community_id = $this->community_id;
		
		$url = "community/$community_id/readers/compare/$filter_community_id";
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"url"=>"url",
			"pict"=>"pict"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_users = $this->last_response['member'];
			$users = array();
			foreach ($raw_users as $user)
				$users[] = self::cleanResponse($user,$mapping);
			return $users;
		}
		else
			return false;
	}
	
	
	/********* community.tags ***********/

	//community.tags.list
	function communityTagsList($community_id=NULL, $count=NULL, $start=NULL)
	{
		if (!$community_id)
			$community_id = $this->community_id;
		
		$url = "community/$community_id/tags";
		$mapping = array(
			"id"=>"id",
			"name"=>"name",
			"count"=>"count",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_tags = $this->last_response['tag'];
			$tags = array();
			foreach ($raw_tags as $tag)
				$tags[] = self::cleanResponse($tag,$mapping);
			return $tags;
		}
		else
			return false;
	}
	
	
	/********* member.communities ***********/
	
	//member.communities.joined.list
	function memberCommunitiesJoinedList($member_id, $count=NULL, $start=NULL)
	{		
		$url = "user/$member_id/communities/joined";
		$mapping = array(
			"id"=>"id",
			"name"=>"name",
			"url"=>"url",
			"pict"=>"pict",
			"description"=>"description",
			"site_url"=>"site_url",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_communities = $this->last_response['community'];
			$communities = array();
			foreach ($raw_communities as $community)
				$communities[] = self::cleanResponse($community,$mapping);
			return $communities;
		}
		else
			return false;
	}
	
		
	/********* member.contacts ***********/
	
	//member.contacts.list
	function memberContactsList($member_id, $count=NULL, $start=NULL)
	{
		$url = "user/$member_id/contacts";
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"pict"=>"pict",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_contacts = $this->last_response['contact'];
			$contacts = array();
			foreach ($raw_contacts as $contact)
				$contacts[] = self::cleanResponse($contact,$mapping);
			return $contacts;
		}
		else
			return false;
	}
	
	
	/********* member(s).find ***********/
	
	//member.find.byscreenname
	function memberFindByScreenName($screen_name)
	{
		$screen_name = urlencode($screen_name);
		$url = "user/screen_name/$screen_name";
		$mapping = array(
			"id"=>"id",
			"name"=>"name",
			"yahoo:created"=>"created"
		);

		if ($result = $this->makeCall($url)) {
			return self::cleanResponse($result, $mapping);
		}
		else
			return false;
	}
	
	//member.find.byid
	function memberFindById($member_id)
	{
		$url = "user/$member_id";
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"screen_name"=>"screen_name",
			"pict"=>"pict",
			"url"=>"url",
			"profile"=>"profile",
			"sites_authored"=>"sites_authored", 		// no alterative yet
			"communities_joined"=>"communities_joined",	// use member.communities.joined.list
			"tags"=>"tags", 							// no alterative yet
			"yahoo:created"=>"created"
		);

		if ($result = $this->makeCall($url)) {
			return self::cleanResponse($result, $mapping);
		}
		else
			return false;
	}
	
	//member.find.byservice
	function memberFindByService($service_name,$id)
	{
		$service_name = urlencode($service_name);
		$url = "user/service/$service_name/$id";
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"screen_name"=>"screen_name",
			"pict"=>"pict",
			"url"=>"url",
		);

		if ($result = $this->makeCall($url)) {
			return self::cleanResponse($result['user'], $mapping);
		}
		else
			return false;
	}
	
	//member.newwithcontacts
	function memberNewWithContacts($member_id, $count=NULL, $start=NULL)
	{
		$url = "user/$member_id/newwithcontacts";
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_events = $this->last_response['event'];
			//$events = array();
			//foreach ($raw_events as $event)
			//	$events[] = self::cleanResponse($event,$mapping);
			return $raw_events;
		}
		else
			return false;
	}
	
	//member.newwithme
	function memberNewWithMe($member_id, $count=NULL, $start=NULL)
	{
		$url = "user/$member_id/newwithme";
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_events = $this->last_response['event'];
			//$events = array();
			//foreach ($raw_events as $event)
			//	$events[] = self::cleanResponse($event,$mapping);
			return $raw_events;
		}
		else
			return false;
	}
	
	//members.find.bytag
	function membersFindByTag($tag, $count=NULL, $start=NULL)
	{
		$tag = urlencode($tag);
		$url = "user/tag/$tag";
		$mapping = array(
			"id"=>"id",
			"nickname"=>"nickname",
			"pict"=>"pict",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);
		
		if ($result = $this->makeCall($url,$parameters)) {
			$raw_members = $this->last_response['member'];
			$members = array();
			foreach ($raw_members as $member)
				$members[] = self::cleanResponse($member,$mapping);
			return $members;
		}
		else
			return false;
	}
	
	
	/********* member.messages ***********/
	
	//member.messages.list
	function memberMessagesList($member_id, $count=NULL, $start=NULL)
	{
		$url = "user/$member_id/messages";
		$mapping = array(
			"id"=>"id",
			"public"=>"public",
			"body"=>"body",
			"date"=>"date",
			"member"=>"member"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_messages = $this->last_response['messages']['message'];
			$messages = array();
			foreach ($raw_messages as $message)
				$messages[] = self::cleanResponse($message,$mapping);
			
			//non-standard paging information returned... populate by hand
			$this->returned = count($messages);
			$this->first = $this->last_response['messages']['start'];
			$this->total = $this->last_response['messages']['count'];
			return $messages;
		}
		else
			return false;
	}
	
	//member.messages.public.list
	function memberMessagesPublicList($member_id, $count=NULL, $start=NULL)
	{
		$url = "user/$member_id/messages/public";
		$mapping = array(
			"id"=>"id",
			"public"=>"public",
			"body"=>"body",
			"date"=>"date",
			"member"=>"member"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_messages = $this->last_response['messages']['message'];
			$messages = array();
			foreach ($raw_messages as $message)
				$messages[] = self::cleanResponse($message,$mapping);
			
			//non-standard paging information returned... populate by hand
			$this->returned = count($messages);
			$this->first = $this->last_response['messages']['start'];
			$this->total = $this->last_response['messages']['count'];
			return $messages;
		}
		else
			return false;
	}


	/********* member.tags ***********/

	//member.tags.list
	function memberTagsList($member_id, $count=NULL, $start=NULL)
	{
		$url = "user/$member_id/tags";
		$mapping = array(
			"name"=>"name",
			"yahoo:created"=>"created"
		);
		$parameters = self::handlePaging($count,$start);

		if ($result = $this->makeCall($url,$parameters)) {
			$raw_tags = $this->last_response['tag'];
			$tags = array();
			foreach ($raw_tags as $tag)
				$tags[] = self::cleanResponse($tag,$mapping);
			return $tags;
		}
		else
			return false;
	}


	/********* test.echo ***********/

	//test.echo
	function testEcho()
	{
		$url = "test/echo";

		if ($result = $this->makeCall($url)) {
			return $result['echo']['message'];
		}
		else
			return false;
	}
	
}
?>