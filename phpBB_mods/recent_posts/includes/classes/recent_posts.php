<?php
/**
*
* @package phpBB3 Recent Posts Class
* @version $Id$
* @author topdown
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
 * Grabs recent posts
 */
class recent_posts
{
	/**
	 * Get all of the recent posts from the database
	 * @return mixed
	 * @param object $p_limit # of posts to show
	 */
	public static function get_posts($p_limit)
	{
		/**
         * Set the needed globals
         */
		global $phpbb_root_path, $phpEx, $db, $template, $auth, $user;
	
		/**
		 * Select all recent posts
		 */
		$sql = 'SELECT post_id, forum_id, post_subject, poster_id, post_username, post_time
				FROM ' . POSTS_TABLE . '
				WHERE post_approved = 1
				ORDER BY post_time DESC
				LIMIT ' . (int)$p_limit;
		$result = $db->sql_query($sql);
					
		while ($recent_data = $db->sql_fetchrow($result))
		{			
			// Request the var to keep it clean
			$p_id = request_var('p', $recent_data['post_id']);
			
			$message = $recent_data['post_subject'];
			/**
			 * Un-comment the following line to remove the Re:
			 */
			//$message = ltrim($recent_data['post_subject'], 'Re:')."\n"; 	
			//Get permissions before display
			if($auth->acl_get('f_read',$recent_data['forum_id']))
	        {
				$template->assign_block_vars('recent_data', array(
					'POST_AUTHOR'		=> get_username_string('full', $recent_data['poster_id'], $user->data['username'], $user->data['user_colour']),
					
					'POST_TIME'			=> $user->format_date($recent_data['post_time']),
					'POST_TITLE'		=> $message,
					'U_RECENT_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $p_id .'#p' . $p_id),
				));
			}
		}	
		$db->sql_freeresult($result);
	}
}
?>