<?php // Simple File List Script: ee-email-settings.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.23.2019
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loading Email Settings Page ...';

// Check for POST and Nonce
if(@$_POST['eePost'] AND check_admin_referer( 'ee-simple-file-list-settings', 'ee-simple-file-list-settings-nonce')) {
	
	// Get all the settings
	$eeSettings = get_option('eeSFL-Settings');
	
	$eeID = $eeSFL_Config['ID'];

	// YES/NO Checkboxes
	$eeCheckboxes = array(
		'Notify'
	);
	foreach( $eeCheckboxes as $eeTerm){
		$eeSettings[$eeID][$eeTerm] = eeSFL_ProcessCheckboxInput($eeTerm);
	}
	
	// Notifications
	$eeDelivery = array('To', 'Cc', 'Bcc');
	
	foreach( $eeDelivery as $eeKey => $eeValue) {
	
		$eeSFL_To = @$_POST['eeNotify' . $eeValue];
			
		if(strpos($eeSFL_To, ',')) { // Multiple Addresses
		
			$eeSFL_Addresses = explode(',', $eeSFL_To); // Make array
			
			$eeSFL_AddressesString = '';
			
			foreach($eeSFL_Addresses as $add){
				
				$add = trim($add);
				
				if(filter_var($add, FILTER_VALIDATE_EMAIL)) {
			
					$eeSFL_AddressesString .= $add . ',';
				} else {
					$eeSFL_Log['errors'][] = $add . ' - ' . __('This is not a valid email address.', 'ee-simple-file-list');
				}
			}
			
			$eeSettings[$eeID]['Notify' . $eeValue] = substr($eeSFL_AddressesString, 0, -1); // Remove last comma
			
		
		} elseif(filter_var(@$_POST['eeNotify' . $eeValue], FILTER_SANITIZE_EMAIL)) { // Only one address
			
			$add = @$_POST['eeNotify' . $eeValue];
			
			if(filter_var($add, FILTER_VALIDATE_EMAIL)) {
				$eeSettings[$eeID]['Notify' . $eeValue] = $add;
			} else {
				$eeSFL_Log['errors'][] = $add . ' - ' . __('This is not a valid email address.', 'ee-simple-file-list');
			}
			
		} else {
			
			$eeSettings[$eeID]['Notify' . $eeValue] = ''; // Anything but a good email gets null.
		}
	}
	
	// Message Options
	$eeTextInputs = array(
		'NotifyFrom'
		,'NotifyFromName'
		,'NotifySubject'
		,'NotifyMessage'
	);
	foreach( $eeTextInputs as $eeTerm){
		$eeSettings[$eeID][$eeTerm] = eeSFL_ProcessTextInput($eeTerm);
	}
	
	// Update DB
	update_option('eeSFL-Settings', $eeSettings );
	
	// Update the array with new values
	$eeSFL_Config = $eeSettings[$eeID];
	
	$eeSFL_Confirm = __('Notification Settings Saved', 'ee-simple-file-list');
}



// Settings Display =========================================
	
$eeOutput .= '<div class="eeSFL_Admin">';
	
if(@$eeSFL_Log['errors']) { 
	$eeOutput .=  eeSFL_ResultsDisplay($eeSFL_Log['errors'], 'notice-error');
} elseif(@$eeSFL_Confirm) { 
	$eeOutput .=  eeSFL_ResultsDisplay($eeSFL_Confirm, 'notice-success');
}

// Begin the Form	
$eeOutput .= '

