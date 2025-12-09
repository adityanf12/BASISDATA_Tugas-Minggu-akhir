<?php
/**
 * Library Management System - PHP Version
 * File: library.php
 * 
 * Features:
 * - CRUD Operations (Create, Read, Update, Delete)
 * - Search Functionality
 * - Uses: VIEW, STORED PROCEDURE, FUNCTION, TRIGGER
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Ubah sesuai password MySQL Anda
define('DB_NAME', 'library_db');

// ============================================
// DATABASE CONNECTION CLASS
// ============================================
class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// ============================================
// INITIALIZE
// ============================================
session_start();
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = 'success';
$action = $_GET['action'] ?? 'list';

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ADD BOOK - Menggunakan Stored Procedure
    if (isset($_POST['tambah_buku'])) {
        $stmt = $conn->prepare("CALL add_book(?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", 
            $_POST['title'], 
            $_POST['author'], 
            $_POST['isbn'], 
            $_POST['year'], 
            $_POST['stock']
        );
        
        if ($stmt->execute()) {
            $message = "✅ Buku berhasil ditambahkan menggunakan Stored Procedure!";
            $messageType = 'success';
        } else {
            $message = "❌ Error: " . $conn->error;
            $messageType = 'error';
        }
        $stmt->close();
        $conn->next_result();
        
        header("Location: library.php?msg=" . urlencode($message) . "&type=" . $messageType);
        exit();
    }
    
    // UPDATE BOOK
    if (isset($_POST['perbarui_buku'])) {
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, published_year=?, stock=? WHERE id=?");
        $stmt->bind_param("sssiii", 
            $_POST['title'], 
            $_POST['author'], 
            $_POST['isbn'], 
            $_POST['year'], 
            $_POST['stock'], 
            $_POST['id']
        );
        
        if ($stmt->execute()) {
            $message = "✅ Buku berhasil diperbarui! (Trigger mencatat perubahan)";
            $messageType = 'success';
        } else {
            $message = "❌ Error: " . $conn->error;
            $messageType = 'error';
        }
        $stmt->close();
        
        header("Location: library.php?msg=" . urlencode($message) . "&type=" . $messageType);
        exit();
    }
    
    // UPDATE STOCK - Menggunakan Stored Procedure
    if (isset($_POST['perbarui_stok'])) {
        $stmt = $conn->prepare("CALL update_stock(?, ?)");
        $stmt->bind_param("ii", $_POST['id'], $_POST['new_stock']);
        
        if ($stmt->execute()) {
            $message = "✅ Stok berhasil diperbarui menggunakan Stored Procedure!";
            $messageType = 'success';
        } else {
            $message = "❌ Error: " . $conn->error;
            $messageType = 'error';
        }
        $stmt->close();
        $conn->next_result();
        
        header("Location: library.php?msg=" . urlencode($message) . "&type=" . $messageType);
        exit();
    }
    
    // DELETE BOOK
    if (isset($_POST['hapus_buku'])) {
        $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        
        if ($stmt->execute()) {
            $message = "✅ Buku berhasil dihapus! (Trigger mencatat penghapusan)";
            $messageType = 'success';
        } else {
            $message = "❌ Error: " . $conn->error;
            $messageType = 'error';
        }
        $stmt->close();
        
        header("Location: library.php?msg=" . urlencode($message) . "&type=" . $messageType);
        exit();
    }
}

// ============================================
// GET MESSAGE FROM URL
// ============================================
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'success';
}

// ============================================
// SEARCH FUNCTIONALITY - Menggunakan Stored Procedure
// ============================================
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $conn->prepare("CALL search_books(?)");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->next_result();
} else {
    // Menggunakan VIEW untuk menampilkan data
    $result = $conn->query("SELECT * FROM book_info_view ORDER BY title");
    $books = $result->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// GET STATISTICS - Menggunakan VIEW
// ============================================
$stats = $conn->query("SELECT * FROM library_stats")->fetch_assoc();

// ============================================
// GET TOTAL STOCK - Menggunakan FUNCTION
// ============================================
$total_stock = $conn->query("SELECT get_total_stock() as total")->fetch_assoc()['total'];

// ============================================
// GET ACTIVITY LOG
// ============================================
$logs = $conn->query("SELECT * FROM activity_log ORDER BY log_time DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);

// ============================================
// GET BOOK FOR EDIT
// ============================================
$edit_book = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $edit_book = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - PHP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        /* HEADER */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        /* CONTENT */
        .content {
            padding: 30px 40px;
        }
        
        /* MESSAGE ALERT */
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* STATISTICS CARDS */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .stat-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-card .icon {
            font-size: 24px;
            opacity: 0.8;
        }
        
        /* SEARCH BAR */
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .search-form input {
            flex: 1;
            min-width: 300px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .search-form input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        /* BUTTONS */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        /* FORM SECTION */
        .form-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .form-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        /* TABLE */
        .table-section {
            margin-bottom: 30px;
        }
        
        .table-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        /* BADGE */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* ACTIONS */
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .actions .btn {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        .stock-update {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .stock-update input {
            width: 70px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
        }
        
        /* LOGS SECTION */
        .logs-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
        }
        
        .logs-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .log-item {
            background: white;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .log-item strong {
            color: #667eea;
            font-size: 14px;
        }
        
        .log-item small {
            color: #6c757d;
            font-size: 12px;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .search-form input {
                min-width: 100%;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .stock-update {
                width: 100%;
            }
            
            .stock-update input {
                flex: 1;
            }
        }
        
        /* NO DATA */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>
                <i class="fas fa-book"></i>
                Library Management System
            </h1>
            <p>PHP Application with MySQL Database Features (VIEW, STORED PROCEDURE, FUNCTION, TRIGGER)</p>
        </div>
        
        <!-- CONTENT -->
        <div class="content">
            <!-- MESSAGE -->
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <i class="fas fa-<?= $messageType == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <!-- STATISTICS -->
            <div class="stats">
                <div class="stat-card">
                    <h3><i class="fas fa-book icon"></i> Total Books</h3>
                    <div class="value"><?= $stats['total_books'] ?></div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-boxes icon"></i> Total Stock (Function)</h3>
                    <div class="value"><?= $total_stock ?></div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-chart-line icon"></i> Avg Stock</h3>
                    <div class="value"><?= number_format($stats['avg_stock_per_book'], 1) ?></div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-users icon"></i> Total Authors</h3>
                    <div class="value"><?= $stats['total_authors'] ?></div>
                </div>
            </div>
            
            <!-- SEARCH -->
            <div class="search-section">
                <form method="GET" action="library.php" class="search-form">
                    <input type="text" 
                           name="search" 
                           placeholder="🔍 Search by title, author, or ISBN (uses Stored Procedure)..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($search): ?>
                        <a href="library.php" class="btn btn-warning">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- FORM ADD/EDIT BOOK -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-<?= $edit_book ? 'edit' : 'plus-circle' ?>"></i>
                    <?= $edit_book ? 'Edit Book' : 'Add New Book' ?>
                </h2>
                <form method="POST" action="library.php">
                    <?php if ($edit_book): ?>
                        <input type="hidden" name="id" value="<?= $edit_book['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Title *</label>
                            <input type="text" 
                                   name="title" 
                                   value="<?= htmlspecialchars($edit_book['title'] ?? '') ?>" 
                                   required 
                                   placeholder="Enter book title">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Author *</label>
                            <input type="text" 
                                   name="author" 
                                   value="<?= htmlspecialchars($edit_book['author'] ?? '') ?>" 
                                   required 
                                   placeholder="Enter author name">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-barcode"></i> ISBN</label>
                            <input type="text" 
                                   name="isbn" 
                                   value="<?= htmlspecialchars($edit_book['isbn'] ?? '') ?>" 
                                   placeholder="Enter ISBN">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Published Year</label>
                            <input type="number" 
                                   name="year" 
                                   value="<?= $edit_book['published_year'] ?? '' ?>" 
                                   placeholder="e.g., 2024">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-cubes"></i> Stock *</label>
                            <input type="number" 
                                   name="stock" 
                                   value="<?= $edit_book['stock'] ?? 0 ?>" 
                                   required 
                                   min="0" 
                                   placeholder="Enter stock quantity">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" 
                                name="<?= $edit_book ? 'update_book' : 'add_book' ?>" 
                                class="btn btn-success">
                            <i class="fas fa-<?= $edit_book ? 'save' : 'plus' ?>"></i>
                            <?= $edit_book ? 'Update Book' : 'Add Book (Stored Procedure)' ?>
                        </button>
                        <?php if ($edit_book): ?>
                            <a href="library.php" class="btn btn-warning">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- BOOK LIST TABLE -->
            <div class="table-section">
                <h2>
                    <i class="fas fa-list"></i>
                    Book List (From VIEW: book_info_view)
                </h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Year</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($books) > 0): ?>
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?= $book['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td><?= htmlspecialchars($book['isbn'] ?? '-') ?></td>
                                    <td><?= $book['published_year'] ?? '-' ?></td>
                                    <td><strong><?= $book['stock'] ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= $book['stock'] == 0 ? 'danger' : ($book['stock'] < 5 ? 'warning' : 'success') ?>">
                                            <?= $book['stock_status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="library.php?action=edit&id=<?= $book['id'] ?>" class="btn btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" 
                                                  style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this book? (Trigger will log this action)')">
                                                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                                                <button type="submit" name="delete_book" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <form method="POST" class="stock-update">
                                                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                                                <input type="number" 
                                                       name="new_stock" 
                                                       value="<?= $book['stock'] ?>" 
                                                       min="0" 
                                                       required>
                                                <button type="submit" name="update_stock" class="btn btn-info">
                                                    <i class="fas fa-sync"></i> Update (SP)
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">
                                        <i class="fas fa-inbox"></i>
                                        <p>Tidak ada buku ditemukan. Tambahkan buku pertama Anda!</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- ACTIVITY LOG -->
            <div class="logs-section">
                <h2>
                    <i class="fas fa-history"></i>
                    Activity Log (Generated by TRIGGER)
                </h2>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <div class="log-item">
                            <strong><?= htmlspecialchars($log['action']) ?></strong> - 
                            <?= htmlspecialchars($log['details']) ?>
                            <br>
                            <small>
                                <i class="fas fa-clock"></i> 
                                <?= date('d M Y, H:i:s', strtotime($log['log_time'])) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <p>Belum ada log aktivitas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Close database connection
$db->close();
?>