<?php
// Simple debug page for database commands
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Commands Debug</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .command { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        pre { background-color: #f5f5f5; padding: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Database Commands Debug</h1>
    
    <?php
    try {
        // Connect to SQLite database
        $db = new SQLite3('blog.sqlite');
        
        // Query all commands
        $result = $db->query('SELECT id, nazev, popis, prikaz FROM spawners');
        
        $hasCommands = false;
        echo '<h2>Commands in Database:</h2>';
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $hasCommands = true;
            echo '<div class="command">';
            echo '<strong>ID:</strong> ' . htmlspecialchars($row['id']) . '<br>';
            echo '<strong>Name:</strong> ' . htmlspecialchars($row['nazev'] ?? 'N/A') . '<br>';
            echo '<strong>Description:</strong> ' . htmlspecialchars($row['popis'] ?? 'N/A') . '<br>';
            echo '<strong>Command:</strong> <pre>' . htmlspecialchars($row['prikaz'] ?? 'N/A') . '</pre>';
            echo '</div>';
        }
        
        if (!$hasCommands) {
            echo '<p>No commands found in the database.</p>';
        }
        
    } catch (Exception $e) {
        echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
</body>
</html>