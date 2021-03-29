<?php
define('FPDF_FONTPATH','lib/fpdf/font/');
require_once('lib/fpdf/fpdf.php');
require_once('lib/fpdi/fpdi.php');
require_once('lib/PHPMailer/PHPMailerAutoload.php');
require_once('constants.php');
?>

<html>
<head>
    <title>Generador de constancias</title>
</head>
<body>

<?php

$pdo = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

$query_text = "select c.id, nombre_participante as nombre, email, tag, "
            ."nombre_evento, template_file, coords_x, coords_y "
            ."from constancias_generar c, constancias_template t "
            ."where c.template_id = t.id AND generada = 0 limit 0, 50";

// Email template
$subject = "Constancia de ";
$message0 = "Te informamos que tu constancia ya fue generada.\n\n";

    $mail = new PHPMailer;
    $mail->CharSet = "UTF-8";  // Charset for accents and special characters
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = SMTPHOST;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = SMTPUSER;                 // SMTP username
    $mail->Password = SMTPPASS;                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to
    $mail->From = SENDEREMAIL;
    $mail->FromName = SENDERNAME;
    $mail->isHTML(false);

// Iterar por cada renglón

foreach($pdo->query($query_text) as $row)
{
    $constanciaId = $row["id"];
    $nombre = $row["nombre"];
    $email = $row["email"];
    $tag = $row["tag"];
    $nombreEvento = $row["nombre_evento"];
    $templatePath = "templates/".$row["template_file"];
    $coordsX = $row["coords_x"];
    $coordsY = $row["coords_y"];

    // Necesario porque FPDF no soporta UTF-8.
    $nombre = iconv('UTF-8', 'windows-1252', $nombre);

    $absDir = RESULTSDIR."/".$tag;
    if (!file_exists($absDir)) {
        mkdir($absDir, 0755, true);
    }

    $pdf = new FPDI();

    $pagecount = $pdf->setSourceFile($templatePath);
    $tplidx = $pdf->importPage(1, '/MediaBox');
    $pdf->addPage("P", "Letter");
    $pdf->useTemplate($tplidx);
// fuentes extra
//     $pdf->AddFont('helvetica, '', 'helvetica.php');
//     $pdf->SetFont('helvetica','',36);

   $pdf->AddFont('TangerineBold', '', 'Tangerine_Bold.php');
   $pdf->SetFont('TangerineBold','',34);

    $pdf->SetXY((int)$coordsX,(int)$coordsY);
    $pdf->Cell(100, 0, $nombre, 0, 0,"C");

    $pdf->setDisplayMode("real");
    $filename = $tag."/".urlencode($nombre).".pdf";
    $absFilename = RESULTSDIR."/".$filename;
    $pdf->Output($absFilename, 'F');

//  Descomentar la siguiente línea si no quieres mandar mails.
//    $email = "nomandar";

    echo "Escribi ".$filename."\n<br />\n";

    $filename = str_replace("%", "%25", $filename);;

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = $message0;
	$message .= "Puedes descargar tu constancia  en ".BASEURL."/".$filename;
        $message .= "\n\nAtentamente,\n Staff SG";
        $mail->Subject = "Constancia de ".$nombreEvento;
        $mail->clearAddresses();       // remove previous recipient
        $mail->addAddress($email);     // add a recipient
        $mail->Body    = $message;

        if(!$mail->Send()) {
            echo "Message could not be sent.";
            echo "Mailer Error: " . $mail->ErrorInfo;
            continue;
        } else {
            echo "Message sent to: ".$email."\n";
          $pdo->exec("UPDATE constancias_generar SET generada = 1 where id = ".$constanciaId );
        }
    } else {  // email invalido. Vamos a poner generada = 2 para indicar que se generó pero no se mandó mail.
        echo "Generada pero el email es invalido\n";
        $pdo->exec("UPDATE constancias_generar SET generada = 2 where id = ".$constanciaId );
    }
} // for


?>
<p>Constancias generadas.</p>

<p>Ver <a href="results/">aqui</a></p>
</body>
</html>

