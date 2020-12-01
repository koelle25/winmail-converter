function checkUploadForm() {
  if (document.getElementById('userfile').value != "") {
    return true;
  }
  else {
    alert("Sie haben keine Datei ausgewählt!");
    return false;
  }
}
