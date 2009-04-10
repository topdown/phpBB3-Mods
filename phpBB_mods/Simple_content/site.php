<?php
 /**
*
* @package Simple_content (CMS Sample)
* @version $Id$
* @author topdown
* @copyright (c) 2009 topdown, Webmasters United.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('common'));

include_once($phpbb_root_path . 'includes/classes/cms_class.' . $phpEx);
/**
 * Let get the topics needed for the CMS articles.
 * @return 
 * @param object $a_forum_id 		Sets the forum id to get the topics from
 * @param object $limit				Sets the limit of articles for pagnition
 * @param object $display_nav		Display article navigation true/false
 * @param object $comments			Display a link to the topic for comments true/false, 
 * 									REMEMBER the forum must be public for comments.
 * @param object $order				Order the articles by 'ASC'/'DESC'
 * @param object $cms_title			Give your page a title 'Home Page'
 */
$page = request_var('page', '');
//We can use a switch here to have multiple pages with different content and one controller file.
switch ($page)
{
    case 'articles':
        cms::get_cms('2', '4', false, true, 'ASC', 'Articles');
    break;
    
    case 'about':
        cms::get_cms('2', '1', true, true, 'ASC', 'About');
    break;

    case 'home':
    default:
        cms::get_cms('2', '4', true, true, 'ASC', 'Home');
    break;
}

// Output page
page_header($page);

$template->set_filenames(array(
    'body' => 'index_body.html')
);
// Set the links in the breadcrumbs
$template->assign_block_vars('navlinks', array(
	'FORUM_NAME'   =>  $page,
	'U_VIEW_FORUM'   => append_sid($phpbb_root_path .  'site.' . $phpEx, 'page=' . $page)
));

$template->set_filenames(array(
	'body' => 'site/index_body.html')
);

page_footer();
?>