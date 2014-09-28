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

$conn = mysql_connect( DBHOST, DBUSER, DBPASS) or die                      ('Error connecting to mysql');
mysql_select_db(DBNAME);

$query_txt = "select c.id, nombre_participante as nombre, email, tag, 
nombre_evento, template_file, coords_x, coords_y
from constancias_generar c, constancias_template t
where c.template_id = t.id
AND generada = 0
limit 0, 50";

$result=mysql_query($query_txt);

// Email template
$subject = "Constancia de ";
$message0 = "Te informamos que tu constancia ya fue generada.\n\n";

    $mail = new PHPMailer;
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
while($row = mysql_fetch_array($result))
{
    $constanciaId = $row["id"];
    $nombre = $row["nombre"];
    $email = $row["email"];
    $tag = $row["tag"];
    $nombreEvento = $row["nombre_evento"];
    $templatePath = "templates/".$row["template_file"];
    $coordsX = $row["coords_x"];
    $coordsY = $row["coords_y"];

    $absDir = RESULTSDIR."/".$tag;
    if (!file_exists($absDir)) {
        mkdir($absDir, 0755, true);
    }

    $pdf =& new FPDI();

    $pagecount = $pdf->setSourceFile($templatePath);
    $tplidx = $pdf->importPage(1, '/MediaBox');
    $pdf->addPage("P", "Letter");
    $pdf->useTemplate($tplidx);
    $pdf->AddFont('MrDafoe', '', 'MrDafoe.php');
    $pdf->SetFont('MrDafoe','',32);

    $pdf->SetXY((int)$coordsX,(int)$coordsY);
    $pdf->Cell(100, 0, $nombre, 0, 0,"C");

    $pdf->setDisplayMode("real");
    $filename = $tag."/".urlencode($nombre).".pdf";
    $absFilename = RESULTSDIR."/".$filename;
    $pdf->Output($absFilename, 'F');

    echo "Escribi ".$filename."\n<br />\n";

    $filename = str_replace("%", "%25", $filename);

    $message = $message0;
    $message .= "Puedes descargarla en ".BASEURL."/".$filename;
    $message .= "\n\nAtentamente,\n Staff SG";

    $mail->Subject = "Constancia de ".$nombreEvento;
    $mail->clearAddresses();       // remove previous recipient
    $mail->addAddress($email);     // add a recipient
    $mail->Body    = $message;

    if(!$mail->Send()) {
       echo 'Message could not be sent.';
       echo 'Mailer Error: ' . $mail->ErrorInfo;
       break;
    } else {
        echo "Message sent to: ".$email."\n";
        mysql_query("UPDATE constancias_generar SET generada = 1 where id = ".$constanciaId ) or die(mysql_error());
    }
}

mysql_close();

?>
<p>Constancias generadas.</p>

<p>Ver <a href="results/">aqui</a></p>
</body>
</html>
