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

// Funkce pro načtení obsahu z SQLite databáze
function loadContentFromDb() {
    global $team_db_file;
    try {
        if (!file_exists($team_db_file)) {
            return null;
        }
        $db = new SQLite3($team_db_file);
        $query = $db->query("SELECT content FROM team_content WHERE id = 1 ORDER BY updated_at DESC LIMIT 1");
        $result = $query ? $query->fetchArray(SQLITE3_ASSOC) : null;
        $db->close();
        return $result ? $result['content'] : null;
    } catch (Exception $e) {
        error_log("SQLite read error: " . $e->getMessage());
        return null;
    }
}

try {
    // Načtení obsahu z databáze
    $content = loadContentFromDb();
    
    if ($content === null) {
        $response = ['success' => false, 'error' => 'Nepodařilo se načíst obsah z databáze'];
    } else {
        // Pokus o zápis do souboru
        $result = @file_put_contents($team_content_file, $content);
        
        if ($result === false) {
            // Alternativní metody zápisu
            $success = false;
            
            // Metoda 1: Pomocí fopen a fwrite
            $fp = @fopen($team_content_file, 'w');
            if ($fp) {
                fwrite($fp, $content);
                fclose($fp);
                $success = true;
            } else {
                // Metoda 2: Pomocí dočasného souboru
                $temp_file = tempnam(sys_get_temp_dir(), 'team_restore');
                if ($temp_file !== false) {
                    if (file_put_contents($temp_file, $content) !== false) {
                        if (copy($temp_file, $team_content_file)) {
                            @unlink($temp_file);
                            $success = true;
                        } else {
                            @unlink($temp_file);
                        }
                    } else {
                        @unlink($temp_file);
                    }
                }
            }
            
            if ($success) {
                $response = ['success' => true, 'message' => 'Obsah byl úspěšně obnoven z databáze'];
            } else {
                $response = [
                    'success' => false, 
                    'error' => 'Nepodařilo se zapsat obsah do souboru. Zkontrolujte oprávnění.',
                    'content' => $content  // Vracíme obsah, aby mohl být vložen do editoru manuálně
                ];
            }
        } else {
            $response = ['success' => true, 'message' => 'Obsah byl úspěšně obnoven z databáze'];
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'error' => 'Chyba: ' . $e->getMessage()];
}

// Odeslání odpovědi ve formátu JSON
header('Content-Type: application/json');
echo json_encode($response);