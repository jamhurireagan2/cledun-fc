<?php
/**
 * CLEDUN FC - Mobile App API
 * Returns JSON data for the mobile app
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = getDB();
$action = $_GET['action'] ?? 'home';

try {
    switch ($action) {

        // === HOME ===
        case 'home':
            $data = [
                'settings' => getSettingsArray($db),
                'upcoming_matches' => $db->query("
                    SELECT m.*, c.name AS category_name, c.icon AS category_icon
                    FROM matches m
                    LEFT JOIN categories c ON m.category_id = c.id
                    WHERE m.match_date >= NOW() AND m.status != 'cancelled'
                    ORDER BY m.match_date ASC LIMIT 5
                ")->fetchAll(),
                'latest_news' => $db->query("
                    SELECT id, title, excerpt, featured_image, category, created_at
                    FROM news WHERE is_published = 1
                    ORDER BY created_at DESC LIMIT 3
                ")->fetchAll(),
                'gallery_preview' => $db->query("
                    SELECT id, title, image_path, category
                    FROM gallery WHERE is_active = 1
                    ORDER BY display_order ASC LIMIT 10
                ")->fetchAll(),
                'videos_preview' => $db->query("
                    SELECT id, title, thumbnail, video_url, video_file, category
                    FROM videos WHERE is_active = 1
                    ORDER BY display_order ASC LIMIT 3
                ")->fetchAll()
            ];
            break;

        // === SQUAD ===
        case 'squad':
            $categoryId = intval($_GET['category'] ?? 0);
            $sql = "SELECT p.*, c.name AS category_name, c.icon AS category_icon
                    FROM players p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_active = 1";
            if ($categoryId > 0) $sql .= " AND p.category_id = " . $categoryId;
            $sql .= " ORDER BY p.category_id, p.is_captain DESC, p.jersey_number ASC";
            $data = ['players' => $db->query($sql)->fetchAll()];
            break;

        // === CATEGORIES ===
        case 'categories':
            $data = ['categories' => getActiveCategories()];
            break;

        // === MATCHES ===
        case 'matches':
            $status = $_GET['status'] ?? 'all';
            $sql = "SELECT m.*, c.name AS category_name, c.icon AS category_icon
                    FROM matches m
                    LEFT JOIN categories c ON m.category_id = c.id";
            if ($status !== 'all') $sql .= " WHERE m.status = " . $db->quote($status);
            $sql .= " ORDER BY m.match_date DESC";
            $data = ['matches' => $db->query($sql)->fetchAll()];
            break;

        // === NEWS ===
        case 'news':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $data = [
                'news' => $db->query("
                    SELECT id, title, excerpt, content, featured_image, category, created_at, view_count
                    FROM news WHERE is_published = 1
                    ORDER BY created_at DESC LIMIT $limit OFFSET $offset
                ")->fetchAll(),
                'page' => $page,
                'has_more' => true
            ];
            break;

        // === SINGLE NEWS ===
        case 'news-detail':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("
                SELECT n.*, u.full_name AS author_name
                FROM news n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.id = ? AND n.is_published = 1
            ");
            $stmt->execute([$id]);
            $article = $stmt->fetch();
            if ($article) {
                $db->prepare("UPDATE news SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
                $data = ['article' => $article];
            } else {
                throw new Exception('Article not found');
            }
            break;

        // === GALLERY ===
        case 'gallery':
            $data = ['images' => $db->query("
                SELECT id, title, image_path, category
                FROM gallery WHERE is_active = 1
                ORDER BY display_order ASC, created_at DESC
            ")->fetchAll()];
            break;

        // === VIDEOS ===
        case 'videos':
            $data = ['videos' => $db->query("
                SELECT id, title, description, thumbnail, video_url, video_file, category
                FROM videos WHERE is_active = 1
                ORDER BY display_order ASC
            ")->fetchAll()];
            break;

        // === TEAMS (for registration form) ===
        case 'teams':
            $data = ['teams' => getActiveCategories()];
            break;

        // === REGISTER PLAYER ===
        case 'register-player':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('POST required');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            $required = ['first_name', 'last_name', 'gender', 'birth_date', 'nationality',
                         'school', 'phone', 'category_id', 'signatory_name'];

            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email');
            }

            // Age check
            $ageLimits = [
                'U8' => [6, 8], 'U10' => [8, 10], 'U13' => [11, 13],
                'U17' => [14, 17], 'Senior' => [18, 99]
            ];
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([intval($input['category_id'])]);
            $cat = $stmt->fetch();
            if (!$cat) throw new Exception('Invalid category');

            $age = (new DateTime($input['birth_date']))->diff(new DateTime())->y;
            $limit = $ageLimits[$cat['name']] ?? null;
            if ($limit && ($age < $limit[0] || $age > $limit[1])) {
                throw new Exception("Age $age does not match {$cat['name']}");
            }

            $db->beginTransaction();
            $stmt = $db->prepare("
                INSERT INTO player_registrations
                (category_id, first_name, last_name, gender, birth_date, language, nationality,
                 email, phone, allergies, medical_comment, school, notify_by, referral_source,
                 signatory_name, agreement_accepted, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'pending')
            ");
            $stmt->execute([
                intval($input['category_id']),
                $input['first_name'], $input['last_name'],
                $input['gender'], $input['birth_date'],
                $input['language'] ?? '', $input['nationality'],
                $input['email'] ?? '', $input['phone'],
                $input['allergies'] ?? '', $input['medical_comment'] ?? '',
                $input['school'], $input['notify_by'] ?? 'Email',
                $input['referral_source'] ?? '', $input['signatory_name']
            ]);
            $regId = $db->lastInsertId();
            $db->commit();

            $data = ['success' => true, 'registration_id' => $regId];
            break;

        default:
            throw new Exception('Unknown action');
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getSettingsArray($db) {
    $rows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
    return $out;
}