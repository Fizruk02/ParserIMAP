<?php
function convertDate($str)
{
	$date = explode(' ', $str);

	$number = $date[1];
	$mon = $date[2];
	$year = $date[3];

	switch ($mon) 
	{
		case 'Jan':
			$mon = '01';
			break;
		case 'Feb':
			$mon = '02';
			break;
		case 'Mar':
			$mon = '03';
			break;
		case 'Apr':
			$mon = '04';
			break;
		case 'May':
			$mon = '05';
			break;
		case 'Jun':
			$mon = '06';
			break;
		case 'Jul':
			$mon = '07';
			break;
		case 'Aug':
			$mon = '08';
			break;
		case 'Sep':
			$mon = '09';
			break;
		case 'Oct':
			$mon = '10';
			break;
		case 'Nov':
			$mon = '11';
			break;
		case 'Dec':
			$mon = '12';
			break;
		
		default:
			$year = '01';
			break;
	}

	if(strlen($number) == 1)
		$number = "0".$number;

	$date = $year.'-'.$mon.'-'.$number;
	return $date;
}

class Imap_parser {
    
	function inbox($data)
	{

		$result = array();
		
		$imap = imap_open($data['email']['hostname'], $data['email']['username'], $data['email']['password']) or die ('Cannot connect to '.$data['email']['username'].': '.imap_last_error());
		
		if ($imap) {
			
			$result['status'] = 'success';
			$result['email']  = $data['email']['username'];
			
			$read = imap_search($imap, 'ALL');
			
			if($data['pagination']['sort'] == 'DESC'){
				rsort($read);
			}
			
			$num = count($read);
			$result['count'] = $num;
			
			$stop = $data['pagination']['limit'] + $data['pagination']['offset'];
			
			if($stop > $num){
				$stop = $num;
			}
			
			for ($i = $data['pagination']['offset']; $i < $stop; $i++) 
			{
				if(!$read[$i])
				{
					continue;
				}
					$overview = imap_fetch_overview($imap, $read[$i]);
					$message = imap_body($imap, $read[$i]);
					$header = imap_headerinfo($imap, $read[$i]);
					$mail = $header->from[0]->mailbox . '@' . $header->from[0]->host;
					$image = '';

					if(mb_strpos($mail, 'gmail') == true)
					{
						$ex = explode("\n", $message);
						$pd = true;
						$message = $ex[4];
						$message = base64_decode($message);
						goto loop;
					}
					else
					{
						$pd = false;
						$message = preg_replace('/--(.*)/i', '', $message);
						$message = preg_replace('/X\-(.*)/i', '', $message);
						$message = preg_replace('/Content\-ID\:/i', '', $message);
						
						$msg = '';            
						
						if (preg_match('/Content-Type/', $message)) {
							$message = strip_tags($message);
							$content = explode('Content-Type: ', $message);
							foreach ($content as $c) 
							{
								if (preg_match('/base64/', $c)) 
								{
									$b64 = explode('base64', $c);
									if (preg_match('/==/', $b64[1])) 
									{
										$str = explode('==', $b64[1]);
										$dec = $str[0];
									} 
									else 
									{
										$dec = $b64[1];
									}
									if (preg_match('/image\/(.*)\;/', $c, $mime)) 
									{
										$image = 'data:image/' . $mime[1] . ';base64,' . trim($dec);
									}
								} 
								else 
								{
									if (!empty($c)) 
									{
										$msg = $c;
									}
								}
							}
						} else {
							$msg = $message;
						}

						$msg = preg_replace('/text\/(.*)UTF\-8/', '', $msg);
						$msg = preg_replace('/text\/(.*)\;/', '', $msg);
						$msg = preg_replace('/charset\=(.*)\"/', '', $msg);
						$msg = preg_replace('/Content\-Transfer\-Encoding\:(.*)/i', '', $msg);
					}
					
					$from = explode('<', imap_utf8($overview[0]->from));

					loop:

					if($pd == true)
					{
						$body = $message;
					}
					else
					{
						$body = iconv_mime_decode(quoted_printable_decode(trim($msg)));
					}

					$result['inbox'][] = array(
						'id' => $read[$i],
						'subject' => imap_utf8(strip_tags($overview[0]->subject)),
						'from' => imap_utf8(strip_tags($overview[0]->from)),
						'email' => $mail,
						'date' => $overview[0]->date,
						'message' => $body,
						'image' => $image
					);
					
					$result['pagination'] = array(
						'sort' => $data['pagination']['sort'],
						'limit' => $data['pagination']['limit'],
						'offset' => array(
							'back' => ($data['pagination']['offset'] == 0 ? null : $data['pagination']['offset'] - $data['pagination']['limit']),
							'next' => ($data['pagination']['offset'] < $num ? $data['pagination']['offset'] + $data['pagination']['limit'] : null)
						)
					);
			}
			
			imap_close($imap);
			
		} else {
			$result['status'] = 'error';
		}
		
		return $result;
		
	}

}

?>
