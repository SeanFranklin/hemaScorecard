<?php
/*******************************************************************************
    Migration Runner
    Scans migrations/*.sql and applies any that haven't been run yet.
    Intended to run on container startup before the PHP server starts.
*******************************************************************************/

// database.php uses DEPLOYMENT_UNKNOWN which is normally defined in config.php
define("DEPLOYMENT_UNKNOWN", 0);
require_once __DIR__ . '/../includes/database.php';

$conn = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, PRIMARY_DATABASE);
if ($conn->connect_error) {
    echo "[migrations] ERROR: Database connection failed: {$conn->connect_error}\n";
    exit(1);
}

// Create migrations tracking table
$conn->query("
    CREATE TABLE IF NOT EXISTS migrations (
        filename VARCHAR(255) NOT NULL PRIMARY KEY,
        ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");
if ($conn->error) {
    echo "[migrations] ERROR: Could not create migrations table: {$conn->error}\n";
    exit(1);
}

// Find all .sql files in this directory
$files = glob(__DIR__ . '/*.sql');
sort($files);

if (empty($files)) {
    echo "[migrations] No migration files found.\n";
    $conn->close();
    exit(0);
}

foreach ($files as $file) {
    $basename = basename($file);

    // Check if already ran
    $stmt = $conn->prepare("SELECT 1 FROM migrations WHERE filename = ?");
    $stmt->bind_param('s', $basename);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "[migrations] Skipping {$basename} (already applied)\n";
        $stmt->close();
        continue;
    }
    $stmt->close();

    // Read and execute the migration
    echo "[migrations] Applying {$basename}...\n";
    $sql = file_get_contents($file);

    if ($conn->multi_query($sql)) {
        // Drain all result sets from multi_query
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }

    if ($conn->error) {
        echo "[migrations] ERROR applying {$basename}: {$conn->error}\n";
        exit(1);
    }

    // Record successful migration
    $stmt = $conn->prepare("INSERT INTO migrations (filename) VALUES (?)");
    $stmt->bind_param('s', $basename);
    $stmt->execute();
    $stmt->close();

    echo "[migrations] Applied {$basename} successfully.\n";
}

echo "[migrations] All migrations complete.\n";
$conn->close();
