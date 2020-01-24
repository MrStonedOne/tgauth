<?php
		
        
        define('FROM_MEDIAWIKI', true); //to hook into the phpbbSSO wiki extension
		
		//stuff phpbb wants defined.
		$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
		define('IN_PHPBB', true);
        $phpEx = substr(strrchr(__FILE__, '.'), 1);
        
		include_once($phpbb_root_path.'common.'.$phpEx); //we include the phpbb frame work
		
		$user->session_begin(); //now we let phpbb do all the fancy work of figuring out who the fuck this are.
		$userid = (int)$user->data['user_id'];
		$usertype = $user->data['user_type'];

		if($userid <= 1 || $usertype == 1 || $usertype == 2) {
			header("location: ucp.php?mode=login&redirect=".urlencode("linkbyondaccount.php?".$_SERVER["QUERY_STRING"]));
			//print_r($user);
			die();
		}
		
		if (isset($_GET['token']) && strlen($_GET['token']) == 128) {
			$token = $_GET['token'];
			$sql = "SELECT `key` FROM `byond_oauth_tokens` WHERE token = '".$db->sql_escape($token)."' AND timestamp > DATE_SUB(CURDATE(),INTERVAL 30 MINUTE)";
			$result = $db->sql_query($sql);
			$key = $db->sql_fetchfield('key');
			$db->sql_freeresult($result);
			
			
			if (!$key) {
				print("Invalid token or unknown error linking byond account<br><a href='linkbyondaccount.php?redirect=".htmlspecialchars(urlencode($redirect))."'>Retry?</a>");
				die();
			}
			
			$sql = "DELETE FROM `byond_oauth_tokens` WHERE token = '".$db->sql_escape($token)."' OR timestamp < DATE_SUB(CURDATE(),INTERVAL 5 MINUTE)";
			$db->sql_freeresult($db->sql_query($sql));
			
			$bannedusernames = array();
			
			$sql = "SELECT u.username AS username FROM `phpbb_banlist` AS b LEFT JOIN `phpbb_profile_fields_data` AS f ON (b.ban_userid = f.user_id) LEFT JOIN `phpbb_users` AS u on (u.user_id = b.ban_userid) WHERE b.ban_userid > 0 AND f.pf_byond_username IS NOT NULL AND ban_exclude <= 0 AND (ban_end = 0 OR ban_end > UNIX_TIMESTAMP()) AND f.pf_byond_username = '".$db->sql_escape($key)."'";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
				$bannedusernames[] = $row['username'];
			
			if (count($bannedusernames) > 0) {
				print("You can not link this byond account while it is banned on another forum account.<br>");
				print("The following forum accounts are registered to this byond account and forum banned:<br>");
				foreach ($bannedusernames as $bannedusername)
					print($bannedusername."<br>");
				die();
			}
			
			$sql = "INSERT INTO phpbb_profile_fields_data (user_id,pf_byond_username) VALUES (".$userid.", '".$db->sql_escape($key)."') ON DUPLICATE KEY UPDATE pf_byond_username='".$db->sql_escape($key)."'";
			$db->sql_freeresult($db->sql_query($sql));
			
			$sql = "INSERT INTO phpbb_user_group (group_id,user_id,user_pending) VALUES (11, ".$userid.", 0) ON DUPLICATE KEY UPDATE user_pending=0";
			$db->sql_freeresult($db->sql_query($sql));
			
			$auth->acl_clear_prefetch($userid);
			
			$redirect = "memberlist.php?mode=viewprofile&u=".$userid;
			header("location: ".$redirect);
			die();
		}
		if (isset($_GET['go'])) {
			header("location: https://secure.byond.com/login.cgi?login=1;noscript=1;url=".urlencode("http://www.byond.com/play/byondoauth.tgstation13.org:31337"));
			die();
		}
		print(<<<EOD
<h1>Validate/Link Byond Account</h1>
<h2>Method 1: Game Server Verb <font color="green">(Preferred)</font></h2>
	Simply connect to any of the normal /tg/ game servers and press the "Link forum account" button in the ooc tab (or type link-forum-account in the input bar), then login to your forum account.
	<br>
	You will know this worked correctly if it opens a new window with your user profile page on the forums, with your byond info displayed.
	<br>
	<ul>
		<li><a href="byond://bagil.tgstation13.org:2337">Bagil</a></li>
		<li><a href="byond://sybil.tgstation13.org:1337">Sybil</a></li>
		<li><a href="byond://terry.tgstation13.org:3337">Terry</a></li>
		<li><a href="byond://tgmc.tgstation13.org:5337">TGMC</a></li>
		<li><a href="byond://138.201.56.145:4337">Event hall</a></li>
	</ul>
<h2>Method 2: Special BYOND Server</h2>
	Join a dedicated byond server for account linking. This still requires you have the game client installed and logged in.<br>
	This works even if you are banned from /tg/'s main servers.<br>
	<a href="byond://byondoauth.tgstation13.org:31337">Connect to the byond account linking server using the byond client.</a>
<h2>Method 3: Browser/Webclient <font color="red">(Beta/Buggy)</font></h2>
Validate your byond account by connecting to a byond webclient hosted by this server.<br/>
Before you can connect you will need to log in to your byond account, this will done at byond's website and we can not see your username and password.<br/>
Byond will show you an ad before connecting you unless you are a byond member and disabled them<br/>
This has been known to not work for adblock users, you may have to disable it and watch an ad.<br/>
After you do this, you will be taken back to the page you came here from or to the site home page<br/>
Ready?<br/>
<a href='?go=1&{${htmlspecialchars($_SERVER['QUERY_STRING'])}}'>Use the buggy Webclient to validate my byond account to my forum account</a>
<br/>
<p/>
&nbsp;<br/>
Webclient Error trouble shooting:<br/>
<ul>
    <li>'Invalid token or unknown error linking byond account'</li>
    <ul>
        <li>
            Try again or bug MrStonedOne via <a href='ucp.php?i=pm&mode=compose&u=2'>forum pm</a>
            to restart the webclient server
        </li>
    </ul>
    <br/>
    <li>Byond says the game is not currently available</li>
    <ul>
        <li>
            Bug MrStonedOne via <a href='ucp.php?i=pm&mode=compose&u=2'>forum pm</a>
            to restart the webclient server
        </li>
    </ul>
    <br/>
    <li>It just sits at connected to world, downloading client</li>
    <ul>
        <li>Stop using Internet Explorer dumbass</li>
        <li>Other wise, Hit control+f5 on that page to get it to clear the cache and reload.</li>
    </ul>
    <br/>
    <li>It says connecting account and then it displays an error</li>
    <ul>
        <li>Breaking change in chrome (and soon to be other browsers), Bitch here: https://github.com/WICG/interventions/issues/16 and/or disable chrome://flags/#enable-framebusting-needs-sameorigin-or-usergesture</li>
    </ul>
    <br/>
</ul>

EOD
);
		die();
?>
