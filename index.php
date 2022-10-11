<?php
		include("inc/db.inc");
		error_reporting(-1);
sleep(2);
$stationen=json_decode('{"2657":"km 26.57","ADZ":"Ardez","ALGR":"Alp Gr\u00fcm","ALPN":"Alp Nouva","ALV":"Alvaneu","AROS":"Arosa","BEBR":"Berg\u00fcn\/Bravuogn","BEDI":"Bernina Diavolezza","BELA":"Bernina Lagalb","BESU":"Bernina Suot","BEV":"Bever","BONA":"Bonaduz","BRIO":"Brusio","CADE":"Cadera","CAIO":"Campascio","CAMP":"Campocologno","CAPA":"Cap\u00e4ls","CARO":"Carolina","CASR":"Castrisch","CAVA":"Cavaglia","CAZI":"Cazis","CDUE":"Cavad\u00fcrli","CELE":"Celerina","CELS":"Celerina Staz","CH":"Chur","CHGB":"Chur GB","CHSA":"Chur Altstadt","CHWE":"Chur West","CHWI":"Chur Wiesental","CIN":"Cinuos-chel-Brail","CURT":"Li Curt","DAD":"Davos Dorf","DAF":"Davos Frauenkirch","DAGL":"Davos Glaris","DALT":"Davos Laret","DAM":"Davos Monstein","DAV":"Davos Platz","DAW":"Davos Wolfgang","DE":"Domat\/Ems","DEPS":"Depot Sand","DIS":"Disentis\/Must\u00e9r","EMSW":"Ems Werk","FBG":"Felsberg","FID":"Fideris","FILI":"Filisur","FTAN":"Ftan Baraigla","FURN":"Furna","FUWI":"Fuchsenwinkel","GRUS":"Gr\u00fcsch","GUA":"Guarda","HALD":"Haldenstein","HGR":"Haspelgrube","IGIS":"Igis","ILZ":"Ilanz","JAZ":"Jenaz","KBOD":"Kalter Boden","KLO":"Klosters Platz","KLOD":"Klosters Dorf","KUEB":"K\u00fcblis","LAPU":"La Punt Chamues-ch","LAVI":"Lavin","LAWI":"Langwies","LITZ":"Litzir\u00fcti","LQ":"Landquart","LQGB":"Landquart GB","LQRI":"Landquart Ried","LUCA":"L\u00fcen-Castiel","MADU":"Madulain","MALA":"Malans","MIRA":"Miralago","MOTA":"Morteratsch","MROS":"T\u00e4sch Monte Rosa","MUOT":"Muot","OSBE":"Ospizio Bernina","PEI":"Peist","PORE":"Pontresina","POS":"Poschiavo","POSN":"Poschiavo Nord","PRAD":"Pradei","PRED":"Preda","PRES":"Le Prese","PRET":"Le Prese (incrocio)","PTM":"Punt Muragl","PTMS":"Punt Muragl Staz","RAND":"Randa","RASU":"Rabius-Surrein","REIT":"Reichenau-Tamins","RHAE":"Rh\u00e4z\u00fcns","RORE":"Rodels-Realta","ROTH":"Rothenbrunnen","RUEU":"Rueun","SAAS":"Saas im Pr\u00e4ttigau","SAGL":"Sagliains","SAME":"Samedan","SAST":"Sagliains Abzw. Sasslatsch","SCAN":"S-chanf","SCRS":"Schiers","SCTA":"Scuol-Tarasp","SEEV":"Seewis-Pardisla","SELF":"Klosters Selfranga","SERN":"Serneus","SFM":"S-chanf Marathon","SMOR":"St. Moritz","SOLI":"Solis","SPIN":"Spinas","SST":"Schnaus-Strada","STAK":"Stablini","STGL":"Stugl\/Stuls","STPM":"St. Peter-Molinis","SUCU":"Sumvitg-Cumpadials","SURA":"Surava","SURO":"Surovas","SUS":"Susch","TABR":"Tavanasa-Breil\/Brigels","TAES":"T\u00e4sch","TASS":"T\u00e4schsand","THS":"Thusis","TICA":"Tiefencastel","TIR":"Tirano","TRIN":"Trin","TRUN":"Trun","USAX":"Untersax","UVAZ":"Untervaz-Trimmis","VASA":"Valendas-Sagogn","VESA":"Versam-Safien","VNOR":"Vereina Nord","VSUD":"Vereina S\u00fcd","WAVU":"Waltensburg\/Vuorz","WIES":"Davos Wiesen","ZALT":"Zizers Altl\u00f6ser","ZEGB":"Zermatt GB","ZERM":"Zermatt","ZEZ":"Zernez","ZIZ":"Zizers","ZUOZ":"Zuoz","SAME_BW":"Samedan","LQ_PZ":"Landquart","LQ_BW":"Landquart","CH_GB":"Chur GB"}',true);
//echo "\n\n".$stationen['ZERM'];
function ort($k){
	if(!is_string($k))return"";
	if(!isset($_GET['ausschreiben']))return $k;
	global $stationen;
	if(isset($stationen[$k]))return $stationen[trim($k)];
	//echo "!-".$k."-!";
	return $k;
}
function pdstime($m,$end){
	if($end)$m--;
	$hours=floor($m/60);
	$min=$m-($hours*60);
	if($end)return str_pad($hours, 2, "0", STR_PAD_LEFT).str_pad($min, 2, "0", STR_PAD_LEFT)."59";
	return str_pad($hours, 2, "0", STR_PAD_LEFT).str_pad($min, 2, "0", STR_PAD_LEFT)."00";}
