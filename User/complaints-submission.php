<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? $_SESSION['role'] ?? '') !== 'User') {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$message = "";
$messageType = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $title = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $batch = isset($_POST['batch']) ? trim($_POST['batch']) : '';
    $categoryId = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $statusId = 1; // Default to Pending

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, category_id, status_id, title, description, batch, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) throw new Exception($conn->error);
        
        $stmt->bind_param("iiisss", $userId, $categoryId, $statusId, $title, $description, $batch);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        
        $complaintId = $stmt->insert_id;
        $stmt->close();

        // Handle multiple file uploads as BLOBs
        if (isset($_FILES['evidence'])) {
            $stmtAtt = $conn->prepare("INSERT INTO complaint_attachments (complaint_id, file_name, file_type, file_data) VALUES (?, ?, ?, ?)");
            if (!$stmtAtt) throw new Exception($conn->error);

            foreach ($_FILES['evidence']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['evidence']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES['evidence']['name'][$key];
                    $fileType = $_FILES['evidence']['type'][$key];
                    $fileData = file_get_contents($tmpName);
                    
                    $stmtAtt->bind_param("isss", $complaintId, $fileName, $fileType, $fileData);
                    $stmtAtt->send_long_data(3, $fileData); // Send BLOB data
                    if (!$stmtAtt->execute()) throw new Exception($stmtAtt->error);
                }
            }
            $stmtAtt->close();
        }

        $conn->commit();
        $_SESSION['message'] = "Complaint and evidence submitted successfully!";
        $_SESSION['messageType'] = "success";
        header("Location: user-complaints.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error submitting complaint: " . $e->getMessage();
        $messageType = "error";
    }
}

// Fetch categories for dropdown
$categories = [];
$res_cat = $conn->query("SELECT * FROM complaint_categories");
if ($res_cat) {
    while ($row = $res_cat->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - Student Complaint System</title>
    <link rel="stylesheet" href="../Assets/css/complaints-submission.css">
</head>
<body>
    <?php include '../Includes/header.php'; ?>
    <?php include '../Includes/sidebar.php'; ?>
    <div class="main-container">

        <main class="main-content">
            <div class="complaint-form-container">
                <h2 class="form-title">Complaint Form</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background-color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form id="complaintForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Title/Subject</label>
                        <input type="text" id="name" name="name" placeholder="Enter Subject....." required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email<span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="batch">Batch<span class="required">*</span></label>
                        <select id="batch" name="batch" required>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Category<span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description<span class="required">*</span></label>
                        <textarea id="description" name="description" placeholder="Please describe your issue in detail...." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="date">Date<span class="required">*</span></label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="evidence">Supporting Evidence</label>
                        <div class="file-upload">
                            <input type="file" id="evidence" name="evidence[]" accept="image/*,.pdf,.doc,.docx" multiple>
                            <label for="evidence" class="file-upload-label">
                                <span id="fileNameDisplay">Upload Files</span>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                </svg>
                            </label>
                        </div>
                        <div id="fileList" style="margin-top: 10px; display: flex; flex-direction: column; gap: 5px;"></div>
                    </div>

                    <div class="button-group">
                        <button type="reset" class="reset-btn">Reset</button>
                        <button type="submit" class="submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- File Removal Confirmation Modal -->
    <div id="fileDeleteModal" class="custom-modal-overlay">
        <div class="custom-modal-box">
            <h3 style="margin-bottom: 20px;">Are you sure you want to remove this file?</h3>
            <div class="custom-modal-actions">
                <button type="button" class="modal-btn-cancel" id="fileDeleteCancel">Cancel</button>
                <button type="button" class="modal-btn-confirm" id="fileDeleteConfirm">Yes, Remove</button>
            </div>
        </div>
    </div>

    <script src="../Assets/JS/complaints-submission.js"></script>
</body>
</html>