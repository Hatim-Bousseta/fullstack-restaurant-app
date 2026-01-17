<?php
echo "<h1>Oracle Connection Test</h1>";

// 1. Check if OCI8 is loaded
if (!extension_loaded('oci8')) {
    die("<p style='color:red;'>❌ OCI8 NOT LOADED. Restart Apache!</p>");
}

echo "<p style='color:green;'>✅ OCI8 Extension is LOADED!</p>";

// 2. Try to connect
echo "<h3>Trying to connect to Oracle...</h3>";

// First, let's check if user exists
require_once __DIR__ . '/db.php';
$user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'FOOD_DB_USER');
$pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : 'FOOD_DB_PASS');
$db = getenv('DB_CONN') ?: (defined('DB_CONN') ? DB_CONN : 'localhost/XEPDB1');

echo "<p>Using: <code>$user / *** / $db</code></p>";

$conn = oci_connect($user, $pass, $db);

if (!$conn) {
    $error = oci_error();
    echo "<p style='color:red;'>❌ Connection failed: " . htmlspecialchars($error['message']) . "</p>";
    
    echo "<h4>Troubleshooting:</h4>";
    echo "<ol>";
    echo "<li>Check if Oracle 21c is running (Windows Services → OracleServiceXE)</li>";
    echo "<li>Verify user exists: Run in SQL Developer: <code>SELECT * FROM all_users WHERE username = 'FOODUSER'</code></li>";
    echo "<li>Try different connection string:</li>";
    echo "</ol>";
    
    // Try alternative connection
    echo "<h4>Alternative connection attempts:</h4>";
    
    $attempts = [
        'localhost:1521/XEPDB1' => 'With port',
        '//localhost/XEPDB1' => 'Easy Connect',
        '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=XEPDB1)))' => 'Full TNS'
    ];
    
    foreach ($attempts as $conn_string => $desc) {
        echo "<p>$desc: <code>$conn_string</code>... ";
        $test = @oci_connect($user, $pass, $conn_string);
        if ($test) {
            echo "<span style='color:green;'>✅ SUCCESS!</span></p>";
            
            // Get Oracle version
            $sql = "SELECT banner FROM v\$version WHERE banner LIKE '%Oracle%'";
            $stmt = oci_parse($test, $sql);
            oci_execute($stmt);
            $row = oci_fetch_array($stmt, OCI_ASSOC);
            
            echo "<p>Oracle Version: <strong>" . $row['BANNER'] . "</strong></p>";
            
            oci_close($test);
            break;
        } else {
            $err = oci_error();
            echo "<span style='color:red;'>❌ Failed</span></p>";
        }
    }
    
} else {
    echo "<p style='color:green; font-size:20px;'>🎉 CONNECTED SUCCESSFULLY!</p>";
    
    // Get Oracle version
    $sql = "SELECT banner FROM v\$version WHERE banner LIKE '%Oracle%'";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    
    echo "<p>Oracle Version: <strong>" . $row['BANNER'] . "</strong></p>";
    
    // Check if tables exist
    $sql = "SELECT table_name FROM user_tables ORDER BY table_name";
    $stmt = oci_parse($conn, $sql);
    oci_execute($stmt);
    
    $tables = [];
    while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
        $tables[] = $row['TABLE_NAME'];
    }
    
    if (empty($tables)) {
        echo "<p style='color:orange;'>⚠️ No tables found in FOODUSER schema</p>";
        echo "<p><a href='create_tables.php'>Click here to create tables</a></p>";
    } else {
        echo "<p>Found " . count($tables) . " table(s):</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    oci_close($conn);
    
    echo "<p><a href='simple_connect.php'>Go to simple connection test</a></p>";
}

// Show PHP info about OCI
echo "<hr><h3>OCI8 Module Info:</h3>";
ob_start();
phpinfo(INFO_MODULES);
$info = ob_get_clean();

// Extract OCI8 section
if (preg_match('/<h2>OCI8.*?<table.*?<\/table>/is', $info, $matches)) {
    echo $matches[0];
}
?>