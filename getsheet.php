<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Gallary Audition');
define('CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-gallary-audition.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS)
));

define('FILEPATH', realpath(dirname(__FILE__)));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);

  // echo $credentialsPath; exit;
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    file_put_contents(FILEPATH . "/saveRefreshToken.token", json_encode($accessToken['refresh_token']));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
  	$refresh_token = $client->getRefreshToken();
  	if (is_null($refresh_token) || $refresh_token == "") {
  		// $handle = fopen("saveRefreshToken.token", "r");
  		// echo filesize(FILEPATH . "/saveRefreshToken.token"); echo "\n"; exit();
  		$refresh_token = json_decode(file_get_contents(FILEPATH . "/saveRefreshToken.token"));
  	}
    $client->fetchAccessTokenWithRefreshToken($refresh_token);
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
$spreadsheetId = '1l9pLx-9oiqufkDuJ3pxNDVtWwKeN5S05ZgTzK_OLVIY';
$range = 'A2:G999999';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
$arrValues = array();
$countRows = count($values);
if (count($values) == 0) {
  print "No data found.\n";
} else {
	$ISREPLYMAIL = 6;
	$CLIENTMAIL = 5;
	$CLIENTNAME = 1;
  	print "Rendering PDF and sending email ...\n";
  	$html = "";
  	$idx = 0;
  	foreach ($values as $key=>$rows) {
  		$idx++;
  		$arrValues[0][] = 1;
	  	if (isset($rows[$ISREPLYMAIL]) && $rows[$ISREPLYMAIL] == 1) {
	    	continue;
	    } else {
		    $html = generateHTML($rows, $idx);
		    sendMailWithAttachFile(generatePDFFile($html), $rows[$CLIENTMAIL], $rows[$CLIENTNAME]);
	    }
  	}
   	
   	updateSheetAfterSendMail($service, $arrValues, $spreadsheetId);
}

function generateHTML($rows, $idx) {
	$html = '<head><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><meta content="utf-8" http-equiv="encoding"><style>body{font-family:Gotham,Helvetica Neue,Helvetica,Arial," sans-serif";width:40%}.outer{border:double 7px #e7e85e;background-color:#f6f7d8;padding:0 20px;width:89.2%;margin:0 auto}.container{margin:10px 30px}.client-info{font-weight:bold}.warning{font-style:italic;font-size:8px}.time-address-alert{font-weight:bold;font-size:12px;margin-top:10px}.time-address{font-weight:bold;font-size:15px}.audition-address{font-size:10px;word-wrap:break-word}table tr th{text-align:left;font-size:10px}table tr td{font-size:10px;margin-left: 3px;}.client-name{font-size:18px;font-weight:bold}.request{margin:30px auto;width:97%}.container-list{margin-bottom:10px}.body-container{min-height:100%;position:relative}.footer{position:absolute;bottom:0;left:0}</style></head><body><div class="body-container"><div class="header"> <img src="'.FILEPATH.'/img/hd.jpg" width="100%"></div></div><div class="container"><div class="frame-info"><div class="outer"><div class="inner-info" style="width: 50%;float: left">';
	$html.='<p style="font-weight: bold; font-size: 15px;">NO <span class="client-no">'.sprintf("%09d",$idx).'</span></p><table style="margin-top: -25px; margin-left: -3px;">';
	// $html = '<div class="body-pdf">';
	for ($i = 0; $i < count($rows); $i++) {
		
		switch($i) {
			case 1:
				$html.='<tr><th colspan="2"><p><span class="client-name">'.$rows[1].'</span></p></th></tr>';
				break;
			case 2:
				$html.='<tr><th>Địa chỉ:</th><td>'.$rows[2].'</td></tr>';
				break;
			case 3:
				$html.='<tr><th>Ngày sinh:</th><td>'.$rows[3].'</td></tr>';
				break;
			case 4:
				$html.='<tr><th>Số điện thoại:</th><td>'.$rows[4].'</td></tr>';
				break;
			case 5:
				$html.='<tr><th>Email:</th><td>'.$rows[5].'</td></tr>';
				break;
		}
	}
	$html.='</table> <p class="warning">Có sai sót gì trong những nội dung trên không?</p> <p class="warning">Nếu có, có thể bạn sẽ không được tham gia vào buổi tuyển chọn.</p><p class="time-address-alert">THỜI GIAN - ĐỊA ĐIỂM THỬ GIỌNG</p> <span class="time-address">10:00 AM - TP.HỒ CHÍ MINH</span><p class="audition-address">11F TNR Tower, 180 Nguyễn Công Trứ, Nguyễn Thái Bình Ward, District 1</p><br></div><div class="inner-map" style="width: 40%;float: right"> <img src="'.FILEPATH.'/img/map.png" width="100%" style="margin: 15px 0;"></div> <br style="clear:both;"/></div></div><div class="request"><table><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Vật dụng mang theo</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjaskldjskaljdsakljdsad sajdlksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Yêu cầu tập trung</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjaskldjska sad jkslajdlsajd lkasjd lkaskldasdkasjdksaldj aksjdk alsj dlsakd jsak djaslkdljdsakljdsad sajdlksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Ghi chú</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjaskldjskaljdsakljdsad sajd sajdlskajd lsakjd lasd askd jsldsakldjasl lksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Liên hệ</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjas dsakljd salkj dkasljd salkjdskaldj salkd jsakldj salkdj sakljd ksad jlkldjskaljdsakljdsad sajdlksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr></table></div></div><div class="footer"> <img src="'.FILEPATH.'/img/ft.jpg" width="100%"></div></div></body>';
	return $html;
}

function generatePDFFile($html) {
	$name = FILEPATH."/Gallery Audition Information.pdf";
	$mpdf=new mPDF('utf-8', 'Letter', 0, '', 0, 0, 0, 0, 0, 0);

	$mpdf->autoScriptToLang = true;
	$mpdf->autoLangToFont = true;
	$mpdf->WriteHTML($html);

	$mpdf->Output($name, "F");
	return $name;
}

function sendMailWithAttachFile($fileName, $mailTo, $clientName) {
	$mail = new PHPMailer;

	//$mail->SMTPDebug = 3;                               // Enable verbose debug output

	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'gallery2016audition@gmail.com';                 // SMTP username
	$mail->Password = 'kappachan';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;                                    // TCP port to connect to

	$mail->setFrom('gallery2016audition@gmail.com', utf8_decode('Gallery Audtion'));
	$mail->addAddress($mailTo, utf8_decode($clientName));     // Add a recipient
	$mail->addCC('hoaithy92@gmail.com');
	// $mail->addBCC('bcc@example.com');
	
	$mail->addAttachment($fileName);         // Add attachments
	$mail->isHTML(true);                                  // Set email format to HTML

	$mail->Subject = '[STARDOM AUDITION] Notice of Audition Primary Audit and Secondary Audit';
	$mail->Body    = 'This is the HTML message body <b>in bold!</b>';

	if(!$mail->send()) {
	    echo 'Message could not be sent.';
	    echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
	    echo "Message has been sent to " . $mailTo . "\n";
	}
	unlink($fileName);
}

function updateSheetAfterSendMail($service, $values, $spreadsheetId) {
	$range = "G2";
	$body = new Google_Service_Sheets_ValueRange(array(
	  'values' => $values,
	  "majorDimension" => "COLUMNS"
	));
	// print_r($body); exit;
	$params = array(
	  'valueInputOption' => 'USER_ENTERED'
	);
	$result = $service->spreadsheets_values->update($spreadsheetId, $range,
	    $body, $params);
}

// print $html; exit;