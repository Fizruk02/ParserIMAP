<?php
include('func.php');
include('conf.php');
echo "<pre>";
$domain = $config['domain'];
$mail = $config['mail'];
$pass = $config['pass'];

$sqlIp = $mySql['ip'];
$sqlLog = $mySql['log'];
$sqlPass = $mySql['pass'];
$sqlBData = $mySql['bData'];
$imap = imap_open($domain, $mail, $pass) or die ('Cannot connect to '.$mail.': '.imap_last_error());;

$read = imap_search($imap, 'ALL');
$limit = count($read);

$unseen = imap_search($imap, 'UNANSWERED');
$countUnseen = count($unseen);

$seen = imap_search($imap, 'ANSWERED');
$countSeen = count($seen);

imap_close($imap);

$email = new Imap_parser();

$data = array(
	'email' => array(
		'hostname' => $domain,
		'username' => $mail,
		'password' => $pass
	),
	'pagination' => array(
		'sort' => 'ASC',
		'limit' => $limit,
		'offset' => 0
	)
);

$result = $email->inbox($data);
$inbox = $result['inbox'];
$countInbox = count($inbox);

$link = mysqli_connect($sqlIp, $sqlLog,$sqlPass, $sqlBData);
$res = mysqli_query($link, "SET names utf8");

for($i = 0; $i < $countInbox; $i++){
	$res = mysqli_query($link, "SELECT * FROM `infoMail`");
	while($Data = mysqli_fetch_assoc($res)){

		if((int)$inbox[$i]['id'] == (int)$Data['idMsg']){
			$inbox[$i]['bool'] = true;
			break;
		}
		else
			$inbox[$i]['bool'] = false;
	}

	if(!$inbox[$i]['bool']){
		$idMsg = $inbox[$i]['id'];
		$subject = $inbox[$i]['subject'];
		$from = $inbox[$i]['from'];
		$email = $inbox[$i]['email'];
		$date = convertDate($inbox[$i]['date']);
		$message = $inbox[$i]['message'];

		mysqli_query($link, "INSERT INTO `infoMail` SET 
			`idMsg` = $idMsg,
			`subject` = '$subject',
			`from` = '$from',
			`email` = '$email',
			`date` = '$date',
			`message` = '$message'
			");
	}
}

for($i = 0; $i < $countUnseen; $i++){
	$res = mysqli_query($link, "SELECT `idMsg` FROM `infoMail`");
	while($Data = mysqli_fetch_assoc($res)){
		if($Data['idMsg'] == $unseen[$i]){
			$id = $unseen[$i];
			mysqli_query($link, "UPDATE `infoMail` SET `answered` = 'no' WHERE `idMsg` = '$id'");
		}
	}
}

for($i = 0; $i < $countSeen; $i++){
	$res = mysqli_query($link, "SELECT `idMsg` FROM `infoMail`");
	while($Data = mysqli_fetch_assoc($res)){
		if($Data['idMsg'] == $seen[$i]){
			$id = $seen[$i];
			mysqli_query($link, "UPDATE `infoMail` SET `answered` = 'yes' WHERE `idMsg` = '$id'");
		}
	}
}

mysqli_close($link);