<form action="' . $_SERVER['PHP_SELF'] . '?page=' . $eeSFL->eePluginSlug . '&tab=settings&subtab=email_settings" method="post" id="eeSFL_Settings">

	<p class="eeSettingsRight"><a class="eeInstructionsLink" href="https://simplefilelist.com/" target="_blank">' . __('Instructions', 'ee-simple-file-list') . '</a>
		<input type="submit" name="submit" value="' . __('SAVE', 'ee-simple-file-list') . '" class="button eeSFL_Save" /></p>
		
	<input type="hidden" name="eePost" value="TRUE" />
	
	<h2>' . __('Notifications', 'ee-simple-file-list') . '</h2>';	
	
	$eeOutput .= wp_nonce_field( 'ee-simple-file-list-settings', 'ee-simple-file-list-settings-nonce', TRUE, FALSE);
	
	if(strlen($eeSFL_Config['NotifyTo']) < 5) {
		$eeSFL_Config['NotifyTo'] = get_option('admin_email');
	}
	if(strlen($eeSFL_Config['NotifyFrom']) < 5) {
		$eeSFL_Config['NotifyFrom'] = get_option('admin_email');
	}
	
	$eeOutput .= '<fieldset>
	
	<label for="eeNotify">' . __('Send Notification', 'ee-simple-file-list') . ':</label><input type="checkbox" name="eeNotify" value="YES" id="eeNotify"'; 
	if(@$eeSFL_Config['Notify'] == 'YES') { $eeOutput .= ' checked'; }
	$eeOutput .= ' /> <div class="eeNote">' . __('Send an email notification when a file is uploaded on the front-side of the website.', 'ee-simple-file-list') . '</div>
	
	<hr />
	
	<label for="eeNotifyTo">' . __('Notice Email', 'ee-simple-file-list') . ':</label>
	<input type="text" name="eeNotifyTo" value="' . @$eeSFL_Config['NotifyTo'] . '" class="eeAdminInput" id="eeNotifyTo" size="64" />
		<div class="eeNote">' . __('Send an email whenever a file is uploaded or changed.', 'ee-simple-file-list') . ' ' .  __('Separate multiple addresses with a comma.', 'ee-simple-file-list') . '</div>
	
	
	<label for="eeNotifyCc">' . __('Copy to Email', 'ee-simple-file-list') . ':</label>
	<input type="text" name="eeNotifyCc" value="' . @$eeSFL_Config['NotifyCc'] . '" class="eeAdminInput" id="eeNotifyCc" size="64" />
		<div class="eeNote">' . __('Copy notice emails here.', 'ee-simple-file-list') . '</div>
	
	
	<label for="eeNotifyBcc">' . __('Blind Copy to Email', 'ee-simple-file-list') . ':</label>
	<input type="text" name="eeNotifyBcc" value="' . @$eeSFL_Config['NotifyBcc'] . '" class="eeAdminInput" id="eeNotifyBcc" size="64" />
		<div class="eeNote">' . __('Blind copy notice emails here.', 'ee-simple-file-list') . '</div>
	
	<br class="eeClearFix" />	
	
	<h3>Message Options</h3>
	
	<label for="eeNotifyFrom">' . __('Sender Email', 'ee-simple-file-list') . ':</label>
	<input type="email" name="eeNotifyFrom" value="' . @$eeSFL_Config['NotifyFrom'] . '" class="eeAdminInput" id="eeNotifyFrom" size="64" />
		<div class="eeNote">' . __('The notification message\'s reply-to address.', 'ee-simple-file-list') . '</div>
	
	
	<label for="eeNotifyFromName">' . __('Sender Name', 'ee-simple-file-list') . ':</label>
	<input type="text" name="eeNotifyFromName" value="' . stripslashes(@$eeSFL_Config['NotifyFromName']) . '" class="eeAdminInput" id="eeNotifyFromName" size="64" />
		<div class="eeNote">' . __('The visible name in the From field.', 'ee-simple-file-list') . '</div>
	
	
	<label for="eeNotifySubject">' . __('Notification Subject', 'ee-simple-file-list') . ':</label>
	<input type="text" name="eeNotifySubject" value="' . stripslashes(@$eeSFL_Config['NotifySubject']) . '" class="eeAdminInput" id="eeNotifySubject" size="64" />
		<div class="eeNote">' . __('The notification email subject line.', 'ee-simple-file-list') . '</div>';
		
	
	if(!@$eeSFL_Config['NotifyMessage']) { $eeSFL_Config['NotifyMessage'] = $eeSFL->eeNotifyMessageDefault; }
	
	$eeOutput .= '<label for="eeNotifyMessage">' . __('Message Text', 'ee-simple-file-list') . ':</label>
	<textarea name="eeNotifyMessage" class="eeAdminInput" id="eeNotifyMessage" cols="64" rows="12" >' . stripslashes($eeSFL_Config['NotifyMessage']) . '</textarea>
		<div class="eeNote">' . __('This will be the text for the file upload notification messages.', 'ee-simple-file-list') . '<br />
			' . __('To insert file information and link, use this shortcode:', 'ee-simple-file-list') . ' [file-list]<br />
			' . __('To insert a link pointing to the file list, use this shortcode:', 'ee-simple-file-list') . ' [web-page]</div>
	
	';
	
	
	$eeOutput .= '<br class="eeClearFix" />
	
	<input type="submit" name="submit" value="' . __('SAVE', 'ee-simple-file-list') . '" class="button eeSFL_Save" />
	
	</fieldset>
	
</form>
	
</div>';
	
?>