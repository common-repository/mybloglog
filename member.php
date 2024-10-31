<?php
	function mybloglog_member_title() {
		$base_url = get_option('mybloglog_base_url');
		$blog_url = parse_url(get_bloginfo('wpurl'));
		$base_url = $blog_url['path'] . '/' . $base_url;

		$screen_name = str_replace($base_url,"",$_SERVER['REQUEST_URI']);
		$screen_name = str_replace("/","",$screen_name);
		echo "Members: $screen_name";
	}
	add_action('wp_title', 'mybloglog_member_title');
	get_header();
?>
<!-- if your theme requires markup between the header and sidebar/content, insert it here -->

<?php
	if (get_option('mybloglog_sidebar_order')=="before")
		get_sidebar();
?>

<div id="mybloglog-member" class="narrowcolumn">
<p class="mbl-nav">&#171; Return to <a href="<?php echo $base_url; ?>">member listing</a></p>
<?php
	require_once("class.mybloglog.php");
	require_once("class.mblmember.php");
	
	$base_url = get_option('mybloglog_base_url');
	$blog_url = parse_url(get_bloginfo('wpurl'));
	$base_url = $blog_url['path'] . '/' . $base_url;
		
	$screen_name = str_replace($base_url,"",$_SERVER['REQUEST_URI']);
	$screen_name = str_replace("/","",$screen_name);
	
	$api_key = get_option('mybloglog_api_key');
	$community_id = get_option('mybloglog_community_id');
	
	$mbl = new MyBlogLog($api_key,$community_id);
	$cache_dir = dirname(__FILE__) . "/cache";
	$cache_life = get_option('mybloglog_cache_life');
	$mbl->cache_dir=$cache_dir;
	if ($cache_life!==false)
		$mbl->cache_minutes = $cache_life;
	
	if (!$temp = $mbl->memberFindByScreenName($screen_name)) {
		$error = "There was an error communicating with the MyBlogLog API.  Please try again.";
	}
	else {
		$id = $temp['id'];
		if (!$user = $mbl->memberFindById($id)) {
			$error = "There was an error communicating with the MyBlogLog API.  Please try again.";
		}
		else {
			$member = new MBLMember($user);
		}
	}

	if (isset($error)) {
		echo "<p>$error</p>";
	}
	else {
?>
	
		<h2><?php echo $user['nickname']; ?></h2>
		<p class="mbl-icon"><a href="<?php echo $user['url'] . "?fs=" . $community_id; ?>"><img src="<?php echo $user['pict']; ?>" align="center" /></a></p>

		<p class="mbl-services"><?php echo $member->getServiceLinks(); ?></p>
		
		<?php if ($user['profile']['age']!=""): ?>
			<p class="mbl-age"><strong>Age</strong>:  <?php echo $user['profile']['age']; ?></p>
		<?php endif; ?>

		<?php if (trim($member->location)!=""): ?>
			<p class="mbl-location"><strong>Location</strong>: <?php echo $member->location; ?></p>
		<?php endif; ?>

		<?php if ($member->bio!=""): ?>
			<p class="mbl-bio"><?php echo $member->bio; ?></p>
		<?php endif; ?>
	
		<p><a href="<?php echo $user['url'] . "?fs=" . $community_id; ?>">Visit my MyBlogLog page</a></p>
	
		<?php
		if (isset($member->services['del.icio.us'])) {
			$delicious = new ServicesDelicious($member->services['del.icio.us']);
			echo $delicious->display();
		}
		?>
		
		<?php if ($user["sites_authored"]["site"]) : ?>
			<h3>My Sites</h3>
			<?php foreach ($user["sites_authored"]["site"] as $site): ?>
				<p class="mbl-sites center">
					<a href="<?php echo $site['site_url']; ?>"><img src="<?php echo $site['pict']; ?>" /></a><br />
					<a href="<?php echo $site['site_url']; ?>"><?php echo $site['name']; ?></a><br />
					visit <a href="<?php echo $site['url']; ?>">MyBlogLog community</a>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>
<?php
	}
?>
	<p class="mbl-nav">&#171; Return to <a href="<?php echo $base_url; ?>">member listing</a></p>
</div>

<?php	
	if (get_option('mybloglog_sidebar_order')!="before")
		get_sidebar();
	get_footer();
?>