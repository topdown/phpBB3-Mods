<?php
/**
*
* @package phpBB3 Recent Topics Class
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
 * Grabs recent topics with and forum id array exception
 */
class recent_topics
{
	/**
	 * Get the recently posted topics ordered by last post time 
	 * (if a reply ocoured, it jumps to the top of the list)
	 * @return 
	 * @param object $t_limit limit the number of topics
	 * @param object $sql_in array to exclude forum ids from the query
	 */
	public static function get_topics($t_limit, $sql_in)
	{
		/**
         * Set the needed globals
         */
		global $phpbb_root_path, $phpEx, $db, $template, $auth, $user;

		/**
		* @var (Int)
		* request vars for topic and forum ids to keep them clean
		*/
		$t_id = request_var('t', 0);
		$f_id = request_var('f', 0);
		
		/**
		 * Select all recent topics
		 */
		$sql = 'SELECT topic_type, topic_id, forum_id, topic_last_post_time, topic_views, topic_title, topic_replies, topic_time, topic_poster, topic_first_poster_name, topic_first_poster_colour, topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('forum_id', $sql_in, true) . '
				AND topic_status = 0
				ORDER BY topic_last_post_time DESC
				LIMIT ' . (int)$t_limit;
		$result = $db->sql_query($sql);
							
		while ($recent_data = $db->sql_fetchrow($result))
		{			
			 //Get permissions before display
			if($auth->acl_get('f_read',$recent_data['forum_id']))
	        {
				$template->assign_block_vars('recent_data', array(
					'TOTAL_POSTS'			=> $recent_data['topic_replies'],
					'S_NEW_POSTER'          => $recent_data['topic_poster'] != $recent_data['topic_last_poster_id'],
					'TOPIC_VIEWS'			=> $recent_data['topic_views'],
					'REPLY_AUTHOR_FULL'		=> get_username_string('full', $recent_data['topic_last_poster_id'], $recent_data['topic_last_poster_name'], $recent_data['topic_last_poster_colour']),
					'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $recent_data['topic_poster'], $recent_data['topic_first_poster_name'], $recent_data['topic_first_poster_colour']),
					'TOPIC_TIME'			=> $user->format_date($recent_data['topic_last_post_time']),
					'TOPIC_TITLE'			=> $recent_data['topic_title'],
					'U_RECENT_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $f_id . '&amp;' . 't=' . $t_id),
				));
			}
		}
		
		$db->sql_freeresult($result);
	}
}
?>