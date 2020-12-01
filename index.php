<?php
session_start();

$classList = " hidden";
$message = "";

if (isset($_SESSION['success']) && $_SESSION['success'] === false) {
  if (isset($_SESSION['error'])) {
    $classList = " error";
    $message = "<strong>Fehler:</strong> " . $_SESSION['error'];
  } else if (isset($_SESSION['warning'])) {
    $classList = " warning";
    $message = "<strong>Warnung:</strong> " . $_SESSION['warning'];
  } else if (isset($_SESSION['info'])) {
    $classList = " info";
    $message = "<strong>Hinweis:</strong> " . $_SESSION['info'];
  } else {
    $message = $_SESSION['message'] ?? "";
    if (!empty($message)) {
      $classList = " secondary";
    }
  }

  // Cleanup
  session_destroy();
}
?>

<html lang="de">
<head>
	<meta charset="utf-8">

	<title>Winmail.dat-Umwandler</title>
	<meta name="description" content="Small web application to extract attachments from winmail.dat files">
	<meta name="author" content="Kevin Köllmann <mail@kevinkoellmann.de>">

	<link rel="stylesheet" type="text/css" href="css/style.css">

</head>

<body>
	<section id="wrapper">
		<div class="container">
			<div class="rectangle">
				<h2>Winmail.dat-Umwandler</h2>
				<div class="left_cor"></div>
				<div class="right_cor"></div>
			</div>
			<div class="content">
				<div class="message<?php echo $classList; ?>"><?php echo $message; ?></div>

				<form enctype="multipart/form-data" action="convert.php" method="POST" onSubmit="return checkUploadForm();">
					<!-- Der Name des Input-Felds bestimmt den Namen im $_FILES-Array -->
					<p>
						Diese Datei hochladen:<br/>
						<input name="userfile" id="userfile" type="file" accept=".dat" />
					</p>
					<p><input type="submit" value="Datei hochladen" /></p>
				</form>
			</div>
		</div>
	</section>

	<script src="js/checkUploadForm.fn.js"></script>
</body>
</html>