function zugart($txt){
	if(!isset($_GET['ausschreiben']))return $txt;
	$nr=trim(explode(" ", $txt)[1]);
	//print_r(":-".$nr."-:");
	if(strpos($nr, "R"))return "Rangier ".$nr;
	if(is_numeric($nr)){
		if($nr>=100 && $nr<490)return "Autozug ".$nr; 
		if($nr>=900 && $nr<950)return "Glacier Express ".$nr; 
		if($nr>=950 && $nr<1000)return "Bernina Express ".$nr; 
		if($nr>=1500 && $nr<1600)return "S-Bahn ".$nr; 
		if($nr>=1000 && $nr<2000)return "Zug ".$nr;//return "Reisezug ".$nr; 
		if($nr>=2000 && $nr<3000)return "Extrazug ".$nr; 
		if($nr>=4000 && $nr<5000)return "Zug ".$nr;//return "Güterzug mit Personenbeförderung ".$nr; 
		if($nr>=5000 && $nr<6000)return "Güterzug ".$nr; 
		if($nr>=6000 && $nr<7000)return "Güterzug ".$nr;//return "GüterExtrazug ".$nr; 
		if($nr>=7800 && $nr<7900)return "Zug ".$nr;//return "Reisezug ".$nr; 
		if($nr>=7000 && $nr<8000)return "Umgeleiteter Zug ".$nr; 
		if($nr>=8000 && $nr<9000)return "Dienstzug ".$nr; 
		if($nr>=9000 && $nr<=9999)return "Dienstzug ".$nr; 
	}
	return $txt;
}

function str_encode_ical($str){
	return str_replace(",", "\,", str_replace(";", "\;", $str));
}

