<?php
// Файлы phpmailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_secret = 'key';
$recaptcha_response = $_POST['recaptcha_response'];

// Выполняем POST-запрос
$recaptcha = $recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response;
$ch = curl_init();
curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $recaptcha);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);  
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);     
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result = curl_exec($ch);
curl_close($ch);

$recaptcha = json_decode($result);


$c = true;
// Формирование самого письма
$title = "Заявка с сайта Городской Квартал";
foreach ( $_POST as $key => $value ) {
  if ( $value != "" && $value != "on"  && $key != "recaptcha_response") {
	 $tgBody .= "<b>".$key."</b> ".$value."%0A";
    $body .= "
    " . ( ($c = !$c) ? '<tr>':'<tr style="background-color: #f8f8f8;">' ) . "
      <td style='padding: 10px; border: #e9e9e9 1px solid;'><b>$key</b></td>
      <td style='padding: 10px; border: #e9e9e9 1px solid;'>$value</td>
    </tr>
    ";
  }
}

$body = "<table style='width: 100%;'>$body</table>";

// Настройки PHPMailer
$mail = new PHPMailer\PHPMailer\PHPMailer();

try {
  $mail->isSMTP();
  $mail->CharSet = "UTF-8";
  $mail->SMTPAuth   = true;

  // Настройки вашей почты
  $mail->Host   = 'smtp.yandex.ru';// SMTP сервера вашей почты
  $mail->Username   = 'mailer.sender.mailer';  // Логин на почте
  $mail->Password   = 'password'; // Пароль на почте
  $mail->SMTPSecure = 'ssl';
  $mail->Port       = 465;

  $mail->setFrom('sender', 'Городской квартал'); // Адрес самой почты и имя отправителя

  // Получатель письма
  $mail->addAddress('recepient');

  // Отправка сообщения
  $mail->isHTML(true);
  $mail->Subject = $title;
  $mail->Body = $body;

if ($recaptcha->success == true && $recaptcha->score >= 0.5 && $recaptcha->action == 'contact') {
	$mail->send();

	} else {
		echo "Something went wrong. Please try again later";
	}

} catch (Exception $e) {
  $status = "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
}