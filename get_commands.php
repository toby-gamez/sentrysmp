<?php
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Connect to the SQLite database
    $db = new SQLite3('blog.sqlite');
    
    // Get commands from spawners table
        $stmt = $db->prepare('SELECT id, nazev, popis, prikaz FROM spawners WHERE prikaz IS NOT NULL');
    $result = $stmt->execute();
    
    $commands = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Make sure to handle potential empty values
        $commands[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['nazev'] ?: 'Command'),
            'description' => htmlspecialchars($row['popis'] ?: ''),
            'command' => trim($row['prikaz'])
        ];
    }
    
    if (count($commands) > 0) {
        // Debug info
        error_log("Found " . count($commands) . " commands in database");
        foreach ($commands as $index => $cmd) {
            error_log("Command $index: " . $cmd['command']);
        }
        
        echo json_encode([
            'success' => true,
            'commands' => $commands,
            'count' => count($commands)
        ]);
    } else {
        error_log("No commands found in database");
        echo json_encode([
            'success' => false,
            'message' => 'No commands found in the database',
            'commands' => []
        ]);
    }
    
} catch (Exception $e) {
    // Handle any errors
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>