<?php
/* auth.php Azure AD oAuth Class
 *
 * Katy Nicholson, last updated 17/11/2021
 *
 * https://github.com/CoasterKaty
 * https://katytech.blog/
 * https://twitter.com/coaster_katy
 *
 *
 *
 * Modified by Luca Hasler
 * - use Session, not MySQL
 * - returnJSON option: return "401" as JSON if auth needed, client then needs to call again for the Auth
 */

//require_once  'mysql.php';
require_once 'config.inc';
require_once 'oauth.inc';
require_once 'session.inc';

class modAuth {
    var $Token;
    var $userData;
    var $userName;
    var $oAuthVerifier;
    var $oAuthChallenge;
    var $oAuthChallengeMethod;
    var $userRoles;
    var $isLoggedIn;
    var $oAuth;

    function __construct($returnJSON = false) {  //$returnJSON: output as JSON; default is header
	    $allowAnonymous=0;
        if (session_status() == PHP_SESSION_NONE) session_start_mod();
		$this->oAuth = new modOAuth();
        $url = _URL . $_SERVER['REQUEST_URI'];

        // check session key against database. If it's expired or doesnt exist then forward to Azure AD
        if (isset($_SESSION['sessionkey'])) {
            // see if it's still valid. Expiry date doesn't mean that we can't just use the refresh token, so don't test this here
            $res=$_SESSION['data'];
            $this->oAuthVerifier = $res['txtCodeVerifier'];
            $this->oAuthChallenge();
            if (!$res || !$res['txtIDToken']) {
                //not in DB or empty id token field
                unset($_SESSION['sessionkey']);
                session_destroy();
                if($returnJSON)echo returnoutput("401");
	            else header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
            if ($_GET['action'] == 'logout') {
                // Logout action selected, clear from database and browser cookie, redirect to logout URL
                unset($_SESSION['data']);
                unset($_SESSION['sessionkey']);
                session_destroy();
                //header('Location: ' . _OAUTH_LOGOUT);
                exit;
            }
            //if (strtotime($res['dtExpires']) < strtotime('+10 minutes')) {
            if (strtotime($res['dtExpires']) < strtotime('+10 minutes')) {
                //attempt token refresh
                if ($res['txtRefreshToken']) {
    	            $oauthRequest = $this->oAuth->generateRequest('grant_type=refresh_token&refresh_token=' . $res['txtRefreshToken'] . '&client_id=' . _OAUTH_CLIENTID . '&scope=' . _OAUTH_SCOPE);
	            $response = $this->oAuth->postRequest('token', $oauthRequest);
                    $reply = json_decode($response);
                    if ($reply->error) {
                        if(substr($reply->error_description, 0, 12) == 'AADSTS70008:') {
                            //refresh token expired
                            $_SESSION['data']['txtRedir']=$url;
                            $_SESSION['data']['txtRefreshToken']='';
                            $_SESSION['data']['dtExpires']=date('Y-m-d H:i:s', strtotime('+5 minutes'));
                            
                            $oAuthURL = 'https://login.microsoftonline.com/' . _OAUTH_TENANTID . '/oauth2/v2.0/' . 'authorize?response_type=code&client_id=' . _OAUTH_CLIENTID . '&redirect_uri=' . urlencode(_URL . _DIR .  '/oauth.php') . '&scope=' . _OAUTH_SCOPE . '&code_challenge=' . $this->oAuthChallenge . '&code_challenge_method=' . $this->oAuthChallengeMethod;
                            if($returnJSON)echo returnoutput("401");
	                        else header('Location: ' . $oAuthURL);
                            exit;
                        }
						if($returnJSON)echo returnoutput("401");
	                    else echo $this->oAuth->errorMessage($reply->error_description)."ERROR 1";
						exit;
                    }
				    $idToken = base64_decode(explode('.', $reply->id_token)[1]);
                    $_SESSION['data']['txtToken']=$reply->access_token;
                    $_SESSION['data']['txtRefreshToken']=$reply->refresh_token;
                    $_SESSION['userName']=$idToken->preferred_username;
                    $_SESSION['data']['txtIDToken']=$idToken;
                    $_SESSION['data']['txtRedir']='';
                    $_SESSION['data']['dtExpires']=date('Y-m-d H:i:s', strtotime('+' . $reply->expires_in . ' seconds'));
				    
	    		    $res['txtToken'] = $reply->access_token;
                }
            }
            //Populate userData and userName from the JWT stored in the session.
            $this->Token = $res['txtToken'];
		    if ($res['txtIDToken']) {
				$idToken = json_decode($res['txtIDToken']);
				$this->userName = $idToken->preferred_username;
                $_SESSION['userName']=$idToken->preferred_username;
				if (isset($idToken->roles)) {
					$this->userRoles = $idToken->roles;
				} else {
					$this->userRoles = array('Default Access');
				}
		    }
		    $this->isLoggedIn = 1;
        }else{
		    if (!$allowAnonymous || $_GET['login'] == '1') {
	                // Generate the code verifier and challenge
	                $this->oAuthChallenge();
	                // Generate a session key and store in cookie, then populate session
	                $sessionKey = $this->uuid();
	                $_SESSION['sessionkey'] = $sessionKey;
	                $_SESSION['data']['txtSessionKey']=$sessionKey;
	                $_SESSION['data']['txtRedir']=$url;
	                $_SESSION['data']['txtCodeVerifier']=$this->oAuthVerifier;
	                $_SESSION['data']['dtExpires']=date('Y-m-d H:i:s', strtotime('+5 minutes'));
	
	                // Redirect to Azure AD login page
	                $oAuthURL = 'https://login.microsoftonline.com/' . _OAUTH_TENANTID . '/oauth2/v2.0/' . 'authorize?response_type=code&client_id=' . _OAUTH_CLIENTID . '&redirect_uri=' . urlencode(_URL . _DIR . '/oauth.php') . '&scope=' . _OAUTH_SCOPE . '&code_challenge=' . $this->oAuthChallenge . '&code_challenge_method=' . $this->oAuthChallengeMethod;
	                if($returnJSON)echo returnoutput("401");
	                else header('Location: ' . $oAuthURL);
	                exit;
		    }
        }
    }

/*  Function not used: remove
    function checkUserRole($role) {
	// Check that the requested role has been assigned to the user
		if (in_array($role, $this->userRoles)) {
		    return 1;
		}
		return;
    }
*/

    function uuid() {
        //uuid function is not my code, but unsure who the original author is. KN
        //uuid version 4
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    function oAuthChallenge() {
        // Function to generate code verifier and code challenge for oAuth login. See RFC7636 for details. 
        $verifier = $this->oAuthVerifier;
        if (!$this->oAuthVerifier) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-._~';
            $charLen = strlen($chars) - 1;
            $verifier = '';
            for ($i = 0; $i < 128; $i++) {
                $verifier .= $chars[mt_rand(0, $charLen)];
            }
            $this->oAuthVerifier = $verifier;
        }
        // Challenge = Base64 Url Encode ( SHA256 ( Verifier ) )
        // Pack (H) to convert 64 char hash into 32 byte hex
        // As there is no B64UrlEncode we use strtr to swap +/ for -_ and then strip off the =
        $this->oAuthChallenge = str_replace('=', '', strtr(base64_encode(pack('H*', hash('sha256', $verifier))), '+/', '-_'));
        $this->oAuthChallengeMethod = 'S256';
    }
}
?>
