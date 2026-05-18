<?php
/**
 * init_database.php
 *
 * Create the project database and tables using the SQL schema file.
 * Run this script once from your browser or CLI, then remove it for security.
 */

$host = 'localhost';
$username = 'root';
$password = '';
$sqlFile = __DIR__ . '/Database/sit_in_system_clean.sql';

function display($message)
{
    if (php_sapi_name() === 'cli') {
        echo $message . PHP_EOL;
    } else {
        echo '<pre>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
}

function loadSqlFile($path)
{
    if (!file_exists($path)) {
        throw new RuntimeException("SQL file not found: {$path}");
    }

    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Unable to read SQL file: {$path}");
    }

    // Remove comments and normalize line endings.
    $sql = preg_replace('/\r\n|\r/', "\n", $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $lines = explode("\n", $sql);
    $cleaned = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
            continue;
        }

        $cleaned .= $line . "\n";
    }

    $statements = preg_split('/;\s*\n/', $cleaned);
    $queries = [];

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }
        $queries[] = $statement;
    }

    return $queries;
}

try {
    display('Connecting to MySQL server...');
    $mysqli = new mysqli($host, $username, $password);

    if ($mysqli->connect_error) {
        throw new RuntimeException('Connection failed: ' . $mysqli->connect_error);
    }

    display('Loading SQL schema from: ' . $sqlFile);
    $queries = loadSqlFile($sqlFile);

    display('Executing ' . count($queries) . ' SQL statements...');

    foreach ($queries as $index => $query) {
        $statementNumber = $index + 1;
        if (!$mysqli->query($query)) {
            throw new RuntimeException("[Statement {$statementNumber}] Error: " . $mysqli->error . "\nQuery: " . $query);
        }
        display("[Statement {$statementNumber}] OK");
    }

    display('Database initialization complete.');
    display('Verify the database in phpMyAdmin or run the app.');
    display('If you are using this in production, delete init_database.php after successful execution.');
} catch (Throwable $e) {
    display('ERROR: ' . $e->getMessage());
    exit(1);
}
