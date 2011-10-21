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

include($phpbb_root_path . 'includes/classes/google_weather.' . $phpEx);

/**
* weather class extends google_weather class
*/
class build_weather extends google_weather 
{
	public $day;
  
	/**
     * Returns the weather acording to location form Google
     * @param object $location
     * @return string mixed
     */
	public static function get_weather($location)
	{
		global $phpbb_root_path, $phpEx, $template, $user, $auth;
		
		$w = new google_weather();
	
		$ar_data = $w->get_weather_data($location);


		/**
		 * This will return the static date, time and location
		 * @var return strings
		 */
		$city 		= $ar_data['forecast_info']['city'];
		$zip 		= $ar_data['forecast_info']['zip'];
		$date 		= $ar_data['forecast_info']['date'];
		$date_time 	= $ar_data['forecast_info']['date_time'];
		
		/**
		 * This will return all of the dynamic data in an array
		 * @var return strings mixed
		 */
		$current 	= $ar_data['current_conditions']['condition'];
		$temp_f 	= $ar_data['current_conditions']['temp_f'];
		$temp_c 	= $ar_data['current_conditions']['temp_c'];
		$humidity 	= $ar_data['current_conditions']['humidity'];
		$icon		= $ar_data['current_conditions']['icon'];
		$wind 		= $ar_data['current_conditions']['wind'];
		
		// Loop them and return an array of 3 days Googles Max :(
		for ($i = 0; $i <= 3; ++$i) 
		{
    		$data = array(
				'day' 			=> $ar_data['forecast'][$i]['day_of_week'],		
				'weather' 		=> $ar_data['forecast'][$i]['condition'],
				'low' 			=> $ar_data['forecast'][$i]['low'],
				'high' 			=> $ar_data['forecast'][$i]['high'],
				'image' 		=> $ar_data['forecast'][$i]['icon'],
			);	
		
			$template->assign_block_vars('weather', array(
				'DAY'		=> $data['day'],
				'WEATHER'	=> $data['weather'],
				'LOW'		=> $data['low'],
				'HIGH'		=> $data['high'],
				'IMAGE'		=> $data['image'],
			));
		}
		
		unset ($ar_data);
		unset ($data);
		
		// Lets give this a nice date
		$date_time = date("l \\t\h\e jS");
		
		// Adding the static vars to the template
		$template->assign_vars(array(
			'CITY'		=> $city,
			'ZIP'		=> $zip,
			'DATE'		=> $date,
			'TIME'		=> $date_time,
			'CURRENT'	=> $current,
			'TEMP_F'	=> $temp_f,
			'TEMP_C'	=> $temp_c,
			'HUMIDITY'	=> $humidity,
			'ICON'		=> $icon,
			'WIND'		=> $wind,
			//Please do not remove this, it helps support Free stuff from me
			'COPY'		=> 'phpBB3 gWeather mod By <a href="http://www.webmastersunited.org" title="Webmasters United Web Development" >topdown &copy; 2009</a>',
		));	
		
	}
}

?>