<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

require_once '../Database/db-config.php';

if (isset($_GET['id'])) {
    $attachmentId = intval($_GET['id']);
    
    // In a real app, you might check if the user has permission to view this specific complaint's attachment
    $stmt = $conn->prepare("SELECT file_name, file_type, file_data FROM complaint_attachments WHERE attachment_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $attachmentId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($fileName, $fileType, $fileData);
            $stmt->fetch();
            $disposition = isset($_GET['download']) ? "attachment" : "inline";
            header("Content-Type: " . $fileType);
            header("Content-Disposition: " . $disposition . "; filename=\"" . $fileName . "\"");
            echo $fileData;
            exit;
        }
        $stmt->close();
    }
}

die("Attachment not found.");
?>
