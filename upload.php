<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["docFile"])) {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["docFile"]["name"]);
    $fileType = pathinfo($targetFile, PATHINFO_EXTENSION);

    // Check if uploaded file is a DOC file
    if ($fileType != "doc" && $fileType != "docx") {
        echo "Please upload a DOC file.";
        exit();
    }

    // Create a directory for uploads if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($_FILES["docFile"]["tmp_name"], $targetFile)) {
        echo "The file ". basename( $_FILES["docFile"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "Please upload a file.";
}
?>
