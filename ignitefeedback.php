<?php
/*
  Plugin Name: IgniteFeedback
  Plugin URI: http://ignitefeedback.com/
  Description: WordPress survey plugin from IgniteFeedback.com
  Version: 1.0.6
  Author: IgniteFeedback
  Author URI: http://ignitefeedback.com/
  License: GPL2
 */
/*  Copyright 2016 IgniteFeedback (email : support@ignitefeedback.com)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 */

class IgniteFeedback {

	public static $plugin_basename;

	/**
	 * Construct.
	 */
	public function __construct() {
		// plugin setup. Get inluded files and setup options
		$this->includes();
		$this->options = get_option('ignitefeedback');
		$this->settings = new IgniteFeedback_Settings();
		if(empty($this->options)) {
			register_activation_hook( __FILE__, array( $this->settings, 'default_settings' ) );
			$this->options = get_option('ignitefeedback');
		}
		//delete_option('ignitefeedback');
		//delete_option('ignitefeedback_account_information');
		// Redirect to the Settings Page
		// Settings Page URL
		define('IGNITEFEEDBACK_SETTINGS_URL', 'admin.php?page=ignitefeedback_options_page');
		define('IGNITEFEEDBACK_SERVICE_URL','https://apps.ignitefeedback.com/');
		// Redirect to settings page on activation
		register_activation_hook(__FILE__, array($this,'ignitefeedback_activate'));
		add_action('admin_init', array($this,'ignitefeedback_redirect'));
		// function to add js to front end
		add_action('wp_head',array($this,'add_if_js_frontend'),100);
		self::$plugin_basename = plugin_basename(__FILE__);
		add_action( 'wp_ajax_connect_to_ignitefeedback_api', array( $this, 'connect_to_ignitefeedback_api' ));
	}

	/**
	 * Redirect user to plugin settings page after activation
	 *
	 */
	function ignitefeedback_activate() {
		add_option('bulkorderform_do_activation_redirect', true);
	}
	
	function ignitefeedback_redirect() {
		if (get_option('bulkorderform_do_activation_redirect', false)) {
			delete_option('bulkorderform_do_activation_redirect');
			if(!isset($_GET['activate-multi'])){
				wp_redirect(IGNITEFEEDBACK_SETTINGS_URL);
			}
		}
	}

	/**
	 * Load additional classes and functions
	 */
	public function includes() {	
		include_once( 'included/ignitefeedback-settings.php' );
	}

	/**
	 * Add javascript from IgniteFeedback to site Header
	 */
	function add_if_js_frontend(){
		$option = get_option( 'ignitefeedback_account_information' );
		$account_id = isset($option['account_id']) ? $option['account_id'] : '';
		?>
		<!--Please login to apps.ignitefeedback.com to change your survey behavior-->
	    <script type='text/javascript'>window._igniter = window._igniter || []; window._igniter.push(['<?php echo $account_id; ?>', '<?php echo IGNITEFEEDBACK_SERVICE_URL; ?>']);</script>
	    <script src='<?php echo IGNITEFEEDBACK_SERVICE_URL; ?>assets/javascripts/w/1/igniter.js' async></script>
		<?php
	}

