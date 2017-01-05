<?php
require_once __DIR__ . '/vendor/autoload.php';
define('FILEPATH', realpath(dirname(__FILE__)));

class Register {
	private $msg = "";
	private $status = "ok";
	public function execute() {
		$file = "csv/galleryAudition.csv";
		$value = $this->getDataFromUser();
		$this->createCSV($file);
		$this->writeCSV($value,$file);
		$html = $this->generateHTML($value, count(file($file)) - 1);
		$this->sendMailWithAttachFile($this->generatePDFFile($html), $value->email, $value->name);
		$this->returnError();
	}

	private function returnError() {
		$res = array("status"=> $this->status, "msg"=>$this->msg);
		echo json_encode($res);
		exit();
	}

	private function redirect($url, $statusCode = 303)
	{
	   header('Location: ' . $url, true, $statusCode);
	   die();
	}

	private function generateHTML($rows, $idx) {
		$html = '<head><meta content="text/html;charset=utf-8" http-equiv="Content-Type"><meta content="utf-8" http-equiv="encoding"><style>body{font-family:Gotham,Helvetica Neue,Helvetica,Arial," sans-serif";width:40%}.outer{border:double 7px #e7e85e;background-color:#f6f7d8;padding:0 20px;width:89.2%;margin:0 auto}.container{margin:10px 30px}.client-info{font-weight:bold}.warning{font-style:italic;font-size:8px}.time-address-alert{font-weight:bold;font-size:12px;margin-top:10px}.time-address{font-weight:bold;font-size:15px}.audition-address{font-size:10px;word-wrap:break-word}table tr th{text-align:left;font-size:10px}table tr td{font-size:10px;margin-left: 3px;}.client-name{font-size:18px;font-weight:bold}.request{margin:30px auto;width:97%}.container-list{margin-bottom:10px}.body-container{min-height:100%;position:relative}.footer{position:absolute;bottom:0;left:0}</style></head><body><div class="body-container"><div class="header"> <img src="'.FILEPATH.'/img/hd.jpg" width="100%"></div></div><div class="container"><div class="frame-info"><div class="outer"><div class="inner-info" style="width: 50%;float: left">';
		$html.='<p style="font-weight: bold; font-size: 15px;">NO <span class="client-no">'.sprintf("%09d",$idx).'</span></p><table style="margin-top: -25px; margin-left: -3px;">';
		
		$html.='<tr><th colspan="2"><p><span class="client-name">'.$rows->name.'</span></p></th></tr>';
		$html.='<tr><th>Địa chỉ:</th><td>'.$rows->address.'</td></tr>';
		$html.='<tr><th>Ngày sinh:</th><td>'.$rows->dob.'</td></tr>';
		$html.='<tr><th>Số điện thoại:</th><td>'.$rows->phone.'</td></tr>';
		$html.='<tr><th>Email:</th><td>'.$rows->email.'</td></tr>';
		$html.='</table> <p class="warning">Có sai sót gì trong những nội dung trên không?</p> <p class="warning">Nếu có, có thể bạn sẽ không được tham gia vào buổi tuyển chọn.</p><p class="time-address-alert">THỜI GIAN - ĐỊA ĐIỂM THỬ GIỌNG</p> <span class="time-address">10:00 AM - TP.HỒ CHÍ MINH</span><p class="audition-address">11F TNR Tower, 180 Nguyễn Công Trứ, Nguyễn Thái Bình Ward, District 1</p><br></div><div class="inner-map" style="width: 40%;float: right"> <img src="'.FILEPATH.'/img/map.png" width="100%" style="margin: 15px 0;"></div> <br style="clear:both;"/></div></div><div class="request"><table><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Vật dụng mang theo</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjaskldjskaljdsakljdsad sajdlksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Yêu cầu tập trung</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjaskldjska sad jkslajdlsajd lkasjd lkaskldasdkasjdksaldj aksjdk alsj dlsakd jsak djaslkdljdsakljdsad sajdlksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Ghi chú</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjaskldjskaljdsakljdsad sajd sajdlskajd lsakjd lasd askd jsldsakldjasl lksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr><tr><td><img src="'.FILEPATH.'/img/liststyle2.jpg" height="30"></td><td style="padding-left:20px;color:#f05c7f;font-weight:bold;font-size:12px;">Liên hệ</td></tr><tr><td></td><td style="padding-left:20px;padding-bottom: 10px;"> asjdksajdlsakdjas dsakljd salkj dkasljd salkjdskaldj salkd jsakldj salkdj sakljd ksad jlkldjskaljdsakljdsad sajdlksajdsalkjdsad sajdskaljdsalkdjsklajdklsajdklsajdsa djsadklsajdklsjadklsajdksljdkls</td></tr></table></div></div><div class="footer"> <img src="'.FILEPATH.'/img/ft.jpg" width="100%"></div></div></body>';
		// print_r($html); exit;
		return $html;
	}

