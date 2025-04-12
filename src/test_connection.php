<?php
// test_connection.php - Use this file to test your database connection

require_once __DIR__ . '/config/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test the connection with a simple query
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<p style='color: green; font-weight: bold;'>✓ Connection successful!</p>";
    echo "<p>PostgreSQL version: " . htmlspecialchars($version['version']) . "</p>";

    // Check if tables exist
    $tables = array('pessoas', 'grupos', 'produtos', 'lugares', 'movimentos');
    echo "<h2>Checking database tables:</h2>";
    echo "<ul>";

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT to_regclass('public.$table') AS exists");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['exists']) {
            echo "<li style='color: green;'>✓ Table '$table' exists</li>";
        } else {
            echo "<li style='color: red;'>✗ Table '$table' does not exist</li>";
        }
    }

    echo "</ul>";

    // Display current connection parameters (without password)
    echo "<h2>Connection Parameters:</h2>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($host) . "</li>";
    echo "<li>Database: " . htmlspecialchars($dbname) . "</li>";
    echo "<li>User: " . htmlspecialchars($user) . "</li>";
    echo "<li>Port: " . htmlspecialchars($port) . "</li>";
    echo "</ul>";

} catch(PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";

    // Display troubleshooting information
    echo "<h2>Troubleshooting:</h2>";
    echo "<ol>";
    echo "<li>Check if PostgreSQL service is running</li>";
    echo "<li>Verify host, port, database name, username and password</li>";
    echo "<li>Check if the database 'estoque' exists</li>";
    echo "<li>Ensure network connectivity between PHP and PostgreSQL</li>";
    echo "<li>If using Docker, check that container names match in docker-compose.yml and db.php</li>";
    echo "</ol>";

    // Display current connection parameters (without password)
    echo "<h2>Current Connection Parameters:</h2>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($host) . "</li>";
    echo "<li>Database: " . htmlspecialchars($dbname) . "</li>";
    echo "<li>User: " . htmlspecialchars($user) . "</li>";
    echo "<li>Port: " . htmlspecialchars($port) . "</li>";
    echo "</ul>";
}
?>