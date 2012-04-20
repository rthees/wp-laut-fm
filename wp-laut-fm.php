<?php
/*
Plugin Name: laut.fm for Wordpress
Plugin URI: http://herrthees.de/wp-laut-fm
Description: Displays data for a webradio-station hostet on laut.fm
Version: 0.1
Author: Ralf Thees
Author URI: http://herrthees.de
License: GPL2
*/


// Sprachdatei
	if(!load_plugin_textdomain('wplautfm','/wp-content/languages/'))  
		load_plugin_textdomain('wplautfm','/wp-content/plugins/wp-laut-fm/languages/');
	
class WPlautfmPlugin {
	
	var $wlfOptionKey = 'wp-laut-fm_Options';
	
	var $lautfmApiUrl = 'http://api.laut.fm/';
	
	function WPlautfmPlugin() {
		
	}
	
	function install()
		{
			$this->getOptions();
		}
	
	function getInstance()
		{
			global $WPlautfmPlugin;
			if(!isset($WPlautfmPlugin))
			{
				$WPlautfmPlugin = new WPlautfmPlugin();
			}
			
			return $WPlautfmPlugin;
		}
		
	function getLautfmData () {
		$geturl=$this->lautfmApiUrl."station/".$this->getOption('station_name');
		$json_station =  wp_remote_retrieve_body(wp_remote_get($geturl));
		$obj_station = json_decode($json_station);
		return $obj_station;
	}
	
	function getOptions()
		{
			// Default-Werte
			$options = array
			(
				'station_name' => '',
				'data_cache_time' => 10
			);
			
			// Gespeicherte Werte laden
			$saved = get_option($this->wlfOptionKey);
			
			// Wenn es gespeicherte Werte gibt
			if(!empty($saved))
			{
				// Gespeicherte Werte über Default-Werte schreiben
				foreach($saved as  $key => $option)
				{
					$options[$key] = $option;
				}
			}
			
			//
			if($saved != $options)
				update_option($this->wlfOptionKey, $options);
				
			return $options;
		}
		
		function getOption($key)
		{
			$options = $this->getOptions();
			
			return $options[$key];
		}
	
}

/**
	 * Plugin instanzieren
	 */
	if (class_exists('WPlautfmPlugin')): 
		$WPlautfmPlugin = WPlautfmPlugin::getInstance();
		if (isset($WPlautfmPlugin)) 
		{
			register_activation_hook(__FILE__, array(&$WPlautfmPlugin, 'install'));
		}
		// print_r($WPlautfmPlugin->getLautfmData());
	endif;
?>