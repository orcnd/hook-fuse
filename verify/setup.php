<?php
/**
 * Playground setup for the verification pass (not shipped in the zip).
 *
 * Builds the situation from the WordPress.org support thread "Fatal error with WPForms Email Confirmation
 * field (array passed to strlen)": Meta pixel for WordPress with a pixel ID and automatic matching on, and a
 * WPForms form whose email field has confirmation enabled. The access token stays empty so no request
 * leaves the sandbox.
 *
 * @package HookFuse
 */

require '/wordpress/wp-load.php';

update_option(
	'facebook_business_extension_config',
	array(
		'facebook_pixel_id'         => '1234567890',
		'facebook_access_token'     => '',
		'facebook_is_fbe_installed' => '1',
	)
);
set_transient(
	'facebook_pixel_aam_settings',
	array(
		'pixelId'                        => '1234567890',
		'enableAutomaticMatching'        => true,
		'enabledAutomaticMatchingFields' => array( 'em', 'fn', 'ln', 'ph', 'ct', 'st', 'zp', 'country' ),
	),
	YEAR_IN_SECONDS
);

$form = array(
	'field_id' => 2,
	'fields'   => array(
		'0' => array(
			'id'       => '0',
			'type'     => 'name',
			'label'    => 'Name',
			'format'   => 'simple',
			'required' => '1',
		),
		'1' => array(
			'id'           => '1',
			'type'         => 'email',
			'label'        => 'Email',
			'required'     => '1',
			'confirmation' => '1',
		),
	),
	'settings' => array(
		'form_title'          => 'Contact',
		'submit_text'         => 'Send',
		'antispam'            => '0',
		'antispam_v3'         => '0',
		'ajax_submit'         => '0',
		'notification_enable' => '0',
		'confirmations'       => array(
			'1' => array(
				'type'    => 'message',
				'message' => 'Thanks, your message was received.',
			),
		),
	),
);

$form_id    = wp_insert_post(
	array(
		'post_type'   => 'wpforms',
		'post_status' => 'publish',
		'post_title'  => 'Contact',
	)
);
$form['id'] = $form_id;
wp_update_post(
	array(
		'ID'           => $form_id,
		'post_content' => wp_slash( wp_json_encode( $form ) ),
	)
);

wp_insert_post(
	array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Contact',
		'post_name'    => 'contact',
		'post_content' => '[wpforms id="' . $form_id . '"]',
	)
);

update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules();
echo esc_html( "form {$form_id} on /contact/" ) . "\n";
