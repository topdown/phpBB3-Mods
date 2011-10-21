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
if (!defined('IN_PHPBB'))
{
	exit;
}

include($phpbb_root_path . 'includes/classes/build_weather.' . $phpEx);

/**
 * Output the weather for the current user
 */
class weather_location extends build_weather {
   
   /**
    * We will allow the user to input a location, but it will be clean and must be valid
    * @return mixed string
    */
	public static function input_location()
	{
		global $user, $auth, $template, $phpbb_root_path, $phpEx;
		
		$submit		= (!empty($_POST['submit'])) ? true : false;
		$error 		= $data = array();
		$s_hidden_fields = '';
				
		/**
		 * We will select a random location and return it
		 * @return string mixed $location
		 */
		$location 	= self::random_location();
		/**
		 * We are cleaning any unwanted data out of the string
		 * @var string mixed
		 */		
		$location 	= self::clean_location($location);
		
		//If the location is empty or invalid, lets let the user know
		if (empty($location))
			{
				meta_refresh(3, append_sid("{$phpbb_root_path}weather.$phpEx"));
				trigger_error('You must have valid input in the form!');
			}		
		/**
		 * This is where the action starts
		 * @var $location string mixed
		 */
		build_weather::get_weather($location);
		add_form_key('weather');
		
			
		if (!check_form_key('weather'))
		{
			$error[] = 'Either the form was invalid or you are getting random weather!';
		}
		
		// Replace "error" strings with their real, localised form
		$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
		
				
		$template->assign_vars(array(
			'S_ACTION'		=> append_sid("{$phpbb_root_path}weather.$phpEx"), 'location=' . $location,
			'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
		));
	}
	
	public static function user_location()
	{
		global $user, $auth;
		
		// If the user is logged in
		if ($user->data['user_id'] != ANONYMOUS)
		{
			$location = request_var('location', '');
			//Get the custom profile field for this user
			$user->get_profile_fields($user->data['user_id']);
			
			//If they have a location set, use it
			if (!empty($user->profile_fields['pf_location']))
			{   
			   	$location = $user->profile_fields['pf_location'];
				
				/**
				 * We are cleaning any unwanted data out of the string
				 * @var string mixed
				 */
				$location = self::clean_location($location);
			}
			else
			{
				/**
				 * We will select a random location and return it
				 * @return string mixed $location
				 */
				$location = self::random_location();
				/**
				 * We are cleaning any unwanted data out of the string
				 * @var string mixed
				 */
				$location = self::clean_location($location);
			}
			/**
			 * This is where the action starts
			 * @var $location string mixed
			 */			
			build_weather::get_weather($location);
		}
		// If not logged in, use the default or let them check their weather via URL
		elseif ($user->data['user_id'] == ANONYMOUS && !$auth->acl_get('u_weather'))
		{
		   	/**
			 * We will select a random location and return it
	 		 * @return string mixed $location
	 		 */ 
			$location = self::random_location();
			/**
			 * We are cleaning any unwanted data out of the string
			 * @var string mixed
			 */
			$location = self::clean_location($location);
			/**
			 * This is where the action starts
			 * @var $location string mixed
			 */
			build_weather::get_weather($location);
		}
		
		return $location;
	}
	
	/**
	 * Lets clean the $location var and send the string to Google the way they like it
	 * @param object $location
	 * @return clean string 
	 */
	public static function clean_location($location)
	{		
		//If the location is empty, generate a random location
		if (empty($location))
		{
			/**
			 * We will select a random location and return it
			 * @return string mixed $location
	 		*/
			self::random_location();
		}
		$location = strip_tags($location, '');
		
		$search = array('%', ' ');
		$replace = array('', ',');
		
		$location = str_replace($search, $replace, $location, $count);	
		
		return $location;
	}
	
	/**
	 * We will select a random location and return it
	 * @return string mixed $location
	 */
	public static function random_location()
	{	
		global $user, $auth;
			
			$l_array = array(
				'Green Bay',
				'Mexico',
				'Paris',
				'Chicago',
				'Saint Louis',
				'Munich',
				'London',
				'Washington DC'
			);
			
			$nlocation = $l_array[array_rand($l_array, 1)];
			
			$location = request_var('location', $nlocation);
		
		return $location;
	}
}
?>