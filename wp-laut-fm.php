<?php
/*
 Plugin Name: laut.fm for Wordpress
 Plugin URI: http://herrthees.de/wp-laut-fm
 Description: Displays data for a webradio-station hostet on laut.fm
 Version: 0.1.3
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

		 function widget($args, $instance) {
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);

			echo $before_widget;

			echo $before_title . $title . $after_title;

			$data = $this -> getLautfmData($instance);
			echo '<div class="wlf_widget_container" style="color:'.$data['station']->color.';">';
			echo '<div class="wlf_widget_imgbox" style="background-image: url('.$data['station']->images->station_120x120.'); background-repeat: no-repeat; ">';
			echo '<div class="wlf_widget_listeners">'.$data['listeners'].'<br/><small>'.__('Hörer','wplautfm').'</small></div>';
			echo '</div><p><strong><a href="'.$data['station']->page_url.'">'.$data['station'] -> name . "</a></strong><br/>\n" . $data['station'] -> format.'</p>';
			echo '<div>'.$data['current_song'].'</div>'
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

		function getLautfmData($instance) {
			$wlf_option = get_option($this -> wlfOptionKey);
			$geturl = $this -> lautfmApiUrl . "station/" . $instance['station_name'];
			$json_station = wp_remote_retrieve_body(wp_remote_get($geturl));
			$obj_station['station'] = json_decode($json_station);
			$obj_station['listeners'] = json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['station'] -> api_urls -> listeners)));
			$obj_station['current_song'] = json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['station'] -> api_urls -> current_song)));
			$obj_station['current_playlist'] = json_decode(wp_remote_retrieve_body(wp_remote_get($obj_station['station'] -> current_playlist)));

/*
			$geturl = $this -> lautfmApiUrl . "stations?limit=9999";		
			$json_station = wp_remote_retrieve_body(wp_remote_get($geturl));
			$all_stations = json_decode($json_station);
			$network[]=array('StationA','StationB','Anzahl');
			//print_r($all_stations);
			foreach ($all_stations as $st) {
 				//$listeners = json_decode(wp_remote_retrieve_body(wp_remote_get($st -> api_urls -> listeners)));
				$geturl = $this -> lautfmApiUrl . "station/" . $st->name.'/network';
				$json_station = wp_remote_retrieve_body(wp_remote_get($geturl));
				$net_stations = json_decode($json_station);
				if (sizeof($net_stations)>0) {
					foreach ($net_stations as $ns) {
						$network[]=array($st->name,$ns->station->name,$listeners);
					}
				}
			}
			//print_r($network);
			$fp = fopen('lautfm.csv', 'w');
			foreach ($network as $fields) {
    			fputcsv($fp, $fields);
			}
			fclose($fp);
			
*/
			

			return $obj_station;
		}
		
		function form($instance)
		{
			
			$defaults = array(
                'title' => __("laut.fm", 'wplautfm'),
                'station_name' => ''

			);

			$instance   = wp_parse_args((array) $instance, $defaults);
			$title      = strip_tags($instance['title']);
			$station_name= $instance['station_name'];
			//$pattern    = htmlspecialchars($instance["pattern"]);

			echo '<a href="http://flattr.com/thing/313825/Wordpress-Plugin-A-Year-Before" target="_blank"><img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>';			
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("title") . '">' . __('Title:', 'wplautfm') . ' <input style="width: 200px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("station_name") . '">' . __('Name der laut.fm-Station:', 'wplautfm') . ' <input style="width: 60px;" id="' . $this->get_field_id("station_name") . '" name="' . $this->get_field_name("station_name") . '" type="text" value="' . $station_name . '" /></label></p>';
			
		}


		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;

			$instance['title']       = strip_tags(stripslashes($new_instance['title']));
			$instance["station_name"]         = strip_tags(stripslashes($new_instance['station_name']));
			

			return $instance;
		}

	}

}

$wlf = new WPlautfm_Widget;

//print_r($wlf -> getLautfmData());
//include('widget.php');
?>