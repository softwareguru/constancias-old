<?php
define('FPDF_FONTPATH','font/');
require_once('fpdf.php');
require_once('fpdi.php');
?>

<html>
<head>
    <title>Generador de constancias</title>
</head>
<body>

<?php

$dbhost = 'localhost';
$dbuser = 'user';
$dbpass = 'password';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die                      ('Error connecting to mysql');

$dbname = 'dev';
mysql_select_db($dbname);

$query_txt = "select c.id, nombre_participante as nombre, email, nombre_evento, template_file, coords_x, coords_y
from constancias_generar c, constancias_template t
where c.template_id = t.id
AND generada = 0
limit 0, 20";

$result=mysql_query($query_txt);

// Email template
$subject = "Constancia ";
$message0 = "Te informamos que tu constancia ya fue generada.\n\n";
$headers = "From: eventos@sg.com.mx";

// Iterar por cada renglón
while($row = mysql_fetch_array($result))
{
    $constanciaId = $row["id"];
    $nombre = $row["nombre"];
    $email = $row["email"];
    $nombreEvento = $row["nombre_evento"];
    $templatePath = "templates/".$row["template_file"];
    $coordsX = $row["coords_x"];
    $coordsY = $row["coords_y"];

    $pdf =& new FPDI();

    $pagecount = $pdf->setSourceFile($templatePath);
    $tplidx = $pdf->importPage(1, '/MediaBox');
    $pdf->addPage("P", "Letter");
    $pdf->useTemplate($tplidx);
    $pdf->SetFont('Helvetica','B',18);

    $pdf->SetXY((int)$coordsX,(int)$coordsY);
    $pdf->Cell(100, 0, $nombre, 0, 0,"C");

    $pdf->setDisplayMode("real");
    $filename = "results/".urlencode($nombreEvento." - ".$email).".pdf";
    $pdf->Output($filename, 'F');

    echo "Escribi ".$filename."\n<br />\n";

    $filename = str_replace("%", "%25", $filename);

    $subject = "Constancia de ".$nombreEvento;
    $message = $message0;
    $message .= "Puedes descargarla en ".$serverpath.$filename;
    $message .= "\n\nAtentamente,\n Staff SG";

    mysql_query("UPDATE constancias_generar SET generada = 1 where id = ".$constanciaId ) or die(mysql_error());

    mail($email, $subject, $message, $headers);
// Esperamos medio segundo para no inundar el mail server. 
    usleep(500000);

}

mysql_close();

?>
<p>Constancias generadas.</p>

<p>Ver <a href="results/">aqui</a></p>
</body>
</html>
