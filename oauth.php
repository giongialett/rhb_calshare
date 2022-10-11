<?php
/* oauth.php Azure AD oAuth web callback script
 *
 * Katy Nicholson, last updated 17/11/2021
 *
 * https://github.com/CoasterKaty
 * https://katytech.blog/
 * https://twitter.com/coaster_katy
 *
 */
//require_once 'dienst_app/inc/mysql.php';
require_once 'inc/config.inc';
require_once 'inc/oauth.inc';
require_once 'inc/session.inc';


session_start_mod();
//$modDB = new modDB();
$oAuth = new modOAuth();
if (isset($_GET['error']) && $_GET['error']) {
	echo $oAuth->errorMessage($_GET['error_description']);
	exit;
}
//retrieve session data from database

if (isset($_SESSION['data']) && $_SESSION['data']) {
	$sessionData = $_SESSION['data'];
	
	// Request token from Azure AD
	//file_put_contents("log_oauth.txt", file_get_contents("log_oauth.txt")."\n\n\nRequest: ".$_SERVER['HTTP_REFERER'].$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
	if(!isset( $_GET['code'])){
		print_r($_SERVER);
		print_r($_SESSION);
		exit;
	}
	$oauthRequest = $oAuth->generateRequest('grant_type=authorization_code&client_id=' . _OAUTH_CLIENTID . '&redirect_uri=' . urlencode(_URL . _DIR . '/oauth.php') . '&code=' . $_GET['code'] . '&code_verifier=' . $sessionData['txtCodeVerifier'].'&scope=' . _OAUTH_SCOPE);
	
	$response = $oAuth->postRequest('token', $oauthRequest);

	// Decode response from Azure AD. Extract JWT data from supplied access_token and id_token and update database.
	if (!$response) { 
		echo $oAuth->errorMessage('Unknown error acquiring token');
		exit;
	}
	$reply = json_decode($response);
	if ($reply->error) {
	        echo $oAuth->errorMessage($reply->error_description);
		exit;
	}

	$idToken = base64_decode(explode('.', $reply->id_token)[1]);
	//$modDB->Update('tblAuthSessions', array('txtToken' => $reply->access_token, 'txtRefreshToken' => $reply->refresh_token, 'txtIDToken' => $idToken, 'txtRedir' => '', 'dtExpires' => date('Y-m-d H:i:s', strtotime('+' . $reply->expires_in . ' seconds'))), array('intAuthID' => $sessionData['intAuthID']));
	$_SESSION['data']['txtToken']=$reply->access_token;
	$_SESSION['data']['txtRefreshToken']=$reply->refresh_token;
	$_SESSION['data']['txtIDToken']=$idToken;
	$_SESSION['data']['txtRedir']='';
	$_SESSION['data']['dtExpires']=date('Y-m-d H:i:s', strtotime('+' . $reply->expires_in . ' seconds'));
	
	// Redirect user back to where they came from.
	header('Location: ' . $sessionData['txtRedir']);
} else {
	header('Location: /');
}
?>
