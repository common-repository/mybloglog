<?php
/*
Plugin Name: MyBlogLog
Plugin URI: http://sitening.com/mybloglog/
Description: Adds MyBlogLog widgets and member pages to your blog.
Author: Jason Tan
Version: 0.92
Author URI: http://sitening.com/
*/

add_action('admin_menu', 'mybloglog_config_page');
add_action('wp_footer', 'mybloglog_tracking');
add_action('template_redirect', 'mybloglog_member_template');
add_action('init', 'widget_mybloglog_plugin_register');

function mybloglog_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', __('MyBlogLog Configuration'), __('MyBlogLog Configuration'), 'manage_options', 'mybloglog-config', 'mybloglog_conf');	
}


function mybloglog_conf() {

	if(isset($_POST['btnSave']))
	{
		if (is_numeric($_POST['avatar_size'])&&$_POST['avatar_size']>0)
			update_option('mybloglog_avatar_size', $_POST['avatar_size']);
		if (is_numeric($_POST['cache_life'])&&$_POST['cache_life']>=0)
			update_option('mybloglog_cache_life', $_POST['cache_life']);
		update_option('mybloglog_delimiter', $_POST['delimiter']);
		update_option('mybloglog_api_key', trim($_POST['api_key']));
		update_option('mybloglog_community_id', trim($_POST['community_id']));
		update_option('mybloglog_tracking_code', stripslashes(trim($_POST['tracking_code'])));
		update_option('mybloglog_sidebar_order', $_POST['sidebar_order']);
		
        if (trim($_POST['base_url'])=="") {
            $base_url = "";
        }
		else {
			$base_url = trim($_POST['base_url']);
	        if (!ereg('.*/$', $base_url))
				$base_url .= '/';
		}
		update_option('mybloglog_base_url', $base_url);
		$msg = "<span style='background-color:green; color:white; padding:5px;'>Settings saved!</span>";
	}

	$cache_dir = dirname(__FILE__) . "/cache";
	$cache_life = get_option('mybloglog_cache_life');
	$api_key = get_option('mybloglog_api_key');
	$community_id = get_option('mybloglog_community_id');
	$avatar_size = get_option('mybloglog_avatar_size');
	$delimiter = get_option('mybloglog_delimiter');
	$base_url = get_option('mybloglog_base_url');
	$tracking_code = get_option('mybloglog_tracking_code');
	$sidebar_order = get_option('mybloglog_sidebar_order');
	if ($base_url===false)
		$base_url = "members/";
	if (!$avatar_size>0)
		$avatar_size = 30;
	if ($cache_life===false)
		$cache_life = 60;
	
	echo "<div class='wrap'>";
	echo "	<h2>MyBlogLog Configuration</h2>";
	echo "	<p><a href=\"../wp-content/plugins/mybloglog/readme.html\" target=\"mblpluginhelp\">Get help on the MyBlogLog plugin</a></p>";
	echo "	<form action='' method='post' id='mybloglog-conf'>";
	echo "		$msg";
	echo "		<h3>MyBlogLog Settings <a href=\"../wp-content/plugins/mybloglog/readme.html#settings\" target=\"mblpluginhelp\">?</a></h3>";
	echo "		<p><label for='api_key'>Your MyBlogLog Application ID:</label></p>";
	echo "		<p><input type='text' name='api_key' value='$api_key' id='api_key' size='45' /></p>";
	echo "		<p><label for='community_id'>Your MyBlogLog Community ID:</label></p>";
	echo "		<p><input type='text' name='community_id' value='$community_id' id='community_id' size='45' /></p>";
	echo "		<p><label for='tracking_code'>Tracking Script Code:</label></p>";
	echo "		<p><textarea name='tracking_code'id='tracking_code' cols='100'>$tracking_code</textarea><br />";
	echo "		<em>Login to MyBlogLog and click on \"Get Widgets\".  Copy code listed under \"Stats Tracking Script\" and paste it in the box above.</em><br />You should notice a portion of the tracking script that reads \"jsserv.php?mblID=<strong>[long string of numbers]</strong>\".  The string of numbers is your MyBlogLog Community ID.</p>";
	echo "		<h3>Member/Reader List Display Settings <a href=\"../wp-content/plugins/mybloglog/readme.html#list\" target=\"mblpluginhelp\">?</a></h3>";
	echo "		<p><label for='delimiter'>List delimiter: </label>";
	if ($delimiter=="p")
		echo "		<select name='delimiter' id='delimiter'><option value='li'>&lt;li&gt;</option><option value='p' selected>&lt;p&gt;</option></select></p>";
	else
		echo "		<select name='delimiter' id='delimiter'><option value='li' selected>&lt;li&gt;</option><option value='p'>&lt;p&gt;</option></select></p>";
	echo "		<p><label for='avatar_size'>Avatar size: </label><input type='text' name='avatar_size' value='$avatar_size' id='avatar_size' size='5' /> pixels</p>";
	echo "		<h3>Member Pages Settings <a href=\"../wp-content/plugins/mybloglog/readme.html#pages\" target=\"mblpluginhelp\">?</a></h3>";
	echo "		<p>If you would like to have a page for each of your MyBlogLog community members, enter the URL where you would like these member pages to be.  The default path is <strong>members/</strong>.  Make sure there are no naming conflicts with other sections of your blog.</p>";
	echo "		<p>" . get_bloginfo('wpurl') . "/ <input type='text' name='base_url' value='$base_url' id='base_url' size='45' /><br /><em>Leaving this blank will disable the member pages feature.</em></p>";
	echo "		<p><label for='sidebar_order'>The sidebar comes </label>";
	if ($sidebar_order=="before")
		echo "		<select name='sidebar_order' id='sidebar_order'><option value='before' selected>before</option><option value='after'>after</option></select>";
	else
		echo "		<select name='sidebar_order' id='sidebar_order'><option value='before'>before</option><option value='after' selected>after</option></select>";
	echo "		the main content in my theme</p>";
	echo "		<h3>MyBlogLog Cache <a href=\"../wp-content/plugins/mybloglog/readme.html#cache\" target=\"mblpluginhelp\">?</a></h3>";
	if (file_exists($cache_dir) && is_dir($cache_dir) && is_writable($cache_dir))
		echo "	<p>Cache directory <strong>is writable</strong>.</p>";
	else {
		echo "	<p><span style='background-color:red; color:white; padding:5px;'>Cache directory <strong>is not writable</strong>.</span></p>";
		echo "	<p>Please modify the directory <strong>wp-content/plugins/mybloglog/cache</strong> so that it can be written to by your web server.</p>";
	}
	echo "		<p><label for='cache_life'>Cache life: </label><input type='text' name='cache_life' value='$cache_life' id='cache_life' size='5' /> minutes</p>";
	echo "		<p align='center'><input type='submit' name='btnSave' value='Save Settings' id='btnSave' /></p>";
	echo "	</form>";
	echo "	<h2>Example Usage</h2>";
	echo "	<h3>Member pages and listing</h3>";
	echo "	<p>Member pages and listing will be automatically created as long as a path is specified under the <strong>Member Pages Settings</strong>.</p>";
	echo "	<p>To display a link to your members listing page, insert the following code where you want the link to appear:</p>";
	echo "	<pre>&lt;?php mybloglog_members_link(); ?&gt;</pre>";
	echo "	<h3>Sidebar modules</h3>";
	echo "	<p>If your WordPress installation and theme supports widgets, you can add the MyBlogLog Members and Readers widgets to your sidebar.  <a href=\"widgets.php\">Configure your widgets here</a>.</p>";
	echo "	<p>If you're not using widgets, you can manually insert code into your theme's sidebar.</p>";
	echo "	<p>To display the top members in the sidebar, insert the following code into <strong>sidebar.php</strong> in your themes folder:</p>";
	echo "	<pre>&lt;h2&gt;Community Members&lt;/h2&gt;\n&lt;?php mybloglog_members(); ?&gt;\n&lt;?php mybloglog_members_link(\"view all\"); ?&gt;</pre>";
	echo "	<p>To display the top 8 members in a list, make sure the list delimiter option is set to <strong>&lt;li&gt;</strong> and insert the following code:</p>";
	echo "	<pre>&lt;h2&gt;Community Members&lt;/h2&gt;\n&lt;ul&gt;\n&lt;?php mybloglog_members(8); ?&gt;\n&lt;/ul&gt;</pre>";
	echo "	<p>To display the 5 latest visitors to your site (similar to the MyBlogLog javascript widget) insert the following code :</p>";
	echo "	<pre>&lt;h2&gt;Recent Visitors&lt;/h2&gt;\n&lt;?php mybloglog_readers(); ?&gt;</pre>";
	echo "</div>";
}


