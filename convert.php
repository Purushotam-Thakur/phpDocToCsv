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

        // Logic to parse the MCQ content
        $lines = explode("\n", $content);
        $mcqData = [];
        $mcq = [];

        foreach ($lines as $line) {
            if (preg_match('/^\d+\./', $line)) {
                // New question starts
                if (!empty($mcq)) {
                    // Save previous MCQ data
                    $mcqData[] = $mcq;
                    $mcq = [];
                }
                $mcq['Question Title'] = trim($line);
            } elseif (preg_match('/^\([a-d]\)/i', $line)) {
                // Options
                $mcq['Answer Text'] = $line;
            } elseif (strpos($line, 'Ans.') !== false) {
                // Answer and Explanation
                preg_match('/\(([a-d])\)/i', $line, $matches);
                $mcq['Answer Correct/InCorrect'] = strtoupper($matches[1]);
                $mcq['Explanation'] = trim(strstr($line, ':', true));
            }
        }
        // Add the last MCQ data
        if (!empty($mcq)) {
            $mcqData[] = $mcq;
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
        // Prepare data for CSV
        $csvFilename = 'converted.csv';
        $csvFile = fopen($csvFilename, 'w');
        fputcsv($csvFile, $columnHeadings); // Write the column headings

        foreach ($mcqData as $mcq) {
            fputcsv($csvFile, $mcq); // Write each MCQ as a row
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
