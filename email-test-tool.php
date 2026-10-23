<?php
/**
 * Plugin Name: Email Test Tool
 * Description: Send a test email from WordPress and see whether it was handed off successfully.
 * Version: 1.0.0
 * Author: Jen Zeigler
 * Author URI: https://github.com/jenzeigler
 * License: GPL-2.0-or-later
 * Text Domain: email-test-tool
 */

// Stop the file from running if someone opens it directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add an "Email Test" page under the Tools menu.
 */
add_action( 'admin_menu', 'ett_add_admin_page' );

function ett_add_admin_page() {
	add_management_page(
		'Email Test',          // Page title (browser tab).
		'Email Test',          // Menu label under Tools.
		'manage_options',      // Only administrators can see it.
		'email-test-tool',     // The page's slug in the URL.
		'ett_render_admin_page' // The function that draws the page.
	);
}

/**
 * Draw the page, and handle the form when it's submitted.
 */
function ett_render_admin_page() {
	// Double check the person is allowed to be here.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$result        = null; // null = form not sent yet, true = success, false = failure.
	$error_message = '';
	$to            = wp_get_current_user()->user_email; // Default to the admin's own address.

	// The form was submitted.
	if ( isset( $_POST['ett_send'] ) ) {
		// Security check: confirms the request came from this form.
		check_admin_referer( 'ett_send_test' );

		$to = isset( $_POST['ett_to'] ) ? sanitize_email( wp_unslash( $_POST['ett_to'] ) ) : '';

		if ( ! is_email( $to ) ) {
			$result        = false;
			$error_message = 'Please enter a valid email address.';
		} else {
			// If WordPress fails to send, capture the reason it gives.
			$capture_error = function ( $wp_error ) use ( &$error_message ) {
				$error_message = $wp_error->get_error_message();
			};
			add_action( 'wp_mail_failed', $capture_error );

			$subject = 'Test email from ' . get_bloginfo( 'name' );
			$message = "This is a test email sent by the Email Test Tool plugin.\n\n"
				. 'Site: ' . home_url() . "\n"
				. 'Sent: ' . current_time( 'mysql' ) . "\n\n"
				. 'If you received this, WordPress email is working for this address.';

			$result = wp_mail( $to, $subject, $message );

			remove_action( 'wp_mail_failed', $capture_error );
		}
	}
	?>
	<div class="wrap">
		<h1>Email Test</h1>
		<p>Send a test email to check whether WordPress can hand off email from this site.</p>

		<?php if ( true === $result ) : ?>
			<div class="notice notice-success">
				<p>
					<strong>WordPress handed the email off successfully.</strong>
					Check the inbox and spam folder for <?php echo esc_html( $to ); ?>.
				</p>
				<p>Note: this confirms the handoff, not delivery. If it never arrives, the problem is usually authentication (SPF, DKIM, DMARC) or the From address.</p>
			</div>
		<?php elseif ( false === $result ) : ?>
			<div class="notice notice-error">
				<p><strong>The email could not be sent.</strong></p>
				<?php if ( $error_message ) : ?>
					<p>Reason given: <?php echo esc_html( $error_message ); ?></p>
				<?php endif; ?>
				<p><?php echo esc_html( ett_get_hint( $error_message ) ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'ett_send_test' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="ett_to">Send test to</label></th>
					<td>
						<input type="email" id="ett_to" name="ett_to" class="regular-text" value="<?php echo esc_attr( $to ); ?>" required>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Send test email', 'primary', 'ett_send' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Pick the most helpful next step based on the error WordPress gave.
 */
function ett_get_hint( $error_message ) {
	// The From address WordPress is sending as isn't valid.
	if ( false !== stripos( $error_message, '(From)' ) ) {
		return "WordPress is sending from an address that isn't valid. Set a real From address on your domain, usually in your SMTP plugin's settings.";
	}

	// The server has no way to send mail at all.
	if ( false !== stripos( $error_message, 'Could not instantiate mail function' ) ) {
		return "This server can't send mail on its own. Setting up an SMTP plugin with a real sending service typically fixes it.";
	}

	// Fallback for errors we don't recognize yet.
	return "Check the reason above. Setting up an SMTP plugin with a real sending service fixes most WordPress email problems.";
}
