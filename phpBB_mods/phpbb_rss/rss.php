<?php
/**
*
* @rss_feed.php 
* @version: $Id$
* @package - RSS output
* @copyright (c) 2008 topdown, Webmasters United.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path    = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';    // Replace with your phpBB path
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('');

include_once($phpbb_root_path .  'includes/classes/rss_class.php');

// We put true to not append the phpBB path to the URL, this allows this script to be anywhere under your.com/
$board_url = generate_board_url();

// Instantiate the rss class
$my_rss = new rss;

// Set your feed path here, (Path to this script)
$my_rss->channel_link 	= $board_url . '/rss.php';

// These will use the phpBB Sitename and Description
$my_rss->channel_title 	= strip_tags($config['sitename'] . ' RSS Feed');
$my_rss->channel_desc 	= strip_tags($config['site_desc']);

$my_rss->image_title 	= strip_tags($config['sitename'] . ' RSS Feed');
$my_rss->image_link 	= $board_url . '/rss.php';
$my_rss->image_url 		= $board_url . '/images/xml.png';
$my_rss->image_width 	= '36';
$my_rss->image_height 	= '14';

// Get the rss data include or exclude these forums (Global Announcements ignored)
$forum_array 	= array('0');
/**
 * Array type, included forums = false and excluded = true, options (true / false)
 * @var booleen
 */
$forum_array_type 	= true;
/**
 * $order_by options , topic_time or topic_last_post_time
 * 		topic_last_post_time = the last time there was a post made.
 * 		topic_time = the actual time the topic was made
 * @var object string
 */
$order_by			= 'topic_last_post_time';
/**
 * $limit the number of topics
 * @var (int)
 */
$limit				= '10';
$rss_data = $my_rss->get_rss($forum_array, $forum_array_type, $order_by, $limit);

// Output the generated rss XML
echo $rss_data;
