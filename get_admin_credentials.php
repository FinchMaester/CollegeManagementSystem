<?php
/**
 * Script to retrieve admin credentials from the database
 * This script queries the staff table to find admin users
 */

// Database configuration (from application/config/database.php)
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'collegesystem';

// Connect to database
$mysqli = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h2>Admin/Staff Credentials</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Name</th><th>Email (Login Username)</th><th>Role</th><th>Is Active</th></tr>";

// Query staff table for admin users
// First, let's get all staff members and their roles
$query = "SELECT s.id, s.name, s.surname, s.email, s.is_active, r.name as role_name, sr.role_id
          FROM staff s
          LEFT JOIN staff_roles sr ON s.id = sr.staff_id
          LEFT JOIN roles r ON sr.role_id = r.id
          ORDER BY s.id";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $full_name = trim($row['name'] . ' ' . $row['surname']);
        $role = $row['role_name'] ? $row['role_name'] : 'No Role';
        
        // Highlight admin/superadmin roles
        $is_admin = (stripos($role, 'admin') !== false || stripos($role, 'superadmin') !== false);
        $row_color = $is_admin ? 'background-color: #ffffcc;' : '';
        
        echo "<tr style='$row_color'>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($full_name) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($role) . "</td>";
        echo "<td>" . ($row['is_active'] == 1 ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No staff members found</td></tr>";
}

echo "</table>";

// Also check the admin table if it exists
echo "<br><h2>Admin Table (if exists)</h2>";
$admin_query = "SHOW TABLES LIKE 'admin'";
$table_check = $mysqli->query($admin_query);

if ($table_check && $table_check->num_rows > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Is Active</th></tr>";
    
    $admin_result = $mysqli->query("SELECT id, username, email, role, is_active FROM admin");
    
    if ($admin_result && $admin_result->num_rows > 0) {
        while ($row = $admin_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td>" . ($row['is_active'] == 'yes' ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No admin users found</td></tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Admin table does not exist. Admin login uses the staff table.</p>";
}

// Close connection
$mysqli->close();

echo "<br><p><strong>Note:</strong> Passwords are hashed in the database and cannot be retrieved. ";
echo "To reset a password, use the 'Forgot Password' feature on the login page, ";
echo "or update the password directly in the database using the encryption method.</p>";
echo "<p><strong>Login Info:</strong> Use the email address as the username on the login page.</p>";
?>

