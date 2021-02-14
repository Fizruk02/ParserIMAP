<?
include('conf.php');
include('func.php');
include('conf.php');

if($_GET['send'] == 'Искать')
{
	/*-------------------------------------*/
	$email = $_GET['email'];
	if($email == true)
		$pd = true;
	/*-------------------------------------*/
	$numStr = $_GET['numStr'];
	if($numStr == true)
	{
		$numList = (int)$numStr-1;
		$numLimit = $numList*10;
	}
	else
	{
		$numList = 0;
		$numLimit = 0;
	}
	/*-------------------------------------*/
	$date1 = $_GET['date1'];
	$date2 = $_GET['date2'];
	if(($date1 == true) and ($date2 == true))
		$dataBool = true;
	else
		$dataBool = false;
	/*-------------------------------------*/
}

$sqlIp = $mySql['ip'];
$sqlLog = $mySql['log'];
$sqlPass = $mySql['pass'];
$sqlBData = $mySql['bData'];

$link = mysqli_connect($sqlIp, $sqlLog,$sqlPass, $sqlBData);
$res = mysqli_query($link, "SET names utf8");
if($pd)
{
	if($dataBool == true)
	{
		$sql = "SELECT * FROM `infoMail` WHERE `answered` = 'no' and `email` = '$email' and `date` >= '$date1' and `date` <= '$date2' ORDER BY `idMsg` LIMIT $numLimit, 10";
		$res = mysqli_query($link,  $sql);
	}
	else
	{
		$sql = "SELECT * FROM `infoMail` WHERE `answered` = 'no' and `email` = '$email' ORDER BY `idMsg` LIMIT $numLimit, 10";
		$res = mysqli_query($link,  $sql);
	}
}
else
{
	if($dataBool == true)
	{
		$sql = "SELECT * FROM `infoMail` WHERE `answered` = 'no' and `date` >= '$date1' and `date` <= '$date2' ORDER BY `idMsg` LIMIT $numLimit, 10";
		$res = mysqli_query($link,  $sql);
	}
	else
	{
		$sql = "SELECT * FROM `infoMail` WHERE `answered` = 'no' ORDER BY `idMsg` LIMIT $numLimit, 10";
		$res = mysqli_query($link, $sql);
	}
}


?>
<table border='1'>
<tr><td colspan="6">Лист<?echo ' '.$numList+1;?></td></tr>
<tr>
	<td>idMsg</td>
	<td>subject</td>
	<td>from</td>
	<td>email</td>
	<td>date</td>
	<td>message</td>
</tr>
<?
while($Data = mysqli_fetch_assoc($res))
{
	if(!$Data)
		break;?>
	<tr>
		<td><?echo $Data['idMsg'];?></td>
		<td><?echo $Data['subject'];?></td>
		<td><?echo $Data['from'];?></td>
		<td><?echo $Data['email'];?></td>
		<td><?echo $Data['date'];?></td>
		<td><?echo $Data['message'];?></td>
	</tr>
<?}?>

</table>
<?
mysqli_close($link);
?>

<br>

<form method="get">
	<table border="1">
		<th colspan="2">Фильтры</th>
		<tr>
			<td><label>Почта</label></td>
			<td><input type="text" name="email"></td>
		</tr>
		<tr>
			<td><label>Номер страницы</label></td>
			<td><input type="number" min="1" max="" name="numStr"></td>
		</tr>
		<tr>
			<td><label>Дата1</label></td>
			<td><input type="text" name="date1"></td>
		</tr>
		<tr>
			<td><label>Дата2</label></td>
			<td><input type="text" name="date2"></td>
		</tr>
		<tr><td colspan="2"><input type="submit" name="send" value="Искать"></td></tr>

	</table>
</form>
