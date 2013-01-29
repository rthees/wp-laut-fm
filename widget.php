<?php
	
	class WPlautfm_Widget extends WP_Widget 
	{
		
		function WPlautfm_Widget()
		{
			$wOptions = array( 
								'classname' => 'wplautfm', // Name 
								'description' => __('Zeigt die laut-fm-Station an.', 'wplautfm')
			);
			$cOptions = array( 
								'width' => 300, 
								'height' => 350, 
								'id_base' => 'wplautfm-widget' 
			);
			
			$this->WP_Widget( 'wplautfm-widget', __('WP-laut.fm', 'wplautfm'), $wOptions, $cOptions );
			
			add_action('init', array(&$this, 'stylesheet'));
		}
		
		function widget( $args, $instance ) 
		{						
			extract( $args );
			$title = apply_filters('widget_title', $instance['title']);
			
			
			echo $before_widget; 
			
			echo $before_title . $title . $after_title;
			
			
			$data=WPlautfmPlugin::getLautfmData();
			print_r($data);
			
			echo $data['station']->name."<br/>\n".$data['station']->format;
			
			echo $after_widget;
		}
		
		function stylesheet()
		{
			
			wp_enqueue_style('wplautfm', plugins_url('/wplautfm.css', __FILE__));
			
		}
	}
?>