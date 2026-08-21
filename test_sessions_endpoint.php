<?php
/**
 * Test endpoint to check what sessions are being returned
 * Access: http://localhost:8000/test_sessions_endpoint.php
 */

// Include CodeIgniter
define('BASEPATH', TRUE);
require_once('index.php');

// Get the CI instance
$CI =& get_instance();
$CI->load->database();
$CI->load->library('customlib');
$CI->load->model('session_model');

echo "<h2>Session Debug Test</h2>";
echo "<h3>Raw Database Query (No Filter):</h3>";

// Get all sessions without filter
$CI->db->select('*');
$CI->db->from('sessions');
$CI->db->order_by('id', 'ASC');
$query = $CI->db->get();
$allSessions = $query->result_array();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Session (Raw)</th><th>Year</th><th>Valid BS?</th><th>Converted</th></tr>";
foreach ($allSessions as $s) {
    $session = $s['session'];
    $parts = explode('-', $session);
    $year1 = isset($parts[0]) ? intval($parts[0]) : 0;
    $isValid = ($year1 >= 2070 && $year1 <= 2090) ? 'YES' : 'NO';
    $converted = $CI->customlib->convertSessionToBSIfNeeded($session);
    
    echo "<tr>";
    echo "<td>{$s['id']}</td>";
    echo "<td>{$session}</td>";
    echo "<td>{$year1}</td>";
    echo "<td>{$isValid}</td>";
    echo "<td>{$converted}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Filtered Query (207/208/209 only):</h3>";

// Get filtered sessions
$sql = "SELECT * FROM sessions WHERE (session LIKE '207%' OR session LIKE '208%' OR session LIKE '209%') ORDER BY id ASC";
$query = $CI->db->query($sql);
$filteredSessions = $query->result_array();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Session</th></tr>";
foreach ($filteredSessions as $s) {
    echo "<tr><td>{$s['id']}</td><td>{$s['session']}</td></tr>";
}
echo "</table>";

echo "<h3>API Endpoint Test:</h3>";
echo "<p><a href='sessions/getCurrentAndFutureSessions' target='_blank'>Click to test API endpoint</a></p>";

