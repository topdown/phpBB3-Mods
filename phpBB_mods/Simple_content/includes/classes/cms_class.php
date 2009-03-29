<?php
/**
*
* @cms.php (CMS controler)
* @version $Id: cms.php beta 0.0.5 2009-03-17 09:23:12Z phpbbxpert $
* @package - Simple BB CMS
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
* CMS class
* @package Simple BB CMS
*/

class cms
{		
	/**
	 * Let get the topics needed for the CMS articles.
	 * @return 
	 * @param object $a_forum_id 		Sets the forum id to get the topics from
	 * @param object $limit				Sets the limit of articles for pagnition
	 * @param object $display_nav		Display article navigation true/false
	 * @param object $comments			Display a link to the topic for comments true/false, 
 	 * 									REMEMBER the forum must be public for comments.
	 * @param object $order				Order the articles by 'ASC'/'DESC'
	 * @param object $cms_title			Give your page atitle 'Home Page'
	 * 
	 * cms_class::get_cms($a_forum_id, $limit, $display_nav, $comments, $order, $cms_title);
	 * EXAMPLE 	cms_class::get_cms('2', '6', true, true, 'ASC', 'Home Page');
	 */
	public static function get_cms($a_forum_id, $limit, $display_nav, $comments, $order, $cms_title)
    {
		/**
         * Set the needed globals
         */
		global $phpbb_root_path, $phpEx, $db, $template, $user, $auth;
		
		/**
		 * @var string
		 * request needed vars
		 */
		$forum_id	= request_var('f', 0);
		$topic_id	= request_var('t', 0);

		/**
		 * @var Starting and limiting pagnition for the Articles
		 */
		$start	= request_var('start', 0);
		$limit	= request_var('limit', (int) $limit);
		
		/**
		 * @var string
		 * return pagination URL
		 */
		$pagination_url = append_sid($phpbb_root_path . '../index.' . $phpEx);
		
		/**
		 * Build SQL array for the Articles
		 * @var $a_forum_id as a function param set to your forum_id
		 */
		
		$sql_ary = array(
		    'SELECT'    	=> '(t.topic_id), (p.poster_id), (u.user_id), username, user_colour, post_id, post_username, post_text, post_subject, post_time, bbcode_uid, bbcode_bitfield, enable_bbcode, enable_smilies, enable_magic_url, topic_replies',
			'FROM'      	=> array(
		    POSTS_TABLE		=> 'p',
			TOPICS_TABLE	=> 't',
			USERS_TABLE		=> 'u',
		    ),
			'WHERE'			=> 'p.post_id = t.topic_first_post_id
				AND p.post_time = t.topic_time
				AND	p.forum_id 	= ' . (int)$a_forum_id . '
				AND u.user_id   =  p.poster_id',
			'ORDER_BY'		=> 'p.topic_id ' . $db->sql_escape($order),
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);
		
						
		while ($post_data = $db->sql_fetchrow($result))
		{
			$poster_id = $post_data['poster_id'];
			//Set BBC and Smiley options
			$post_data['bbcode_options'] = (($post_data['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) +
				(($post_data['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + 
				(($post_data['enable_magic_url']) ? OPTION_FLAG_LINKS : 0);
							     					   			
			//Who is the poster
			$poster_id = $post_data['poster_id'];
			//A little url cleaning
			$article	= request_var('#article_', 0);
			
			// assign the database results to the block_vars loop
			$template->assign_block_vars('post_data', array(
				'POST_AUTHOR_FULL'	=> get_username_string('full', $poster_id, $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
				'POST_TITLE'		=> $post_data['post_subject'],
				'ARTICLE_ID'		=> $post_data['topic_id'],
				'ARTICLE_LINK'      => append_sid("#article_" . $post_data['topic_id']),
				'ARTICLE_URL'       => append_sid("{$phpbb_root_path}../index.$phpEx","start=$start&amp;#article_" . $post_data['topic_id']),
				'TOTAL_COMMENTS'	=> $post_data['topic_replies'],
				'POST_TEXT'     	=> generate_text_for_display($post_data['post_text'], $post_data['bbcode_uid'], $post_data['bbcode_bitfield'], $post_data['bbcode_options']),
				'POST_DATE'			=> $user->format_date($post_data['post_time']),
				'U_COMMENTS'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$a_forum_id&amp;t={$post_data['topic_id']}"),
					
				/** No big permission deal here because they would need permissions to the forum first
				*  only admins should see these links
				*/
				'U_EDIT'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f=$a_forum_id&amp;p={$post_data['post_id']}"),
				'U_DELETE'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=delete&amp;f=$a_forum_id&amp;p={$post_data['post_id']}"),
			));      		
		}
		
		// free the result
		$db->sql_freeresult($result);
		
		/**
		 * Run the query again with a count to set the pagnition
		 */
		$sql_ary['SELECT'] = 'COUNT(p.post_subject) as total_articles';
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		
		// get the total articles, this is a single row, single field.
		$total_articles = $db->sql_fetchfield('total_articles');
		// free the result
		$db->sql_freeresult($result);
		
		// Assign index specific vars
		$template->assign_vars(array(
			'CMS_TITLE'			=> $cms_title,
			// S_CMS_PAGES is a simple controler for the pages, can be used for CMS specific blocks
			'S_CMS_PAGES'		=> true,
			'S_NAVIGATION'		=> $display_nav,
			'S_COMMENTS'		=> $comments,

			'PAGINATION'        => generate_pagination($pagination_url, $total_articles, $limit, $start, true),
		    'PAGE_NUMBER'       => on_page($total_articles, $limit, $start),
		    'TOTAL_ARTICLES'    => ($total_articles == 1) ? $total_articles['total_articles'] : sprintf($total_articles['total_articles'], $total_articles),
		));
    }		
}
?>