<?php
	function mybloglog_members_title() {
		echo "Members";
	}
	add_action('wp_title', 'mybloglog_members_title');
	get_header();
?>
<!-- if your theme requires markup between the header and sidebar/content, insert it here -->

<?php
	if (get_option('mybloglog_sidebar_order')=="before")
		get_sidebar();
?>

<div id="mybloglog-members" class="narrowcolumn">

<?php
	require_once("class.mybloglog.php");
	require_once("class.mblmember.php");
	
	$api_key = get_option('mybloglog_api_key');
	$community_id = get_option('mybloglog_community_id');
	
	$mbl = new MyBlogLog($api_key,$community_id);
	$cache_dir = dirname(__FILE__) . "/cache";
	$cache_life = get_option('mybloglog_cache_life');
	$mbl->cache_dir=$cache_dir;
	if ($cache_life!==false)
		$mbl->cache_minutes = $cache_life;

	if (isset($_GET['page'])&&intval($_GET['page'])>1)
		$page = intval($_GET['page']);
	else
		$page = 1;

	$perpage = 10;
	
	$results = $mbl->communityMembersList(null,$perpage,($page-1)*$perpage);
	if ($results===false)
		$error = "There was an error communicating with the MyBlogLog API.  Please try again.";
	$max = $mbl->total;
	
	if (isset($error)) {
		echo "<p>$error</p>";
	}
	else {
?>
		<h2>Members List</h2>
		<p>There are <?php echo $max; ?> total members.<br />Currently displaying members <?php echo ($page-1)*10+1; ?> - <?php echo min($page*10,$max); ?></p>
		<p class="mbl-nav">
			<?php if ($page > 1): ?>
			<a href="<?php echo $base_url . "?page=" . intval($page-1); ?>">&#171; Previous</a>&nbsp; &nbsp; 
			<?php endif; ?>
			
			<?php if ($max>$perpage*$page): ?>
			<a href="<?php echo $base_url . "?page=" . intval($page+1); ?>">Next &#187;</a>
			<?php endif; ?>
		</p>
		
		<?php foreach ($results as $result): ?>
		<?php $user = $mbl->memberFindById($result['id']); ?>
		<p class="mbl-listuser">
			<a href="<?php echo $base_url . $user['screen_name']; ?>/"><img src="<?php echo $user['pict']; ?>" align="middle" /></a>
			<a href="<?php echo $base_url . $user['screen_name']; ?>/"><?php echo $user['nickname']; ?></a>
		</p>
		<?php endforeach; ?>
		<p class="mbl-nav">
			<?php if ($page > 1): ?>
			<a href="<?php echo $base_url . "?page=" . intval($page-1); ?>">&#171; Previous</a>&nbsp; &nbsp; 
			<?php endif; ?>
			
			<?php if ($max>$perpage*$page): ?>
			<a href="<?php echo $base_url . "?page=" . intval($page+1); ?>">Next &#187;</a>
			<?php endif; ?>
		</p>

<?php
	}
?>

</div>

<?php
	if (get_option('mybloglog_sidebar_order')!="before")
		get_sidebar();
	get_footer();
?>