function delete_all_between($beginning, $end, $string) {
	$beginningPos = strpos($string, $beginning);
	$endPos = strpos($string, $end);
	if ($beginningPos === false || $endPos === false) return $string;
	$textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
	return delete_all_between($beginning, $end, str_replace($textToDelete, '', $string)); // recursion
}



	$sql="SELECT * FROM daten WHERE username = ? and calkey = ?";
	$statement = $mysqli->prepare($sql);
	$statement->bind_param('ss', $_GET['username'],$_GET['key']);
	$statement->execute();
	$result = $statement->get_result();
	$data = $result->fetch_array();
	if($data['username']==0 || $data['username']==""){
		sleep(8);
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}
	if(!isset($_GET['debug']) && !isset($_GET['raw'])){
		header("content-type:text/calendar");
		header("accept-ranges:bytes");
		//header("content-length:".filesize($filename));
	}else{
		echo "<pre>";
		//print_r($data);
	}
	$dienste=json_decode($data['dienste']);
	$dienstdaten=json_decode($data['dienstdaten']);
	//print_r($dienste); exit();
	$events="BEGIN:VCALENDAR\n";
	$uid=1;
	if(!isset($_GET['alles'])){
		foreach($dienste->data->shift as $dst){
			if($dst->startTime==0 && $dst->endTime==1440)continue;
			if(isset($_GET['debug']))print_r($dst);

			$events.="BEGIN:VEVENT\nUID:$uid@default\nCLASS:PUBLIC\n";
			$events.="SUMMARY:Dienst ".str_encode_ical(trim(explode("(Bez:", $dst->name)[0]))."\n";
			if($dst->startTime>=1440){
				$events.="DTSTART;TZID=Europe/Zurich:".(intval(str_replace("-", "",$dst->date))+1)."T".pdstime($dst->startTime-1440,false)."\n";
			}else{
				$events.="DTSTART;TZID=Europe/Zurich:".str_replace("-", "",$dst->date)."T".pdstime($dst->startTime,false)."\n";
			}
			if($dst->endTime>=1440){
				$events.="DTEND;TZID=Europe/Zurich:".(intval(str_replace("-", "",$dst->date))+1)."T".pdstime($dst->endTime-1440,true)."\n";
			}else{
				$events.="DTEND;TZID=Europe/Zurich:".str_replace("-", "",$dst->date)."T".pdstime($dst->endTime,true)."\n";
			}
			if($dst->signonPoint==$dst->signoffPoint){
				/*if(is_string($dst->signoffPoint))*/
				$events.="LOCATION:".ort($dst->signoffPoint)."\n";
			}else{
				/*if(is_string($dst->signonPoint) && is_string($dst->signoffPoint))*/
				$events.="LOCATION:".ort($dst->signonPoint)." - ".ort($dst->signoffPoint)."\n";
			}
			$events.="DTSTAMP:20220620T190609\n";
			$events.="TRANSP:TRANSPARENT\nEND:VEVENT\n";
			$uid++;
		}
	}if(isset($_GET['details'])){
		if(isset($_GET['debug'])){
			print_r($dienstdaten);
		}
		foreach($dienstdaten as $dst){
				//if(isset($_GET['debug']))print_r($dst->data->shiftDetail);
				foreach($dst->data->shiftDetail as $diel){
					$show=false;
					
					
	if(isset($_GET['alles']))$show=true;
	if(isset($_GET['zuege']) && (strpos($diel->workContent, "Zug")>-1 || strpos($diel->workContent, "Passagierfahrt")>-1 || strpos($diel->workContent, "Rangier")>-1 || strpos(trim($diel->workContent), "R ")>-1 || strpos(trim($diel->workContent), "Reserve")>-1))$show=true;
	if(isset($_GET['pausen']) && (trim($diel->workContent)=="P" || trim($diel->workContent)=="Pause" || trim($diel->workContent)=="PAUSE"))$show=true;
					
					
					
					
					if($show){
						$events.="BEGIN:VEVENT\nUID:$uid@default\nCLASS:PUBLIC\n";
						//$events.="DTSTART;TZID=Europe/Zurich:".str_replace("-", "",$diel->date)."T".pdstime($diel->startTime,false)."\n";
						if($diel->startTime>=1440){
							$events.="DTSTART;TZID=Europe/Zurich:".(intval(str_replace("-", "",$diel->date))+1)."T".pdstime($diel->startTime-1440,false)."\n";
						}else{
							$events.="DTSTART;TZID=Europe/Zurich:".str_replace("-", "",$diel->date)."T".pdstime($diel->startTime,false)."\n";
						}
						if($diel->endTime>=1440){
							$events.="DTEND;TZID=Europe/Zurich:".(intval(str_replace("-", "",$diel->date))+1)."T".pdstime($diel->endTime-1440,true)."\n";
						}else{
							$events.="DTEND;TZID=Europe/Zurich:".str_replace("-", "",$diel->date)."T".pdstime($diel->endTime,true)."\n";
						}
						if($diel->signonPoint==$diel->signoffPoint){
							$events.="LOCATION:".str_encode_ical(ort($diel->signoffPoint))."\n";
						}else{
							$events.="LOCATION:".str_encode_ical(ort($diel->signonPoint)." - ".ort($diel->signoffPoint))."\n";
						}
						if(strpos($diel->workContent, "Zug")>-1 || strpos($diel->workContent, "Passagierfahrt")>-1){
								//$tm=explode(" ", $diel->workContent);
								
							$events.="SUMMARY:".str_encode_ical(zugart($diel->workContent))."\n";
								
						}else{
							$events.="SUMMARY:".str_encode_ical($diel->workContent)."\n";
						}
						$events.="DTSTAMP:20220620T190609\n";
						if(is_string($diel->comments)){
							$comment=$diel->comments;
						}else{
							$comment="";
							foreach($diel->comments as $com){
								$comment.=$com."; ";
							}
						}
						$events.="DESCRIPTION:".str_encode_ical($comment)."\n"; 
						$events.="TRANSP:TRANSPARENT\nEND:VEVENT\n";
						$uid++;
					}
				}
			
		}
	}
	echo $events."END:VCALENDAR" ;
	
	?>