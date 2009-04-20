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

		// Request the var to keep it clean
		$p_id = request_var('p', 0);
		
		/**
		 * Select all recent posts
		 */
		$sql_ary = array(
		    'SELECT'    	=> '(p.post_id), (t.topic_last_post_id), (p.forum_id), poster_id, post_username, post_subject, post_time,  topic_last_poster_id, topic_last_poster_colour,  topic_last_poster_name',
			'FROM'      	=> array(
		    POSTS_TABLE		=> 'p',
			TOPICS_TABLE	=> 't',
		    ),
			'WHERE'			=> 'p.post_id = t.topic_last_post_id
				AND post_approved = 1',
			'ORDER_BY'		=> 'p.post_time DESC',
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, (int)$p_limit);					
		
		while ($recent_data = $db->sql_fetchrow($result))
		{
			$p_id 		= $recent_data['post_id'];
			$message 	= $recent_data['post_subject'];
			/**
			 * $remove_re remove the Re: from titles
			 * @var bool
			 */
			if($remove_re == true)
			{
				$message = ltrim($recent_data['post_subject'], 'Re:')."\n"; 	
			}
			
			$poster_id = $recent_data['poster_id'];
			
			//Get permissions before display
			if($auth->acl_get('f_read',$recent_data['forum_id']))
	        {
				$template->assign_block_vars('recent_postdata', array(
					'POST_AUTHOR'	=> get_username_string('full', $recent_data['topic_last_poster_id'], $recent_data['topic_last_poster_name'], $recent_data['topic_last_poster_colour']),
					'S_AUTHOR'		=> $show_author,
					'S_TIME'		=> $show_time,
					'POST_TIME'		=> $user->format_date($recent_data['post_time']),
					'POST_TITLE'	=> $message,
					'U_RECENT_POST'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $p_id .'#p' . $p_id),
				));
			}
		}	
		$db->sql_freeresult($result);
	}
}
?>