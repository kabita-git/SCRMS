<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? $_SESSION['role'] ?? '', ['Admin', 'UpperBody'])) {
    header('Location: /index.php');
    exit;
}

include_once '../Database/db-config.php';

$message = '';
$messageType = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $categoryName = trim($_POST['categoryName'] ?? '');
    $description = trim($_POST['categoryDescription'] ?? '');
    $categoryId = intval($_POST['categoryId'] ?? 0);

    if ($action === 'add') {
        if (empty($categoryName) || empty($description)) {
            $_SESSION['message'] = "Please fill in all required fields.";
            $_SESSION['messageType'] = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO complaint_categories (category_name, description) VALUES (?, ?)");
            if ($stmt) {
                $stmt->bind_param("ss", $categoryName, $description);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Category added successfully!";
                    $_SESSION['messageType'] = "success";
                } else {
                    $_SESSION['message'] = "Error adding category: " . $conn->error;
                    $_SESSION['messageType'] = "error";
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "Database error: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
        }
    } elseif ($action === 'edit') {
        if (empty($categoryName) || empty($description) || $categoryId <= 0) {
            $_SESSION['message'] = "Invalid input for editing.";
            $_SESSION['messageType'] = "error";
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE complaint_categories SET category_name = ?, description = ? WHERE category_id = ?");
                $stmt->bind_param("ssi", $categoryName, $description, $categoryId);
                $stmt->execute();
                $stmt->close();

                // Handle Dept Head assignment
                $deptHeadId = isset($_POST['deptHeadId']) ? intval($_POST['deptHeadId']) : 0;
                
                // First, unassign whoever was assigned to this category
                $stmt = $conn->prepare("UPDATE users SET assigned_category = NULL WHERE assigned_category = ?");
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                $stmt->close();

                // Then, assign the new head if one was selected
                if ($deptHeadId > 0) {
                    $stmt = $conn->prepare("UPDATE users SET assigned_category = ? WHERE user_id = ?");
                    $stmt->bind_param("ii", $categoryId, $deptHeadId);
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit();
                $_SESSION['message'] = "Category updated successfully!";
                $_SESSION['messageType'] = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Database error: " . $e->getMessage();
                $_SESSION['messageType'] = "error";
            }
        }
    } elseif ($action === 'delete') {
        if ($categoryId <= 0) {
            $_SESSION['message'] = "Invalid category selected for deletion.";
            $_SESSION['messageType'] = "error";
        } else {
            $stmt = $conn->prepare("DELETE FROM complaint_categories WHERE category_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $categoryId);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Category deleted successfully!";
                    $_SESSION['messageType'] = "success";
                } else {
                    $_SESSION['message'] = "Database error: " . $conn->error;
                    $_SESSION['messageType'] = "error";
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "Database error: " . $conn->error;
                $_SESSION['messageType'] = "error";
            }
        }
    }
    
    // Redirect to clear POST data (PRG pattern)
    header('Location: complaint-category.php');
    exit;
}

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'] ?? 'success';
    // Clear the message so it only shows once
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Fetch all DeptAdmins for the dropdown
$deptAdmins = [];
$res_heads = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'DeptAdmin' AND status = 'Active' ORDER BY first_name ASC");
if ($res_heads) {
    while ($row = $res_heads->fetch_assoc()) {
        $deptAdmins[] = $row;
    }
}

// Fetch Categories for the Table
$categories = [];
$sql = "SELECT c.*, u.user_id as head_id, u.first_name, u.last_name 
        FROM complaint_categories c 
        LEFT JOIN users u ON u.assigned_category = c.category_id AND u.role = 'DeptAdmin'
        ORDER BY c.category_name ASC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Category - Complaint System</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
    <link rel="stylesheet" href="../Assets/Css/complaint-category.css">
</head>
<body>
   <?php include '../Includes/header.php'; ?>

    <div class="main-container">
        <?php include '../Includes/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <h2 class="page-title">Complaints <span class="page-subtitle">Category</span></h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 5px; color: white; background-color: <?php echo $messageType === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Data Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-title">Complaint Category Table</h3>
                    <button class="add-btn" id="addCategoryBtn">Add +</button>
                </div>

                <!-- Table Controls -->
                <div class="table-controls">
                    <div class="entries-control">
                        <label>Show</label>
                        <select id="entriesSelect">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>Entries</span>
                    </div>

                    <div class="search-control">
                        <label>Search:</label>
                        <input type="text" id="searchInput" placeholder="Search...">
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="data-table" id="categoryTable">
                        <thead>
                            <tr>
                                <th>Complaint Category</th>
                                <th>Description</th>
                                <th>Dept Head</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                                        <td class="desc-cell" title="<?php echo htmlspecialchars($cat['description']); ?>">
                                            <div class="text-truncate">
                                                <?php echo htmlspecialchars($cat['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($cat['head_id']) {
                                                    echo htmlspecialchars($cat['first_name'] . ' ' . $cat['last_name']);
                                                } else {
                                                    echo '<span style="color: #999; font-style: italic;">Not Assigned</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                            <button class="edit-btn" title="Edit" 
                                                    data-id="<?php echo $cat['category_id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($cat['category_name']); ?>" 
                                                    data-desc="<?php echo htmlspecialchars($cat['description']); ?>"
                                                    data-head-id="<?php echo $cat['head_id'] ?? ''; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <button class="delete-btn" title="Delete" data-id="<?php echo $cat['category_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="table-footer">
                    <div class="entries-info">Showing 1 to 3 of 18 entries</div>
                    <div class="pagination">
                        <button class="page-btn" id="prevBtn">Previous</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" id="nextBtn">Next</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Category Modal -->
    <div class="modal" id="categoryModal" style="z-index: 99999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add Category</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <form id="categoryForm" method="POST" action="">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="categoryId" id="categoryId" value="">
                <div class="form-group">
                    <label>Category Name <span class="required">*</span></label>
                    <input type="text" id="categoryName" name="categoryName" placeholder="Enter category name" required>
                </div>
                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea id="categoryDescription" name="categoryDescription" placeholder="Enter description" rows="4" required></textarea>
                </div>
                <div class="form-group" id="deptHeadGroup">
                    <label>Assigned Dept Head</label>
                    <select name="deptHeadId" id="deptHeadId">
                        <option value="">-- Select Dept Admin --</option>
                        <?php foreach ($deptAdmins as $admin): ?>
                            <option value="<?php echo $admin['user_id']; ?>"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelBtn">Close</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteModal" class="custom-modal-overlay" style="z-index: 99999;">
        <div class="custom-modal-box">
            <h3 id="deleteMessage">Are you sure you want to delete this category?</h3>
            <div class="custom-modal-actions" id="deleteActions">
                <button class="modal-btn-cancel" id="deleteCancel">Cancel</button>
                <button class="modal-btn-confirm" id="deleteConfirm">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for deletion -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="categoryId" id="deleteCategoryId">
    </form>

    <script src="../Assets/JS/admin-dashboard.js"></script>
    <script src="../Assets/JS/table-pagination.js"></script>
    <script src="../Assets/JS/complaint-category.js"></script>
</body>
</html>