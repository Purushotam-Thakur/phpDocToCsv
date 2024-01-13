<!DOCTYPE html>
<html>
<head>
    <title>DOC to CSV Converter</title>
</head>
<body>
    <h1>DOC to CSV Converter</h1>
    <form action="convert.php" method="post" enctype="multipart/form-data">
        Select DOC file to upload:
        <input type="file" name="docFile" id="docFile">
        <input type="submit" value="Convert to CSV" name="submit">
    </form>
</body>
</html>
