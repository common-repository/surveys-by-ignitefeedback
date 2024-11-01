<?php

class IgniteFeedback_Settings {
	
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) ); // Registers settings
		add_action( 'admin_menu', array( $this, 'ignitefeedback_add_page' ) );
		add_filter( 'plugin_action_links_'.IgniteFeedback::$plugin_basename, array( $this, 'ignitefeedback_add_settings_link' ) );
	}

	/**
	 * User settings.
	 */
	public function init_settings() {

		$option = 'ignitefeedback';
		$account_information = 'ignitefeedback_account_information';
	
		// Create option in wp_options.
		if ( false == get_option( $option ) ) {
			add_option( $option );
		}
		if ( false == get_option( $account_information ) ) {
			add_option( $account_information );
		}

		global $current_user;
      	get_currentuserinfo();
	
		// Section.
		add_settings_section(
			'plugin_settings',
			__( '', 'ignitefeedback' ),
			array( $this, 'section_options_callback' ),
			$option
		);

		add_settings_field(
			'new_or_existing',
			__( 'Already have an account? Click this box.', 'ignitefeedback' ),
			array( $this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'new_or_existing',
				'description'	=> 'Check this box to link to your existing IgniteFeedback account',
				'class'			=> 'already-have-account'
			)
		);

		add_settings_field(
			'admin_email',
			__( 'Email', 'ignitefeedback' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'admin_email',
				'description'	=> 'Your primary email address.',
				'placeholder'	=> $current_user->user_email,
				'type'			=> 'email'
			)
		);

		add_settings_field(
			'first_name',
			__( 'First Name', 'ignitefeedback' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'first_name',
				'description'	=> 'Your first name',
				'placeholder'	=> $current_user->user_firstname,
				'class'			=> 'hide-if-have-account',
				'type'			=> 'text'
			)
		);

		add_settings_field(
			'last_name',
			__( 'Last Name', 'ignitefeedback' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'last_name',
				'description'	=> 'Your last name',
				'placeholder'	=> $current_user->user_lastname,
				'class'			=> 'hide-if-have-account',
				'type'			=> 'text'
			)
		);

		add_settings_field(
			'organization_name',
			__( 'Organization', 'ignitefeedback' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'organization_name',
				'description'	=> 'Name of your organization or company',
				'placeholder'	=> get_bloginfo('name'),
				'class'			=> 'hide-if-have-account',
				'type'			=> 'text'
			)
		);

		add_settings_field(
			'organization_domain',
			__( 'Site URL', 'ignitefeedback' ),
			array( $this, 'text_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'organization_domain',
				'description'	=> 'The domain or URL for your site (for example, http://domain.com) ',
				'placeholder'	=> get_bloginfo('url'),
				'class'			=> 'hide-if-have-account',
				'type'			=> 'url'
			)
		);
		
		add_settings_field(
			'terms_and_conditions',
			__( 'Terms of Service', 'ignitefeedback' ),
			array( $this, 'checkbox_element_callback' ),
			$option,
			'plugin_settings',
			array(
				'menu'			=> $option,
				'id'			=> 'terms_and_conditions',
				'description'	=> 'I have read and agree to the <a href="#TB_inline?width=600&height=550&inlineId=terms-of-service" class="thickbox">Terms of Service</a> and the <a href="#TB_inline?width=600&height=550&inlineId=privacy-policy" class="thickbox">Privacy Policy</a>.',
				'class'			=> 'terms-and-conditions',
				'required'		=> 'required'
			)
		);

		// Section.
		add_settings_section(
			'account_information',
			__( '', 'ignitefeedback' ),
			array( $this, 'section_options_callback' ),
			$account_information
		);

		add_settings_field(
			'account_id',
			__( 'Account ID (leave alone unless directed to change)', 'ignitefeedback' ),
			array( $this, 'text_element_callback' ),
			$account_information,
			'account_information',
			array(
				'menu'			=> $account_information,
				'id'			=> 'account_id',
				'description'	=> 'Do not modify unless you know your IgniteFeedback account id',
				'placeholder'	=> ''
			)
		);

		// Register settings.
		register_setting( $option, $option, array( $this, 'ignitefeedback_options_validate' ) );

		// Register defaults if settings empty (might not work in case there's only checkboxes and they're all disabled)
		$option_values = get_option($option);
		
		if ( empty( $option_values ) ) {
			$this->default_settings();
		}
	}

	/*
	 * Add menu page
	*/
	public function ignitefeedback_add_page() {
		$ignitefeedback_page = add_submenu_page(
			'options-general.php',
			__( 'IgniteFeedback', 'ignitefeedback' ),
			__( 'IgniteFeedback', 'ignitefeedback' ),
			'manage_options',
			'ignitefeedback_options_page',
			array( $this, 'ignitefeedback_options_do_page' )
		);

		add_action('admin_print_scripts-'.$ignitefeedback_page,array($this,'enqueue_jquery_validate'));
	}

	/*
	 * Enqueue jQuery Validate Script
	 */
	function enqueue_jquery_validate(){
		wp_register_script('jquery-validation-plugin', plugin_dir_url( __FILE__ ). 'js/jquery.validate.min.js', array('jquery'));
		wp_enqueue_script('jquery-validation-plugin');
	}

	/**
	 * Add settings link to plugins page
	 */
	public function ignitefeedback_add_settings_link( $links ) {
	    $settings_link = '<a href="options-general.php?page=ignitefeedback_options_page">'. __( 'Settings', 'ignitefeedback' ) . '</a>';
	  	array_push( $links, $settings_link );
	  	return $links;
	}
	 
	/**
	 * Default settings.
	 */
	public function default_settings() {
		$options = get_option('ignitefeedback');
		$options = array();
		update_option('ignitefeedback',$options);
	}

	/**
	 * Build the options page.
	 */
	public function ignitefeedback_options_do_page() {
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php _e('IgniteFeedback Settings','ignitefeedback') ?></h2>
			<?php
			$option = get_option('ignitefeedback');
			$account_information = get_option('ignitefeedback_account_information');
			//print_r($option); //for debugging
			?>
			<div id="settings-page-header" style="background-color:#EB6127;padding:20px">
				<img src="<?php echo plugins_url().'/surveys-by-ignitefeedback/included/images/ignitelogo.png'; ?>" />
				<h1 style="display:inline;color:#ffffff;vertical-align: top;line-height: 73px;margin-left: 20px;">
					- Customer Engagement Made Awesome!
				</h1>
			</div>
			<div id="api-response-message-container" class="updated" style="padding:15px;font-size:20px;display:none;">
			</div>
			<form method="post" action="options.php" id="ignitefeedbacksettingsform">
				<?php
				if(empty($account_information['account_id'])){
					?>
					<h1>Activate your Free Account!</h1>
					<?php
					settings_fields( 'ignitefeedback' );
					do_settings_sections( 'ignitefeedback' );
					submit_button('Sign-Up (FREE FOR LIFE)');
				} else {
					?>
					<div style="font-size:22px;margin:0px auto; width:50%;text-align:center;line-height:30px;margin-top:100px">
						<p style="font-size:22px;">The IgniteFeedback JavaScript code is active on this site!</p>
						<p style="font-size:22px;">To change your survey behavior, please login with your IgniteFeedback credentials.</p>
						<a href="<?php echo IGNITEFEEDBACK_SERVICE_URL; ?>" class="button button-primary" style="font-size: 22px;line-height: 22px;height: inherit;padding: 8px;margin-top: -8px;">Login</a>
					</div>
					<?php
				}
				?>
			</form>
			<?php add_thickbox(); ?>

			<div id="terms-of-service" style="display:none;">
			  <h2 id="tosTitle">IGNITEFEEDBACK TERMS OF SERVICE</h2>
			  <p>PLEASE READ THE TERMS AND CONDITIONS OF THIS LICENSE AGREEMENT (“AGREEMENT”) CAREFULLY BEFORE USING THE SOFTWARE (AS DEFINED BELOW). THIS IS A LEGALLY BINDING CONTRACT BETWEEN YOU AND IGNITEFEEDBACK, INC., (“IGNITEFEEDBACK”). BY ACCEPTING ELECTRONICALLY OR USING THIS SOFTWARE, YOU ACCEPT ALL THE TERMS AND CONDITIONS OF THIS AGREEMENT. If you do not agree with the terms and conditions of this Agreement, do not use the Software.</p>
			  <p>This Agreement accompanies certain software, features, content, roadmap, and other materials, including any upgrades or updates thereto as provided by IGNITEFEEDBACK (collectively, the “Software”) and related explanatory written materials together with information provided by IGNITEFEEDBACK that lists the conditions subject to which you may use the Software (“Documentation”).</p>
			  <ol>
			    <li>License
			      <p>IGNITEFEEDBACK grants to you a non-exclusive license to use the Software and the Documentation for the period of time set forth in your applicable trial or purchased subscription, or if no period of time is stated, fourteen days for a trial subscription and one month from the date you accept this Agreement for a purchased subscription (the “Service Period”), provided that you agree to the terms and conditions of this Agreement.&nbsp;&nbsp;This Agreement will automatically renew for subsequent successive time periods set forth in your applicable purchased subscription, unless set forth otherwise in your applicable order.&nbsp; IGNITEFEEDBACK may terminate the Agreement at any time&nbsp;and your right to access and use the Software and Documentation shall terminate at the end of your subscription.</p>
			      <p>IGNITEFEEDBACK reserves the right to make changes to the Software in IGNITEFEEDBACK’s discretion.  Upgrades and updates of the Software shall be provided to you by IGNITEFEEDBACK during the term of the license indicated in the Documentation or other transaction materials made available to you at the time you purchase the Software. You will not be entitled to receive any feature or content updates or upgrades of the Software unless you renew the Service Period or purchase a new subscription.</p>
			    </li>
			    <li>Permitted use of the software
			      <p>The Software is hosted by IGNITEFEEDBACK.  You may access and permit the authorized number of users to access the Software and the Documentation as set forth in your applicable order.   The Software may be only used by you or your subsidiaries (those entities over which you have more than fifty percent (50%) ownership and control) for internal purposes that do not contravene this Agreement or applicable law. For all use of the Software under your subscription, you will ensure compliance with all obligations imposed on you hereunder. Any obligations of IGNITEFEEDBACK in respect of the Software shall be owed solely to you and not your subsidiaries that use the Software under this license.</p>
			      <p>ANY USE OF THE SOFTWARE OTHER THAN AS EXPRESSLY AUTHORIZED BY THIS SECTION OR ANY RESALE OR FURTHER DISTRIBUTION OF ACCESS TO THE SOFTWARE CONSTITUTES A MATERIAL BREACH OF THIS AGREEMENT AND MAY VIOLATE APPLICABLE COPYRIGHT LAWS.</p>
			      <p>IGNITEFEEDBACK reserves the right to suspend your access to the Software for any unpaid, overdue amounts or for any misuse of the Software.</p>
			    </li>
			    <li>Fees and Refund Policy
			      <p>Certain of our Services may require registration and payment as will be indicated with respect to such Service ("Paid Services"). We reserve the right to deny any registration form or to cancel any existing accounts. Any and all payments made in connection with the Paid Services shall be non-refundable for any reason whether you used the Paid Services in whole or in part or have not used them at all.</p>
			      <p>Subscriptions may be subject to pricing changes. Pricing for monthly subscriptions can change at any time. For all other subscriptions, if a pricing change occurs, you will be notified not less than 20 days prior to your automatic renewal date via the email address with which your account is associated. Unless a formal cancellation request is received prior to 5 days in advance of your automatic renewal date, your IGNITEFEEDBACK subscription will automatically renew at the new price.</p>
			      <p>If you choose to cancel your account during your subscription term, you will not be refunded in whole or in part. If you choose to downgrade your subscription level during your subscription term, you will not receive a cash refund at any time. If a monthly subscription is canceled, the subscriber will be entitled to use of the product until the end of the then current month’s subscription.  You may cancel your subscription by sending an email to help@ignitefeedback.com.</p>
			      <p>If, during the renewal of your subscription term, your credit card is no longer valid, you will be notified and asked to update your payment card information.  If your payment cannot be processed at the time of subscription renewal, access to IGNITEFEEDBACK will be terminated.</p>
			      <p>In some states, IGNITEFEEDBACK is required to charge sales tax on IGNITEFEEDBACK subscriptions.</p>
			    </li>
			    <li>Ownership rights
			      <p>The Software, trademarks, and Documentation are the intellectual property of IGNITEFEEDBACK and are protected by applicable intellectual property laws, international treaty provisions and other applicable laws of the country in which the Software is being used. The structure, organization and code of the Software are valuable trade secrets and confidential information of IGNITEFEEDBACK. To the extent you provide any comments or suggestions about the Software to IGNITEFEEDBACK, IGNITEFEEDBACK shall own any comments or suggestions and shall have the right to retain and use any such comments or suggestions in our current or future products or services, without further compensation to you and without your approval of such retention or use.</p>
			      <p>Except as stated in this Agreement, your use of the Software does not grant you any rights or title to any intellectual property rights in the Software, trademarks, or Documentation. All rights to the Software, trademarks, and Documentation, including all associated copyrights, patents, trade secret rights, trademarks and other intellectual property rights, are reserved by IGNITEFEEDBACK.</p>
			    </li>
			    <li>Restrictions
			      <p>You may not use the Software or the Documentation except as set forth in Section 2 of this Agreement. You may not mask or remove any proprietary notices or labels on the Software. You agree not to copy, modify, adapt, translate, reverse engineer, decompile or disassemble the Software or otherwise attempt to discover the source code of the Software or algorithms contained therein or create any derivative works from the Software.</p>
			      <p>You may not permit third parties to benefit from the use or functionality of the Software via a timesharing, service bureau or other similar arrangement.</p>
			    </li>
			    <li>Transfer
			      <p>You may not rent, lease, sub-license, or lend the Software, trademarks or the Documentation or any portions thereof. You may not transfer or assign the license herein or any of your obligations in this Agreement, in whole or in part, without IGNITEFEEDBACK’S prior written consent.</p>
			    </li>
			    <li>Disclaimers and exclusion of liability
			      <p>IGNITEFEEDBACK DOES NOT AND CANNOT WARRANT THE PERFORMANCE OR RESULTS YOU MAY OBTAIN BY USING THE SOFTWARE OR DOCUMENTATION. THE SOFTWARE IS PROVIDED “AS IS” AND IGNITEFEEDBACK MAKES NO EXPRESS OR IMPLIED WARRANTIES OR CONDITIONS AND, TO THE MAXIMUM EXTENT PERMITTED BY LAW, DISCLAIMS ANY AND ALL CONDITIONS AND WARRANTIES IMPLIED BY STATUTE, COMMON LAW OR JURISPRUDENCE, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF NONINFRINGEMENT OF THIRD PARTY RIGHTS, MERCHANTABILITY, SUITABLE QUALITY OR FITNESS FOR ANY PARTICULAR PURPOSE. YOU AGREE AND ACCEPT THAT, TO THE FULL EXTENT PERMITTED BY LAW, IN NO EVENT WILL IGNITEFEEDBACK BE LIABLE TO YOU FOR ANY DAMAGES, ESPECIALLY FOR CONSEQUENTIAL, INCIDENTAL OR SPECIAL DAMAGES, INCLUDING ANY LOST PROFITS, LOST SAVINGS OR LOST DATA, EVEN IF IGNITEFEEDBACK HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES, OR FOR ANY CLAIM BY ANY THIRD PARTY. YOU AGREE AND ACCEPT THAT, TO THE FULL EXTENT PERMITTED BY LAW, IN NO CASE SHALL IGNITEFEEDBACK’S LIABILITY FOR ANY DAMAGE EXCEED THE AMOUNT OF FEES PAID FOR THE SOFTWARE THAT IS THE SUBJECT OF THE CLAIM OR DISPUTE.</p>
			      <p>THE FOREGOING EXCLUSIONS AND LIMITATIONS OF LIABILITY OF IGNITEFEEDBACK DO NOT LIMIT POTENTIAL LIABILITY FOR DEATH, PERSONAL INJURY OR FRAUD OVER THE EXTENT PERMITTED BY APPLICABLE LAWS.</p>
			    </li>
			    <li>Privacy; Processing of personal information
			      <p>IGNITEFEEDBACK requires each user to register for use of the Software.  IGNITEFEEDBACK will collect personal information about the user during the registration.  IGNITEFEEDBACK will use the personal information to manage, activate, and administer the user’s subscription to the Software.</p>
			      <p>Personal information will be subject to IGNITEFEEDBACK’s privacy policy located at http://www.ignitefeedback.com/privacypolicy/.</p>
			    </li>
			    <li>U.S. Government restricted rights
			      <p>This Software and Documentation are deemed to be “commercial computer software” and “commercial computer software documentation”, respectively, and subject to certain restricted rights as identified in FAR Section 12.212 “Computer Software” and DFARS 227.7202, “Rights in Commercial Computer Software or Commercial Computer Software Documentation”, as applicable, or any successor U.S. regulations. Any use, modification, reproduction, release, performance, display or disclosure of the Software by the U.S. Government shall be done solely in accordance with this Agreement.</p>
			    </li>
			    <li>Export regulations
			      <p>You agree and accept that the use of the Software and the Documentation may be subject to import and export laws of any country, including those of the United States (specifically the Export Administration Regulations (EAR)) and the European Union. In using the Software, you agree to and acknowledge that you are exclusively responsible for complying with all applicable laws and regulations, including but not limited to all United States and European Union trade sanctions and export regulations (including any activities relating to nuclear, chemical or biological materials or weapons, missiles or technology capable of mass destruction), regardless of the country in which you reside in or of which you are a citizen.</p>
			    </li>
			    <li>Governing law and jurisdiction
			      <p>The laws of the State of Montana, excluding its conflicts of law rules, govern this Agreement and your use of the Software and the Documentation. The application of the United Nations Convention on Contracts for the International Sale of Goods is expressly excluded. The courts located within the county of Gallatin, Montana shall be the exclusive jurisdiction and venue for any dispute or legal matter arising out of or in connection with this Agreement or your use of the Software and the Documentation. Notwithstanding this, you agree that IGNITEFEEDBACK shall still be allowed to apply for injunctive remedies or an equivalent type of urgent legal relief in any jurisdiction.</p>
			    </li>
			    <li>General
			      <p>This Agreement is the entire agreement between you and IGNITEFEEDBACK relating to the Software and Documentation. This Agreement supersedes all prior or contemporaneous oral or written communications, proposals, and representations with respect to the Software or Documentation. Notwithstanding the foregoing, nothing in this Agreement will diminish any rights you may have under existing consumer protection legislation or other applicable laws in your jurisdiction that may not be waived by contract.</p>
			      <p>This Agreement will immediately terminate upon your breach of any obligation contained herein (especially your obligations in Sections 2, 3, 5, 6, and 10). IGNITEFEEDBACK reserves the right to any other remedies available under law in the event your breach of this Agreement adversely affects IGNITEFEEDBACK. The limitations of liability and disclaimers of warranty and damages contained herein shall survive termination of this Agreement. This Agreement may be modified by the Documentation. No provision hereof shall be deemed waived unless such waiver shall be in writing and signed by IGNITEFEEDBACK. If any provision of this Agreement is held invalid, the remainder of this Agreement shall continue in full force and effect.</p>
			      <p>It may happen that applicable transaction materials made available to you explicitly amend or delete certain parts of this Agreement or adds new parts hereto. In such cases the changes, deletions and additions included in the applicable transaction materials made available to you take precedence over this version of the Agreement. If you have any questions regarding this Agreement or wish to request any information from IGNITEFEEDBACK, please e-mail: help@ignitefeedback.com.</p>
			    </li>
			  </ol>
			</div>
			<div id="privacy-policy" style="display:none;">
				<h2 id="privacyPolicyTitle">IGNITEFEEDBACK – Website and Application Privacy Policy</h2>
				<p>IGNITEFEEDBACK.com ("Website") is a Website and Application owned and operated by IGNITEFEEDBACK, Inc. ("IGNITEFEEDBACK", "We" or "Us"). We respect our users‘ privacy, and make an effort to provide services that address the following privacy standards.</p>
				<p>When you use the IGNITEFEEDBACK Website, products and services, you consent to the collection, use and disclosure of your personal information as described below.</p>

				<ol>
				<li><p>No registration is required when you visit the Website IGNITEFEEDBACK.com.  Registration is required to use our product.</p></li>
				<li><p>When you register as a user, we collect and store your name and email address because it is used to log-in to your IGNITEFEEDBACK product.  After product registration, you are given the opportunity to opt-out of marketing emails. For users who opt-in, we add you to our list of interested customers and send you occasional emails about IGNITEFEEDBACK’s Website, products or services or the products or services of our partners. The purpose of collecting email addresses is to: 1) verify registered users of our products when they log-in to the product, 2) send email notifications regarding product updates and/or changes, 3) send users our newsletter, blogs or other such marketing materials, and 4) to manage your subscription.  When you respond to surveys, you may include your personal information or not.</p></li>
				<li><p>If you have not opted-out, each marketing email we send includes a link to opt out of future marketing emails.</p></li>
				<li><p>When you visit or logn to our Website, we ask&nbsp;your browser&nbsp;to&nbsp;store one or more&nbsp;cookies&nbsp;that&nbsp;we use to&nbsp;recognize&nbsp;your reoccurring visits&nbsp;in order to improve your experience with our Website. To continually improve our products and services, we&nbsp;use commercially available analytics tools to measure our website traffic, observe visitor behavior, collect feedback, and other factors that help us enhance your experience with IGNITEFEEDBACK.&nbsp;We collect usage data and use such data in an anonymized and aggregated way.  We&nbsp;utilize&nbsp;cookies and store information, including without limitation, your IP address, system information and browser information as a part of our website analytics tools. Cookies expire periodically. You may refuse the use of&nbsp;all&nbsp;cookies by selecting the appropriate settings on your&nbsp;browser. Please&nbsp;note that if you do this you may not be able to use the full functionality of this Website.</p></li>
				<li><p>We will not transfer your personal information to third party entities. Please note there may be times when we must disclose your personal information in response to the following: (1) where necessary to satisfy a legitimate government request or order; (2) in response to a third-party subpoena, if we believe, on the advice of our attorneys, that we are required to respond; (3) where we hire a contractor to perform a service for us, such as product development or market research (but not if doing so would violate the terms of our privacy policy, or laws governing personal data); (4) if we obtain your permission; or (5) if necessary to defend ourselves or our users (for example, in a lawsuit).</p>
					<p>Information that we collect is stored on our servers and may be accessed by our employees, contractors, representatives, agents, or resellers who are working on our behalf.  Servers are located in the US.  Personal information on our servers is accessible only through encrypted SSL connections and access is limited to authorized personnel. Company networks are password protected and subject to additional policies and procedures for security.</p>
				</li>
				<li><p>In general, our policy is to keep personal information for no longer than reasonably necessary in light of the purpose for which the personal information was collected, plus any additional period that is permitted or required by law thereafter. Following the expiration of the purpose for which we collected personal information plus any additional period that is permitted or required by law, we will either delete or de-identify the personal information from our systems. You may contact us to request that your personal information be removed from our system, other than personal information necessary for the administration, activation and management of your subscription to an IGNITEFEEDBACK product or service.</p>
					<p>You may request information on the way your personal information is stored. In addition, you may request changes to the personal information we have on file for you, for example if you believe that some personal information we have about you is incorrect, or there is some personal information about you that has changed (a former email address for you is invalid). To request information or changes regarding your personal information that we have on file, please email help@ignitefeedback.com with the headline “PRIVACY REQUEST” in the message line.</p>
				</li>
				<li><p>If we implement any changes to this privacy policy, we will publish an updated policy on our Website.</p></li>
				</ol>
				<br>
				This Privacy Policy is effective as of April 29, 2015.
			</div>
			<style type="text/css">
			label.error{
				color:red;
			}
			</style>
			<script type="text/javascript">
			jQuery( document ).ready(function( $ ) {
				/* Validate Form */
				$("#ignitefeedbacksettingsform").validate({
					submitHandler: function(form) {
						return false;
					}
				});
				/* Determine action based on whether user selects existing or new account */
				$('input[name="ignitefeedback[new_or_existing]"]').on('change',function(){
					if($(this).prop('checked') == true){
						$('input.hide-if-have-account').parents('tr').hide();
						$('#ignitefeedbacksettingsform #submit').val('Link Account');
						$('input#terms_and_conditions').removeAttr('required');
						$('input#terms_and_conditions').hide();
						$('tr.terms-and-conditions').hide();
					} else {
						$('input.hide-if-have-account').parents('tr').show();
						$('#ignitefeedbacksettingsform #submit').val('Sign Up (FREE FOR LIFE!)');
						$('input#terms_and_conditions').attr('required');
						$('input#terms_and_conditions').show();
						$('tr.terms-and-conditions').show();
					}
				});
				$("#ignitefeedbacksettingsform").submit(function( event ) {
					event.preventDefault();
					if ($("#ignitefeedbacksettingsform").valid()){
						var $url = '<?php echo admin_url( 'admin-ajax.php' );?>?action=connect_to_ignitefeedback_api';
						var $admin_email = $('input#admin_email').val();
						var $first_name = $('input#first_name').val();
						var $last_name = $('input#last_name').val();
						var $organization_name = $('input#organization_name').val();
						var $organization_domain = $('input#organization_domain').val();
						var $has_account = $('input[type=checkbox]').prop('checked');
						if($has_account){
							$has_account = 'yes';
						} else {
							$has_account = 'no';
						}
						var request = $.ajax({
							url: $url,
							type: "POST",
							data: { admin_email : $admin_email, first_name: $first_name, last_name: $last_name, organization_name: $organization_name, organization_domain: $organization_domain, has_account: $has_account },
							dataType: "html"
						});
						 
						request.done(function( msg ) {
							var msg = $.parseJSON(msg);
							if(msg.type == 'error'){
								$('#api-response-message-container').addClass('error');
								$('#api-response-message-container').removeClass('updated');
							} else {
								$('#api-response-message-container').addClass('updated');
								$('#api-response-message-container').removeClass('error');
							}
							if(msg.replace == 'replace'){
								$('#ignitefeedbacksettingsform').replaceWith('<div style="font-size:22px;margin:0px auto; width:50%;text-align:center;line-height:30px;margin-top:100px">'+msg.message+'</div>');
								console.log(msg.message);
							} else {
								$('#api-response-message-container').html( msg.message );
								$('#api-response-message-container').fadeIn('fast', function () {
									if(msg.fade != 'nofade'){
										$(this).delay(5000).fadeOut('slow');
									}							
								});
							}
						});
						 
						request.fail(function( jqXHR, textStatus ) {
							$('#api-response-message-container').html('Looks like there was an error. Please try again or contact us for help');
							$('#api-response-message-container').addClass('error');
							$('#api-response-message-container').removeClass('updated');
							$('#api-response-message-container').fadeIn('fast', function () {
								if(msg.fade != 'nofade'){
									$(this).delay(5000).fadeOut('slow');
								}
							});
						});
					}
				});
			});
			</script>
		</div>
		<?php
	}

	/**
	 * Text field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Text field.
	 */

	public function text_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		$size = isset( $args['size'] ) ? $args['size'] : '50';
		$class = isset($args['class']) ? $args['class'] : '';
		$type = isset($args['type']) ? $args['type'] : 'text';

		$options = get_option( $menu );

		if ( !empty( $options[$id] ) ) {
			$current = $options[$id];
		} elseif(!empty($args['placeholder'])){
			$current = $args['placeholder'];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}

		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="%7$s" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s class="%6$s" required />', $id, $menu, $current, $size, $disabled, $class, $type );

		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		echo $html;

	}
	
	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function select_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
		
		$options = get_option( $menu );
		
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		
		$html = sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled );
		$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
		
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}
		$html .= sprintf( '</select>' );
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		
		echo $html;
	}

	/**
	 * Displays a multiple selectbox for a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function multiple_select_element_callback( $args ) {
		$html = '';
		foreach ($args as $id => $boxes) {
			$menu = $boxes['menu'];
			
			$options = get_option( $menu );
			
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $boxes['default'] ) ? $boxes['default'] : '';
			}
			
			$disabled = (isset( $boxes['disabled'] )) ? ' disabled' : '';
			
			$html .= sprintf( '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>', $menu, $id, $disabled);
			$html .= sprintf( '<option value="%s"%s>%s</option>', '0', selected( $current, '0', false ), '' );
			
			foreach ( (array) $boxes['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
			$html .= '</select>';
	
			if ( isset( $boxes['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $boxes['description'] );
			}
			$html .= '<br />';
		}
		
		
		echo $html;
	}

	/**
	 * Checkbox field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu = isset($args['menu']) ? $args['menu'] : '';
		$id = isset($args['id']) ? $args['id'] : '';
		$class = isset($args['class']) ? $args['class'] : '';
		$required = isset($args['required']) ? $args['required'] : '';
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s class="%5$s" %6$s />', $id, $menu, checked( 1, $current, false ), $disabled, $class, $required );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
	
		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array   $args settings field args
	 */
	public function radio_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
		$html = '';
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $menu, $id, $key, checked( $current, $key, false ) );
			$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
		}
		
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
		echo $html;
	}

	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section_options_callback() {
	
	}

	/**
	 * Validate/sanitize options input
	 */
	public function ignitefeedback_options_validate( $input ) {
		// Create our array for storing the validated options.
		$output = array();
		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings.
				$output[$key] = strip_tags( stripslashes( $input[$key] ) );
			}
		}
		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'ignitefeedback_validate_input', $output, $input );
	}
}