<?php
declare(strict_types = 1);
session_start();

require __DIR__ . '/vendor/autoload.php';

use TNEFDecoder\TNEFAttachment;
use TNEFDecoder\TNEFFileBase;

/**
 * Simple wrapper to redirect via the HTTP "Location" header.
 *
 * @param string $url The URL to redirect to
 * @param int    $statusCode (Optional) The HTTP statuscode to use (301 = permanent, 302 = temporary, 303 = other)
 */
function redirect(string $url, int $statusCode = 303)
{
  header("Location: $url", true, $statusCode);
  die();
}

/**
 * Extract attachments from winmail.dat files using TNEFDecoder.
 *
 * @param string $filename The path to the winmail.dat file
 *
 * @return TNEFFileBase[]
 */
function getAttachments(string $filename): array
{
  $buffer = file_get_contents($filename);

  $attachment = new TNEFAttachment();
  $attachment->decodeTnef($buffer);

  return $attachment->getFiles();
}

/**
 * Create a Zip archive containing the provided files.
 *
 * @param TNEFFileBase[] $files Array of TNEFFile's to archive
 *
 * @return string The path to the created Zip archive
 */
function createZip(array $files): string
{
  $zip = new ZipArchive();
  $zipFile = tempnam(sys_get_temp_dir(), "Att");

  if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
    die("cannot open <$zipFile>\n");
  }

  $tmpFiles = []; // Keep temporary file names for later cleanup

  foreach ($files as $file) {
    $tmpFile = tempnam(sys_get_temp_dir(), $file->getName());
    $tmpFiles[] = $tmpFile;

    $handle = fopen($tmpFile, "w");
    if (!$handle) {
      die("cannot open <$tmpFile>\n");
    }

    fwrite($handle, $file->getContent());
    fclose($handle);

    $zip->addFile($tmpFile, $file->getName());
  }

  $zip->close();

  // Cleanup temporary files
  foreach ($tmpFiles as $tmpFile) {
    unlink($tmpFile);
  }

  return $zipFile;
}

// Process only if a file has been uploaded
if (isset($_FILES['userfile'])) {
  // Get attachments from uploaded file
  $attachments = getAttachments($_FILES['userfile']['tmp_name']);
  if (empty($attachments)) {
    $_SESSION['success'] = false;
    $_SESSION['info'] = "Keine Anhänge enthalten.";
    redirect("./");
  }

  // Create Zip archive containing the attachments
  $zipFile = createZip($attachments);

  // Provide Download of Zip archive
  header("Cache-Control: must-revalidate");
  header("Content-Description: File Transfer");
  header("Content-Disposition: attachment; filename=\"attachments.zip\"");
  header("Content-Encoding: zip");
  header("Content-Length: " . filesize($zipFile));
  header("Content-Type: application/zip");
  header("Expires: 0");
  header("Pragma: public");
  readfile($zipFile);

  // Cleanup temporary files
  unlink($zipFile);
} else {
  redirect('./');
}