function mybloglog_members($num = 5, $widget = false) {
	require_once("class.mybloglog.php");
	$api_key = get_option('mybloglog_api_key');
	$community_id = get_option('mybloglog_community_id');
	$avatar_size = get_option('mybloglog_avatar_size');
	if ($widget)
		$delimiter = "p";
	else
		$delimiter = get_option('mybloglog_delimiter');
	if (!$avatar_size)
		$avatar_size = 30;
	$base_url = get_option('mybloglog_base_url');
	if ($base_url===false)
		$base_url = "members/";
	if ($base_url) {
		$blog_url = parse_url(get_bloginfo('wpurl'));
		$base_url = $blog_url['path'] . '/' . $base_url;
	}
	
	$mbl = new MyBlogLog($api_key,$community_id);
	$cache_dir = dirname(__FILE__) . "/cache";
	$cache_life = get_option('mybloglog_cache_life');
	$mbl->cache_dir=$cache_dir;
	if ($cache_life!==false)
		$mbl->cache_minutes = $cache_life;
	$results = $mbl->communityMembersList(null,$num);
	if ($results===false)
		echo "<$delimiter>There was an error communicating with the MyBlogLog API.  Please make sure you have correctly configured the MyBlogLog plugin.</$delimiter>";
	else {
		foreach ($results as $result) {
			$user = $mbl->memberFindById($result['id']);
			if (is_array($user)) {
				if ($base_url)
					echo "<$delimiter><a href='$base_url$user[screen_name]'><img src='$user[pict]' align='middle' width='$avatar_size' height='$avatar_size' /></a> <a href='$base_url$user[screen_name]'>$user[nickname]</a></$delimiter>";
				else
					echo "<$delimiter><a href='$user[url]?fs=$community_id'><img src='$user[pict]' align='middle' width='$avatar_size' height='$avatar_size' /></a> <a href='$user[url]?fs=$community_id'>$user[nickname]</a></$delimiter>";
			}
		}
	}
}


