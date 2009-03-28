<?php
/**
*
* @rss_feed.php 
* @version 0.0.1 Beta
* @package - RSS output
* @copyright (c) 2008 topdown, Webmasters United.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path    = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './phpBB/';    // Replace with your own root path
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin($config['board_disable'] = '0');
$auth->acl($user->data);
$user->setup('');

include_once($phpbb_root_path .  '../includes/classes/rss_class.php');

// We put true to not append the phpBB path to the URL, this allows this script to be anywhere under your.com/
$board_url = generate_board_url(true);

// Instantiate the rss class
$my_rss = new rss;

// Set your feed path here, (Path to this script)
$my_rss->channel_link 	= $board_url . '/rss.php';

// These will use the phpBB Sitename and Description
$my_rss->channel_title 	= strip_tags($config['sitename'] . ' RSS Feed');
$my_rss->channel_desc 	= strip_tags($config['site_desc']);

$my_rss->image_title 	= 'Home Page';
$my_rss->image_link 	= $board_url;
$my_rss->image_url 		= 'images/xml.png';
$my_rss->image_width 	= '36';
$my_rss->image_height 	= '14';

// Get the rss data include or exclude these forums (Global Announcements ignored)
$forum_array 	= array('2', '7');
/**
 * Array type, included forums = false or excluded = true, options (true / false)
 * @var booleen
 */
$forum_array_type 	= false;
$order				= 'DESC';
$limit				= '20';
$rss_data = $my_rss->get_rss($forum_array, $forum_array_type, $order, $limit);

// Output the generated rss XML
echo $rss_data;
