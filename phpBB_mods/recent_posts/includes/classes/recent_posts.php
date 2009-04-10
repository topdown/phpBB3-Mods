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
	 * @param object $p_limit Limit the number of posts to display (Int)
	 * @param object $show_author Show author of the post (Booleen)
	 * @param object $show_time Show time of post or not (Booleen)
	 * @param object $remove_re Remove the Re: from the titles (Booleen)
	 */
	public static function get_posts($p_limit, $show_author, $show_time, $remove_re)
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
			 * $remove_re remove the Re: from titles
			 * @var bool
			 */
			if($remove_re == true)
			{
				$message = ltrim($recent_data['post_subject'], 'Re:')."\n"; 	
			}
			//Get permissions before display
			if($auth->acl_get('f_read',$recent_data['forum_id']))
	        {
				$template->assign_block_vars('recent_postdata', array(
					'POST_AUTHOR'		=> get_username_string('full', $recent_data['poster_id'], $user->data['username'], $user->data['user_colour']),
					'S_AUTHOR'			=> $show_author,
					'S_TIME'			=> $show_time,
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