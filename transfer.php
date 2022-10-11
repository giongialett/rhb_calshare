<?php 
require_once 'inc/session.inc';
error_reporting(1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

function returnoutput($obj){
		echo "document.".$_GET['return']."('".$_GET['req']."','".json_encode($obj)."');";
		exit();
}
if (session_status() == PHP_SESSION_NONE) session_start_mod();

// if no userName is set in session: do auth, else already auth ok
	include("inc/db.inc");
	if($_GET['complete']=="true"){
		$_SESSION['buffer'].=$_GET['data'];
		//echo"<pre>";
		//print_r(json_decode($_SESSION['buffer']));
		$data=json_decode($_SESSION['buffer'],true);
		$_SESSION['buffer']="";
	}else{
		$_SESSION['buffer'].=$_GET['data'];
		returnoutput('200');
	}
	if(isset($_GET['username'])){
		if(isset($_GET['auth']) && $_GET['auth']!="" && isset($_GET['username']) && $_GET['username']!="" && $_GET['req']=="caldata"){
			$username=$_GET['username'];
			$auth=$_GET['auth'];
			$sql="SELECT * FROM daten WHERE username = ? and privkey = ?";
			$statement = $mysqli->prepare($sql);
			$statement->bind_param('ss', $username, $auth);
			$statement->execute();
			$result = $statement->get_result();
			$res= $result->fetch_array();
			$id=$res['username'];
			$calkey = $res['calkey'];
			if($id!=0 && $id!=""){
				if(isset($_GET['reset'])){
					$sql = "DELETE FROM daten WHERE username = ?";
					$statement = $mysqli->prepare($sql);
					$statement->bind_param('s', $id);
					$tmp="deleted";
				}else{
					$sql = "UPDATE daten SET dienste = ?, dienstdaten = ? WHERE username = ?";
					$statement = $mysqli->prepare($sql);
					$statement->bind_param('sss', json_encode($data['alleDienste']), json_encode($data['dienste']), $id);
					if($statement->execute()){
						$tarr=array();
						$tarr['key']=$calkey;
						$tmp=$tarr;
					}else{
						$tmp="error";
					}
				}
			}else{
				include 'inc/auth.inc';
				$Auth = new modAuth(true); //true: output as JSON, not as redirection header
				if($Auth->userName==""){
					returnoutput("401c");
				}
				if($Auth->userName==$_GET['username']){
					$sql="SELECT * FROM daten WHERE username = ?";
					$statement = $mysqli->prepare($sql);
					$statement->bind_param('s', $username);
					$statement->execute();
					$result = $statement->get_result();
					$res= $result->fetch_array();
					$id=$res['username'];
					$calkey = $res['calkey'];
					$privkey = $res['privkey'];
					if($id!=0 && $id!=""){
						$sql = "UPDATE daten SET dienste = ?, dienstdaten = ? WHERE username = ?";
						$statement = $mysqli->prepare($sql);
						$statement->bind_param('sss', json_encode($data['alleDienste']), json_encode($data['dienste']), $id);
						if($statement->execute()){
							$tarr=array();
							$tarr['key']=$calkey;
							$tarr['privkey']=$privkey;
							$tmp=$tarr;
						}else{
							$tmp="error";
						}			
					}else{
						$sql = "INSERT INTO daten (username, dienste, dienstdaten, calkey, privkey) VALUES (?,?,?,?,?)";
						$statement = $mysqli->prepare($sql);
						$calkey=(md5(md5($username.time()."giongialet")).md5($data['dienste']));
						$privkey=(md5(md5($username.time()."superbravo")).$calkey.md5($data['dienste']));
						$statement->bind_param('sssss', $username, json_encode($data['alleDienste']), json_encode($data['dienste']), $calkey,$privkey);
						if($statement->execute()){
							$tarr=array();
							$tarr['key']=$calkey;
							$tarr['privkey']=$privkey;
							$tmp=$tarr;
						}else{
							$tmp="error";
						}			
					}
				}	
			}
		}else{
			returnoutput("401a");
		}
	}else{
		returnoutput("401b");
	}
	returnoutput($tmp);

?>