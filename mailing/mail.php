<?
$mailContent=file_get_contents('index.html');
$subject='Mailing Petrolabs ';

$headers   = array();
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/html; charset=iso-8859-1";
$headers[] = "From: Petrolabs <mercadeo@".$_SERVER['HTTP_HOST'].">";
$headers[] = "Subject: {$subject}";
$headers[] = "X-Mailer: PHP/".phpversion();

mail('carcasari@gmail.com, carcasari@hotmail.com, gerencia@haggen-it.com',$subject,$mailContent, implode("\r\n", $headers));

echo $mailContent;