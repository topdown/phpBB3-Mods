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
 * Some of this script was * origionally authored by Ashwin Surajbali
 * Grabs weather data from Google.com's weather API and return a nicely formatted array
 * This script is modified from the origional version to benifite use in phpBB3
 * Requires PHP 5 or greater
 *
 * Some code in this script is Copyright 2009 by Ashwin Surajbali (http://www.redinkdesign.net).
 * This program is free software; you can redistribute it and/or modify it under the terms of the 
 * GNU General Public License as published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 * without even the implied warranty of ERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 * 
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class google_weather{
 

	/**
	 * This var will hold the location to sent to Google
	 * 
	 * @var string mixed
	 */
	public $location;
 
	/**
	 * Location of the google weather api
	 *
	 * @var string
	 */
	private $gweather_api_url = 'http://www.google.com/ig/api?weather=';
 
	/**
	 * Storage var for data returned from curl request to the google api
	 *
	 * @var string
	 */
	private $raw_data;
 
	/**
	 * Pull weather information for the location
	 * Weather data is returned in an associative array
	 *
	 * @param int $location
	 * @return array
	 */
	public function get_weather_data($location)
	{
 		/**
 		 * we need this for the error reporting and redirect
 		 * @var phpBB globals
 		 */
		global $phpEx, $phpbb_root_path;
			
		$this->location = $location;
			
 			//This doesn't work so lets dump this and use the phpBB way later
			/*if ($this->enable_cache && !empty($this->cache_path)){
				$this->cache_file = $this->cache_path . '/' . $this->zip;
				return $this->load_from_cache();
			}*/
 
		// build the url
		$this->gweather_api_url = $this->gweather_api_url . $this->location;
 
		if ($this->make_request())
		{
 
			$xml = new SimpleXMLElement($this->raw_data);
 
			$return_array = array();
 
			$return_array['forecast_info']['city'] = $xml->weather->forecast_information->city['data'];
			$return_array['forecast_info']['zip'] = $xml->weather->forecast_information->postal_code['data'];
			$return_array['forecast_info']['date'] = $xml->weather->forecast_information->forecast_date['data'];
			$return_array['forecast_info']['date_time'] = $xml->weather->forecast_information->current_date_time['data'];
 
			$return_array['current_conditions']['condition'] = $xml->weather->current_conditions->condition['data'];
			$return_array['current_conditions']['temp_f'] = $xml->weather->current_conditions->temp_f['data'];
			$return_array['current_conditions']['temp_c'] = $xml->weather->current_conditions->temp_c['data'];
			$return_array['current_conditions']['humidity'] = $xml->weather->current_conditions->humidity['data'];
			$return_array['current_conditions']['icon'] = 'http://www.google.com' . $xml->weather->current_conditions->icon['data'];
			$return_array['current_conditions']['wind'] = $xml->weather->current_conditions->wind_condition['data'];
 
			for ($i = 0; $i < count($xml->weather->forecast_conditions); $i++)
			{
				$data = $xml->weather->forecast_conditions[$i];
				$return_array['forecast'][$i]['day_of_week'] = $data->day_of_week['data'];
				$return_array['forecast'][$i]['low'] = $data->low['data'];
				$return_array['forecast'][$i]['high'] = $data->high['data'];
				$return_array['forecast'][$i]['icon'] = 'http://img0.gmodules.com/' . $data->icon['data'];
				$return_array['forecast'][$i]['condition'] = $data->condition['data'];
			}
 			
			/**
			 * Lets give them a proper error and shut it down
			 * if there is invalid input and send them home
			 * @var string
			 */
			if ($xml->weather->problem_cause['data'])
			{
				//We wont keep them here to long, maybe 3 seconds, then send them back
				meta_refresh(3, append_sid("{$phpbb_root_path}weather.$phpEx"));
				trigger_error('This place does not exist!');			
			}
		}
 			
			//This doesn't work so lets dump this and use the phpBB way later
			/*if ($this->enable_cache && !empty($this->cache_path)){
				$this->write_to_cache();
			}*/
 
		return $return_array;
 
	}
 
	private function make_request()
	{
 
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_URL, $this->gweather_api_url);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$this->raw_data = curl_exec ($ch);
		curl_close ($ch);
 
		if (empty($this->raw_data))
		{
			return false;
			
		}
		else
		{
			return true;
		}
 
	}
 
}
 
?>