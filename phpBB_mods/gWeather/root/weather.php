<?php
/**
* @package phpBB gWeather
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
$user->setup('');

include($phpbb_root_path . 'includes/classes/weather_location.' . $phpEx);

weather_location::input_location();

// Output page
page_header('Weather Testing');

$template->set_filenames(array(
	'body' => 'mods/weather/index_body.html')
);

page_footer();

?>