	function connect_to_ignitefeedback_api(){
		$admin_email =  !empty($_POST['admin_email']) ? $_POST['admin_email'] : '';
		$first_name = !empty($_POST['first_name']) ? $_POST['first_name'] : '';
		$last_name =  !empty($_POST['last_name']) ? $_POST['last_name'] : '';
		$organization_name = !empty($_POST['organization_name']) ? $_POST['organization_name'] : '';
		$organization_domain = !empty($_POST['organization_domain']) ? $_POST['organization_domain'] : '';
		$has_account = !empty($_POST['has_account']) ? $_POST['has_account'] : '';

		if($has_account == 'yes'){
			/* Setup Call Data */
			$url = IGNITEFEEDBACK_SERVICE_URL.'api/account/retrieveID';
			$body_data = array(
				'email' => $admin_email
			);
			$body_data = json_encode($body_data);

			/* Call */
			$response = wp_remote_post($url, array(
		        'method' => 'POST',
		        'blocking' => true,
		        'headers' => array(
		            'Accept' => 'application/json',
		            'Content-type' => 'application/json',
		            'Authorization' => 'Basic ' . base64_encode('28:Vf-xe70tgVlKcjVh9VXuzehcP13ZPuPR'),
		        ),
		        'body' => $body_data
		    ));

			/* Decode Response and Save to variables */
		    $response_data = json_decode(wp_remote_retrieve_body( $response ),true);
		    $error = isset($response_data['error']) ? $response_data['error'] : '';
		    $account_id = isset($response_data['customerID']) ? $response_data['customerID'] : '';

		    /* Setup Error Messages */
		    if($error == 20){
		    	echo json_encode(array('type' => 'error','message' => 'We can\'t find an account with that email address. Please double check your address or sign up for an account.'));
				exit;
			}

			/* Save to DB */
			/* Settings */
			$settings = get_option('ignitefeedback');
			if(!empty($_POST)){
				$settings_array = array(
					'admin_email' => $admin_email,
					'first_name' => $first_name, 
					'last_name' => $last_name, 
					'organization_name' => $organization_name, 
					'organization_domain' => $organization_domain
				);
				update_option('ignitefeedback', $settings_array);
			}
			/* Account ID */
			$option = get_option( 'ignitefeedback_account_information' );
			$option['account_id'] = $account_id;
			update_option('ignitefeedback_account_information',$option);
			echo json_encode(array('type' => 'success','message' => 'Congratulations! IgniteFeedback has been connected to your WordPress site. Please login if you wish to adjust your survey settings. <br /><br /><a href="https://apps.ignitefeedback.com/" target="blank" class="button button-primary" style="font-size: 22px;line-height: 22px;height: inherit;padding: 8px;">Login</a>', 'replace' => 'replace'));
			exit;

		} else {
			/* Setup Call Data */
			$url = IGNITEFEEDBACK_SERVICE_URL.'api/account/create';
			$body_data = array(
				'email' => $admin_email,
				'firstName' => $first_name, 
				'lastName' => $last_name, 
				'organization' => array(
					'name' => $organization_name, 
					'domain' => $organization_domain
				)
			);
			$body_data = json_encode($body_data);

			/* Call */
			$response = wp_remote_post($url, array(
		        'method' => 'POST',
		        'blocking' => true,
		        'headers' => array(
		            'Accept' => 'application/json',
		            'Content-type' => 'application/json',
		            'Authorization' => 'Basic ' . base64_encode('28:Vf-xe70tgVlKcjVh9VXuzehcP13ZPuPR'),
		        ),
		        'body' => $body_data
		    ));

		    /* Decode Response and Save to variables */
		    $response_data = json_decode(wp_remote_retrieve_body( $response ),true);
		    $error = isset($response_data['error']) ? $response_data['error'] : '';
		    $account_id = isset($response_data['customerID']) ? $response_data['customerID'] : '';
		    $reset_token = isset($response_data['resetToken']) ? $response_data['resetToken'] : '';
		    /* Setup Error Messages */
			if($response['response']['code'] == 500){
				echo json_encode(array('type' => 'error','message' => 'uh oh, looks like there was an error. Please try again or contact help@ignitefeedback.com'));
				exit;
			} elseif($response['response']['code'] > 200){
				echo json_encode(array('type' => 'error','message' => 'It appears you may have an account with us already. Please check the box at the top to link WordPress with your existing IgniteFeedback account.'));
				exit;
			} elseif(is_wp_error($response)) {
				echo json_encode(array('type' => 'error','message' => 'uh oh, looks like there was an error. Please try again or contact help@ignitefeedback.com'));
				exit;
			}
		
			/* Save to DB */
			/* Settings */
			$settings = get_option('ignitefeedback');
			if(!empty($_POST)){
				$settings_array = array(
					'admin_email' => $admin_email,
					'first_name' => $first_name, 
					'last_name' => $last_name, 
					'organization_name' => $organization_name, 
					'organization_domain' => $organization_domain
				);
				update_option('ignitefeedback', $settings_array);
			}

			/* Account ID */
			$option = get_option('ignitefeedback_account_information');
			$option['account_id'] = $account_id;
			update_option('ignitefeedback_account_information',$option);

			/* Return Setup URL */
			$setup_link = IGNITEFEEDBACK_SERVICE_URL.'password/update/'.base64_encode($admin_email).'/'.$reset_token;
			echo json_encode(array('type' => 'success','message' => 'Your account has been successfully created! To get started with your first WordPress survey, please visit the link below to choose an IgniteFeedback password. <br /><br /><a href="'.$setup_link.'" target="blank" class="button button-primary" style="font-size: 22px;line-height: 22px;height: inherit;padding: 8px;">Setup your password</a>', 'fade' => 'nofade', 'replace' => 'replace'));
		}

		exit;
	}

}
$IgniteFeedback = new IgniteFeedback();