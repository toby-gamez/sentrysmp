<?php
// Ochrana přístupu - pouze pro přihlášené administrátory
session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

// Definice souborů
$team_content_file = "team_content.html";
$team_db_file = "team_content.sqlite";

// Výchozí odpověď
$response = ['success' => false, 'error' => 'Unknown error'];

// Získání dat z POST požadavku
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['content'])) {
    $response = ['success' => false, 'error' => 'Nebyl poskytnut žádný obsah'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$content = $data['content'];

// Použijeme více metod pro vytvoření souboru
try {
    $methods_tried = [];
    $success = false;

    // Metoda 1: touch + file_put_contents
    $methods_tried[] = 'touch + file_put_contents';
    if (!file_exists($team_content_file)) {
        @touch($team_content_file);
    }
    if (file_exists($team_content_file)) {
        @chmod($team_content_file, 0644);
        if (@file_put_contents($team_content_file, $content) !== false) {
            $success = true;
        }
    }

    // Metoda 2: fopen + fwrite
    if (!$success) {
        $methods_tried[] = 'fopen + fwrite';
        $fp = @fopen($team_content_file, 'w');
        if ($fp) {
            if (@fwrite($fp, $content)) {
                $success = true;
            }
            @fclose($fp);
            @chmod($team_content_file, 0644);
        }
    }

    // Metoda 3: tempnam + rename
    if (!$success) {
        $methods_tried[] = 'tempnam + rename';
        $temp_file = tempnam(sys_get_temp_dir(), 'team');
        if ($temp_file) {
            if (@file_put_contents($temp_file, $content) !== false) {
                if (@rename($temp_file, $team_content_file)) {
                    $success = true;
                    @chmod($team_content_file, 0644);
                } else {
                    @unlink($temp_file);
                }
            } else {
                @unlink($temp_file);
            }
        }
    }

    // Metoda 4: tempnam + copy
    if (!$success) {
        $methods_tried[] = 'tempnam + copy';
        $temp_file = tempnam(sys_get_temp_dir(), 'team');
        if ($temp_file) {
            if (@file_put_contents($temp_file, $content) !== false) {
                if (@copy($temp_file, $team_content_file)) {
                    $success = true;
                    @chmod($team_content_file, 0644);
                }
                @unlink($temp_file);
            } else {
                @unlink($temp_file);
            }
        }
    }

    // Uložení do SQLite jako záložní řešení
    $db_saved = false;
    if (class_exists('SQLite3')) {
        try {
            $methods_tried[] = 'SQLite3 backup';
            $db = new SQLite3($team_db_file);
            $db->exec("CREATE TABLE IF NOT EXISTS team_content (id INTEGER PRIMARY KEY, content TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $stmt = $db->prepare("INSERT OR REPLACE INTO team_content (id, content, updated_at) VALUES (1, :content, CURRENT_TIMESTAMP)");
            $stmt->bindParam(':content', $content, SQLITE3_TEXT);
            $result = $stmt->execute();
            $db->close();
            $db_saved = ($result !== false);
        } catch (Exception $e) {
            $methods_tried[] = 'SQLite3 failed: ' . $e->getMessage();
        }
    } else {
        $methods_tried[] = 'SQLite3 not available';
    }

    // Výsledná odpověď
    if ($success) {
        $response = [
            'success' => true,
            'message' => 'Soubor byl úspěšně vytvořen.',
            'methods' => $methods_tried
        ];
    } else if ($db_saved) {
        $response = [
            'success' => true,
            'message' => 'Nepodařilo se vytvořit soubor, ale obsah byl uložen do databáze.',
            'methods' => $methods_tried
        ];
    } else {
        $response = [
            'success' => false,
            'error' => 'Nepodařilo se vytvořit soubor ani zapsat do databáze.',
            'methods' => $methods_tried,
            'directory_writable' => is_writable(dirname($team_content_file)),
            'server_path' => $_SERVER['DOCUMENT_ROOT']
        ];
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Chyba: ' . $e->getMessage(),
        'methods' => $methods_tried ?? []
    ];
}

// Odeslání odpovědi ve formátu JSON
header('Content-Type: application/json');
echo json_encode($response);
?>