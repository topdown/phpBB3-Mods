<?php
/**
*
* @rss.php
* @version 0.0.1 Beta
* @package phpBB RSS
* @copyright (c) 2009 topdown, Webmasters United.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* rss class
* @package phpBB RSS
*/

class rss
{
	// Create our channel variables
	var $channel_title;
	var $channel_link;
	var $channel_desc;

	// Create our image variables
	var $image_title;
	var $image_link;
	var $image_url;
	var $image_width;
	var $image_height;
	
	/**
	 * @var date string
	 */
	var $timestamp;
	/**
	 * @var Builds the correct URL's for the Feed
	 * @return string
	 */
	var $board_url;

	/**
	 * Format the phpBB date to RSS standards
	 * @return date string
	 * @param object $timestamp
	 */
	function format_date($timestamp)
	{
		return date('D, d M Y H:i:s O', $timestamp);
	}
	
	/**
	 * Lets build the RSS Feed
	 * @return
	 */
	function get_rss($forum_array, $forum_array_type, $order, $limit)
	{
		/**
         * Set the needed globals
         */
		global $phpbb_root_path, $phpEx, $db, $template, $auth, $user;
		
		// Get time, use current time
		$build_date = mktime();
		$board_url = generate_board_url();
	
		$rss_value = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		$rss_value .= '<rss version="2.0">' . "\r\n";
		
		// Build the channel tag
		$rss_value .= "\t<channel>\r\n";
		$rss_value .= "\t\t<title>" . $this->channel_title . "</title>\r\n";
		$rss_value .= "\t\t<link>" . $this->channel_link . "</link>\r\n";
		$rss_value .= "\t\t<description>" . $this->channel_desc . "</description>\r\n";
		$rss_value .= "\t\t<language>en-us</language>\r\n";
		$rss_value .= "\t\t" . '<lastBuildDate>' . $this->format_date($build_date) . '</lastBuildDate>' ."\r\n";

		
		// Build the image tag
		$rss_value .= "\t\t<image>\r\n";
		$rss_value .= "\t\t\t<title>" . $this->image_title . "</title>\r\n";
		$rss_value .= "\t\t\t<url>" . $this->image_url . "</url>\r\n";
		$rss_value .= "\t\t\t<link>" . $this->image_link . "</link>\r\n";
	  	$rss_value .= "\t\t\t<width>" . $this->image_width . "</width>\r\n";
		$rss_value .= "\t\t\t<height>" . $this->image_height . "</height>\r\n";
		$rss_value .= "\t\t</image>\r\n";
		
		/**
		 * Request the data for the feed from the database
		 * @var array strings mixed
		 */		
		$sql_ary = array(
		    'SELECT'    	=> '(t.topic_id), (p.poster_id), (u.user_id), (t.forum_id), topic_title, username, user_colour, post_id, post_username, post_text, post_subject, post_time, bbcode_uid, bbcode_bitfield, enable_bbcode, enable_smilies, enable_magic_url, topic_replies, topic_views',
			'FROM'      	=> array(
		    POSTS_TABLE		=> 'p',
			TOPICS_TABLE	=> 't',
			USERS_TABLE		=> 'u',
		    ),
			'WHERE'			=> 'p.post_id = t.topic_first_post_id
				AND ' . $db->sql_in_set('t.forum_id', $forum_array, $db->sql_escape($forum_array_type)) . '
				AND u.user_id   =  p.poster_id',
			'ORDER_BY'		=> 'topic_time ' . $db->sql_escape($order),
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, (int)$limit);	
						
		while ($row = $db->sql_fetchrow($result))
		{			
			$t_id = $row['topic_id'];
			$f_id = $row['forum_id'];

			//Set BBC and Smiley options
			$row['bbcode_options'] = (($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);		
				
						     					   			
			$message = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);
   						     					   			
			$message = $this->clean_message($message);
			
			 //Get permissions before display
			if($auth->acl_get('f_read',$row['forum_id']))
	        {
				$rss_value .= "\t\t\t" . '<item>' ."\r\n";
				$rss_value .= "\t\t\t\t" . '<author>' . $row['username'] . '</author>'. "\r\n";
				$rss_value .= "\t\t\t\t" . '<pubDate>' . $this->format_date($row['post_time']) . '</pubDate>' ."\r\n";
				$rss_value .= "\t\t\t\t" . '<title>' . $row['topic_title'] . '</title>' . "\r\n";
				$rss_value .= "\t\t\t\t" . '<link>' . $board_url . '/viewtopic.' . $phpEx . '?f=' . $f_id . '&amp;t=' . $t_id . '</link>' . "\r\n";
				$rss_value .= "\t\t\t\t<description>" . $message . '&lt;br /&gt;&lt;br /&gt;Posted by: &lt;b&gt;'  . $row['username'] . '&lt;/b&gt;,  Replies: ' . $row['topic_replies'] . ', Views: ' . $row['topic_views'] .  "&lt;hr /&gt;</description>\r\n";
				$rss_value .= "\t\t\t</item>\r\n";
			}
		}
		
		$db->sql_freeresult($result);
				
		// Add the closing rss tags and return
		$rss_value .= "\t</channel>\r\n </rss>\r\n";
		return $rss_value; 
		
		// gzip compression
		if ($config['gzip_compress'])
		{
			if (@extension_loaded('zlib') && !headers_sent())
			{
				ob_start('ob_gzhandler');
			}
		}
		// Close the database and phpBB connections
		garbage_collection();
		exit_handler();
	}
	
	/**
	 * Clean the unwanted & unsafe garbage out of the text
	 * @return string
	 * @param object $message
	 */
	function clean_message ($message)
	{
		//Search and destroy :P
		//$search 	= array('&nbsp;', '<', '>', '&lt;span style="font-weight: bold"&gt;', '&lt;/span&gt;');
		//$replace 	= array(' ', '&lt;', '&gt;', '&lt;b&gt;', '&lt;/b&gt;');
		
		//Strip all HTML tags except the allowed tags in the list	
		$message 	= strip_tags($message, '<ul><li><dt><dd><dl><code><blockquote><span><br><img><p><a>');
		//Convert special characters to HTML entities (I think this maybe a better way then str_replace)
		$message = htmlspecialchars($message, ENT_QUOTES);
		
		//Clean it up
		//$message 	= str_replace($search, $replace, $message);
		
		// Special, remove the Select All link from the code blocks (Lets get rid of that broken java link)
		$message 	= str_replace('&lt;a href=&quot;#&quot; onclick=&quot;selectCode(this); return false;&quot;&gt;Select all&lt;/a&gt;', '&lt;br /&gt;', $message);
		
		return $message;
	}
}
?>