function mybloglog_readers($num = 5, $widget = false) {
	require_once("class.mybloglog.php");
	$api_key = get_option('mybloglog_api_key');
	$community_id = get_option('mybloglog_community_id');
	$avatar_size = get_option('mybloglog_avatar_size');
	if ($widget)
		$delimiter = "p";
	else
		$delimiter = get_option('mybloglog_delimiter');
	if (!$avatar_size)
		$avatar_size = 30;
	
	$mbl = new MyBlogLog($api_key,$community_id);
	//don't cache readers list - we want the current reader to show up on the list
	$results = $mbl->communityReadersList(null,$num);
	if ($results===false)
		echo "<$delimiter>There was an error communicating with the MyBlogLog API.  Please make sure you have correctly configured the MyBlogLog plugin.</$delimiter>";
	else {
		$cache_dir = dirname(__FILE__) . "/cache";
		$cache_life = get_option('mybloglog_cache_life');
		$mbl->cache_dir=$cache_dir;
		if ($cache_life!==false)
			$mbl->cache_minutes = $cache_life;
		foreach ($results as $result) {
			$user = $mbl->memberFindById($result['id']);
			if (is_array($user))
				echo "<$delimiter><a href='$user[url]?fs=$community_id'><img src='$user[pict]' align='middle' width='$avatar_size' height='$avatar_size' /></a> <a href='$user[url]?fs=$community_id'>$user[nickname]</a></$delimiter>";
		}
	}
}

function mybloglog_members_link($text = "View all members") {
	$base_url = get_option('mybloglog_base_url');
	if ($base_url===false)
		$base_url = "members/";
	if ($base_url) {
		$blog_url = parse_url(get_bloginfo('wpurl'));
		$base_url = $blog_url['path'] . '/' . $base_url;
		echo "<a href=\"$base_url\">$text</a>";
	}
}

function mybloglog_tracking() {
	$tracking_code = get_option('mybloglog_tracking_code');
	echo "$tracking_code\n";
}

function mybloglog_member_template () {
	$base_url = get_option('mybloglog_base_url');
	if ($base_url===false)
		$base_url = "members/";
	if ($base_url) {
		$blog_url = parse_url(get_bloginfo('wpurl'));
		$base_url = $blog_url['path'] . '/' . $base_url;
		
		if ($_SERVER['REQUEST_URI']==$base_url ||
			$_SERVER['REQUEST_URI']."/"==$base_url || 
			substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "?"))==$base_url) {
		 		include(dirname(__FILE__).'/members.php');
				exit;
		}		
	    if (strpos($_SERVER['REQUEST_URI'], $base_url) === 0) {
	 		include(dirname(__FILE__).'/member.php');
			exit;
		}
	}
}

function widget_mybloglog_plugin_register() {
	if ( function_exists('register_sidebar_widget') ) :
	
		function widget_mybloglog_plugin_members($args) {
			extract($args);
			echo $before_widget;
			echo $before_title . "Community Members" . $after_title;
			mybloglog_members(5,true);
			mybloglog_members_link("view all");
			echo $after_widget;
		}
	
		function widget_mybloglog_plugin_readers($args) {
			extract($args);
			echo $before_widget;
			echo $before_title . "Recent Visitors" . $after_title;
			mybloglog_readers(5,true);
			echo $after_widget;
		}

		register_sidebar_widget('MyBlogLog Members', 'widget_mybloglog_plugin_members');
		register_sidebar_widget('MyBlogLog Readers', 'widget_mybloglog_plugin_readers');
		
	endif;
}

?>