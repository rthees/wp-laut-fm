<?php
/*
Plugin Name: laut.fm for Wordpress
Plugin URI: http://herrthees.de/wp-laut-fm
Description: Displays data for a webradio-station hostet on laut.fm
Version: 0.1.1
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
	
	static $CURRENTINSTANCENUMBER = 1;
	
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
		$obj_station['studio'] = json_decode($json_station);
		$obj_station['listeners']=json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['studio']->api_urls->listeners)));
		$obj_station['current_song']=json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['studio']->api_urls->current_song)));
		return $obj_station;
	}
	
	function getOptions()
		{
			// Default-Werte
			$options = array
			(
				'station_name' => 'wuerzblog',
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


if(!function_exists('load_wplautfm')):		
		add_action( 'widgets_init', 'load_wplautfm' );
		function load_wplautfm() 
		{
			register_widget( 'WPlautfm_Widget' );
		}
	endif;

/**
	 * Plugin instanzieren
	 */
	if (class_exists('WPlautfmPlugin')): 
		$WPlautfmPlugin = WPlautfmPlugin::getInstance();
		if (isset($WPlautfmPlugin)) 
		{
			register_activation_hook(__FILE__, array(&$WPlautfmPlugin, 'install'));
		}
		print_r($WPlautfmPlugin->getLautfmData());
	endif;
	
	include('widget.php');
?>