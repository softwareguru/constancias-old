<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Importar constancias para generar</title>
<style type="text/css">
body {
	background: #E3F4FC;
	font: normal 14px/30px Helvetica, Arial, sans-serif;
	color: #2b2b2b;
}
a {
	color:#898989;
	font-size:14px;
	font-weight:bold;
	text-decoration:none;
}
a:hover {
	color:#CC0033;
}

h1 {
	font: bold 14px Helvetica, Arial, sans-serif;
	color: #CC0033;
}
h2 {
	font: bold 14px Helvetica, Arial, sans-serif;
	color: #898989;
}
#container {
	background: #CCC;
	margin: 100px auto;
	width: 945px;
}
#form 			{padding: 20px 150px;}
#form input     {margin-bottom: 20px;}
</style>
</head>
<body>
<div id="container">
<div id="form">

<?php

<?php
define('FPDF_FONTPATH','lib/fpdf/font/');
require_once('lib/fpdf/fpdf.php');
require_once('lib/fpdi/fpdi.php');
require_once('lib/PHPMailer/PHPMailerAutoload.php');
require_once('constants.php');

$conn = mysql_connect( DBHOST, DBUSER, DBPASS) or die                      ('Error connecting to mysql');
mysql_select_db(DBNAME);

//Upload File
if (isset($_POST['submit'])) {
	if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
		echo "<h1>" . "Archivo ". $_FILES['filename']['name'] ." subido exitosamente." . "</h1>";
		echo "<h2>Mostrando contenido:</h2>";
		readfile($_FILES['filename']['tmp_name']);
	}

	//Import uploaded file to Database
	$handle = fopen($_FILES['filename']['tmp_name'], "r");
/*
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    if(is_numeric($data[0]) {  // Checar que no es una etiqueta de header
  	  $import="INSERT into constancias_generar (template_id, nombre_participante, email, tag) values( $data[0],'$data[1]','$data[2]','$data[3]')";
      mysql_query($import) or die(mysql_error());
    } // if
	} // while
*/
	fclose($handle);

	print "Import terminado";

	//view upload form
} else {

	print "Upload new csv by browsing to file and clicking on Upload<br />\n";

	print "<form enctype='multipart/form-data' action='upload.php' method='post'>";

	print "File name to import:<br />\n";

	print "<input size='50' type='file' name='filename'><br />\n";

	print "<input type='submit' name='submit' value='Upload'></form>";

}

?>

</div>
</div>
</body>
</html>

