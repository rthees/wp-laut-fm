<?php
/*
 Plugin Name: laut.fm for Wordpress
 Plugin URI: http://herrthees.de/wp-laut-fm
 Description: Displays data for a webradio-station hostet on laut.fm
 Version: 0.1.2
 Author: Ralf Thees
 Author URI: http://herrthees.de
 License: GPL2
 */


if (!load_plugin_textdomain('wplautfm', '/wp-content/languages/'))
	load_plugin_textdomain('wplautfm', '/wp-content/plugins/wp-laut-fm/languages/');

if (!class_exists('WPlautfm_Widget')) {

	class WPlautfm_Widget extends WP_Widget {

		var $wlfOptionKey = "wplautfm";

		var $lautfmApiUrl = 'http://api.laut.fm/';
		static $instance;

		static $CURRENTINSTANCENUMBER = 1;

		function WPlautfm_Widget() {
			$this->WP_Widget('wplautfm', 'WP-laut.fm',  array('description' => __('zeigt die laut-fm-Station an', 'wplautfm'), )	);
			add_action('init', array(&$this, 'stylesheet'));
			$this->default_options();
			$this -> wlfOptionKey = 'wplautfm';

			add_action('widgets_init', create_function('', 'register_widget( "WPlautfm_Widget" );'));
		}

		public function widget($args, $instance) {
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);

			echo $before_widget;

			echo $before_title . $title . $after_title;

			$data = $this -> getLautfmData();
			echo '<div class="wlf_widget_container" style="background-image: url('.$data['station']->images->station_120x120.'); background-repeat: no-repeat;">';
			echo '<div class="wlf_widget_listeners">'.$data['listeners'].'</div>';
			echo $data['station'] -> name . "<br/>\n" . $data['station'] -> format;
			echo '</div>';
			echo $after_widget;
		}
		
		function stylesheet() {

			wp_enqueue_style('wplautfm', plugins_url('/wplautfm.css', __FILE__));

		}

		function getInstance() {
			if (!isset(self::$instance)) {
				try {
					self::$instance = new self();
				} catch(Exception $e) {
					//Flattr_Logger::log($e->getMessage(), 'Flattr_View::getInstance');
					self::$instance = false;
				}
			}
			return self::$instance;
		}

		function default_options() {
			$options_array = array('station_name' => 'wuerzblog');
			add_option($this -> wlfOptionKey, $options_array);

		}

		function getLautfmData() {
			$wlf_option = get_option($this -> wlfOptionKey);
			$geturl = $this -> lautfmApiUrl . "station/" . $wlf_option['station_name'];
			$json_station = wp_remote_retrieve_body(wp_remote_get($geturl));
			$obj_station['station'] = json_decode($json_station);
			$obj_station['listeners'] = json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['station'] -> api_urls -> listeners)));
			$obj_station['current_song'] = json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['station'] -> api_urls -> current_song)));

			return $obj_station;
		}

	}

}

$wlf = new WPlautfm_Widget;

//print_r($wlf -> getLautfmData());
//include('widget.php');
?>