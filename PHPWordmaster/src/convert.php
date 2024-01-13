<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    // Check for file upload errors
    if ($_FILES["docFile"]["error"] !== UPLOAD_ERR_OK) {
        echo "File upload failed with error code: " . $_FILES["docFile"]["error"];
        exit();
    }

    if (move_uploaded_file($_FILES["docFile"]["tmp_name"], $targetFile)) {
        // Read content from DOC/DOCX file
        if ($fileType == "docx") {
            $zip = new ZipArchive;
            if ($zip->open($targetFile) === true) {
                $content = $zip->getFromName('word/document.xml');
                $content = strip_tags($content);
                $zip->close();
            }
        } else if ($fileType == "doc") {
            // Handle DOC file
            $content = shell_exec('antiword -t "' . $targetFile . '"');
        }

        // Set the column headings
        $columnHeadings = [
            "Item Type",
            "Question Title",
            "Question Description",
            "Answer Text",
            "Answer Point",
            "Answer Correct/InCorrect",
            "Answer Caption",
            "Answer label",
            "Question Answer Info",
            "Comments",
            "Hints",
            "Question Type New",
            "Required",
            "Require all rows",
            "Answer Editor",
            "Feature Image Src",
            "Match Answer",
            "Case Sensitive",
            "Answer Columns",
            "Image Size-Width",
            "Image Size-Height",
            "Autofill Text Limit",
            "Limit Multiple Response",
            "File Upload Limit",
            "File Upload Type",
            "Categories"
        ];

        // Convert text content to CSV format
        $lines = explode("\n", $content);
        $csvContent = [];

        foreach ($lines as $line) {
            // Remove any unwanted characters or formatting from the text
            $cleanedLine = str_replace(["\r", '"'], ['', '""'], $line);
            // Add cleaned lines to the row array
            $csvContent[] = $cleanedLine;
        }

        // Save the CSV content to a file
        $csvFilename = 'converted.csv';
        $csvFile = fopen($csvFilename, 'w');
        fputcsv($csvFile, $columnHeadings); // Write the column headings

        // Write each row to the CSV file
        foreach ($csvContent as $row) {
            fputcsv($csvFile, [$row]); // Write each row as an array
        }

        fclose($csvFile);

        // Provide download link for the converted CSV
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $csvFilename . '"');
        readfile($csvFilename);
        exit();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo "Please upload a file.";
}
?>