	function generatePDFFile($html) {
		$name = $this->randomName() . "Gallery Audition Information.pdf";
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
		$mail->addCC('lnhthy@brights.vn');
		// $mail->addBCC('bcc@example.com');
		
		$mail->addAttachment($fileName);         // Add attachments
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = '[STARDOM AUDITION] Notice of Audition Primary Audit and Secondary Audit';
		$mail->Body    = 'This is the HTML message body <b>in bold!</b>';

		if(!$mail->send()) {
		    $this->msg = 'Message could not be sent.';
		    $this->msg .= '<br>Mailer Error: ' . $mail->ErrorInfo;
		    $this->status = "ng";
		    unlink($fileName);
		    $this->returnError();
		} else {
		    $this->msg = 'Message has been sent to ' . $mailTo;
		}
		unlink($fileName);

	}

	function createCSV($file) {
		$header = array(
			"Time","Họ và tên","Địa chỉ","Ngày sinh","Số điện thoại","Email","Image Link"
		);
		if (file_exists($file)) {
			return;
		}
		$output = fopen($file, 'w');
		fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		fputcsv($output, $header);

		fclose($output);
	}

	function writeCSV($value = null, $file) {
		if (is_null($value)) {
			return;
		}
		$contents = array(
			$value->time,$value->name,$value->address,$value->dob,$value->phone,$value->email,$value->image
		);
		$output = fopen($file, 'a');
		fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		fputcsv($output, $contents);

		fclose($output);
	}

	function format_phone_number($number) {
		$number = str_replace(" ", "", $number);
		if (strlen($number) == 10) {
			$result = preg_replace('~.*(\d{4})(\d{3})(\d{3}).*~', '$1 $2 $3', $number);
		} else {
			$result = preg_replace('~.*(\d{4})(\d{3})(\d{4}).*~', '$1 $2 $3', $number);
		}
	    
	    return $result;
	}

	function getDataFromUser() {
		$value = new stdClass();
		$value->time = date("Y/m/d H:i:s");
		$value->name = $_POST["name"];
		$value->address = $_POST["address"];
		$value->dob = $_POST["dob"];
		$value->phone = $this->format_phone_number($_POST["phone"]);
		$value->email = $_POST["email"];
		$value->image = $this->getFileUploadImage();
		return $value;
	}

	function getFileUploadImage() {
		$target_dir = FILEPATH . "/uploads";
		$target_file = $target_dir . "/" . $this->randomName() . basename($_FILES["avatar"]["name"]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		// if(isset($_POST["submit"])) {
		//     $check = getimagesize($_FILES["avatar"]["tmp_name"]);
		//     if($check !== false) {
		//         // echo "File is an image - " . $check["mime"] . ".";
		//         $uploadOk = 1;
		//     } else {
		//         $msg = "File is not an image.";
		//         $status = "ng";
		//         $uploadOk = 0;
		//     }
		// }
		// Check if file already exists
		if (file_exists($target_file)) {
		    $this->msg = "Sorry, file already exists.";
		    $this->status = "ng";
		    $this->uploadOk = 0;
		    $this->returnError();
		}
		// Check file size
		if ($_FILES["avatar"]["size"] > 700000) {
		    $this->msg = "Sorry, your file is too large.";
		    $this->status = "ng";
		    $this->uploadOk = 0;
		    $this->returnError();
		}

		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
		    $this->msg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$this->status = "ng";
		    $this->uploadOk = 0;
		    $this->returnError();
		}

		if (!is_dir($target_dir)) {
			mkdir($target_dir);
		}
		// Check if $uploadOk is set to 0 by an error

		
		if ($uploadOk == 0) {
		    $this->msg = "Sorry, your file was not uploaded.";
		    $this->status = "ng";
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
			    $this->msg = "The file ". basename( $_FILES["avatar"]["name"]). " has been uploaded.";
			} else {
			    $this->msg = "Sorry, there was an error uploading your file.";
			    $this->status = "ng";
			    $this->returnError();
			}
			return $target_file;
		}
	}
	function randomName() {
		$str = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890_";
		$result = "";
		for ($i = 0; $i<20; $i++) {
			$int = rand(0, strlen($str));
			$result.= substr($str, $int, 1);
		}
	 	return $result;
	}


	// function base_url($atRoot=FALSE, $atCore=FALSE, $parse=FALSE){
	//     if (isset($_SERVER['HTTP_HOST'])) {
	//         $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
	//         $hostname = $_SERVER['HTTP_HOST'];
	//         $dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

	//         $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
	//         $core = $core[0];

	//         $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
	//         $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
	//         $base_url = sprintf( $tmplt, $http, $hostname, $end );
	//     }
	//     else $base_url = 'http://localhost/';

	//     if ($parse) {
	//         $base_url = parse_url($base_url);
	//         if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
	//     }

	//     return $base_url;
	// }
}


$register = new Register();
$register->execute();
