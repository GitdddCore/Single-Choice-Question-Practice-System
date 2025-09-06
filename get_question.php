<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 数据库配置类
class DatabaseConfig {
    private static $config = null;
    
    public static function getConfig() {
        if (self::$config === null) {
            $configFile = __DIR__ . '/config.json';
            if (file_exists($configFile)) {
                $configData = json_decode(file_get_contents($configFile), true);
                self::$config = $configData['database'] ?? null;
            }
        }
        return self::$config;
    }
    
    public static function getConnection() {
        $config = self::getConfig();
        if (!$config) {
            throw new Exception('数据库配置未找到');
        }
        
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception('数据库连接失败: ' . $e->getMessage());
        }
    }
}

// 题目管理类
class QuizQuestionManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
    }
    
    // 获取随机题目
    public function getRandomQuestions($limit = 10, $category = null) {
        try {
            $limit = (int)$limit; // 确保是整数
            $sql = "SELECT id, question, option_1, option_2, option_3, option_4, correct_answer, explanation, knowledge_category FROM questions WHERE 1=1";
            $params = [];
            
            if ($category && $category !== 'all') {
                $sql .= " AND knowledge_category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY RAND() LIMIT " . $limit; // 直接拼接LIMIT值
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('获取题目失败: ' . $e->getMessage());
        }
    }
    
    // 获取套题详情
    public function getQuestionSetDetails($setId) {
        try {
            // 获取套题信息
            $setStmt = $this->pdo->prepare("SELECT * FROM question_sets WHERE id = ?");
            $setStmt->execute([$setId]);
            $questionSet = $setStmt->fetch();
            
            if (!$questionSet) {
                throw new Exception('套题不存在');
            }
            
            // 解析题目ID列表
            $questionIds = [];
            if (!empty($questionSet['question_ids'])) {
                $questionIds = explode(',', $questionSet['question_ids']);
                $questionIds = array_map('intval', $questionIds);
            }
            
            $questions = [];
            if (!empty($questionIds)) {
                // 获取套题中的题目
                $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
                $questionsStmt = $this->pdo->prepare("
                    SELECT id, question, option_1, option_2, option_3, option_4, 
                           correct_answer, explanation, knowledge_category
                    FROM questions 
                    WHERE id IN ($placeholders)
                    ORDER BY FIELD(id, $placeholders)
                ");
                $questionsStmt->execute(array_merge($questionIds, $questionIds));
                $questions = $questionsStmt->fetchAll();
            }
            
            return [
                'set_info' => $questionSet,
                'questions' => $questions
            ];
        } catch (Exception $e) {
            throw new Exception('获取套题详情失败: ' . $e->getMessage());
        }
    }
    
    // 获取所有分类
    public function getCategories() {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT knowledge_category FROM questions WHERE knowledge_category IS NOT NULL AND knowledge_category != '' ORDER BY knowledge_category");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            throw new Exception('获取分类失败: ' . $e->getMessage());
        }
    }
    
    // 获取可用的套题列表
    public function getAvailableQuestionSets() {
        try {
            $stmt = $this->pdo->query("
                SELECT id, question_ids, created_at, updated_at,
                       CASE 
                           WHEN question_ids IS NOT NULL AND question_ids != '' 
                           THEN (LENGTH(question_ids) - LENGTH(REPLACE(question_ids, ',', '')) + 1)
                           ELSE 0
                       END as question_count
                FROM question_sets 
                WHERE question_ids IS NOT NULL AND question_ids != ''
                ORDER BY id ASC
            ");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception('获取套题列表失败: ' . $e->getMessage());
        }
    }
}

// 处理请求
try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $manager = new QuizQuestionManager();
    
    switch ($action) {
        case 'get_random_questions':
            $limit = (int)($_GET['limit'] ?? $_POST['limit'] ?? 10);
            $category = $_GET['category'] ?? $_POST['category'] ?? null;
            
            $questions = $manager->getRandomQuestions($limit, $category);
            echo json_encode([
                'success' => true,
                'data' => $questions
            ]);
            break;
            
        case 'get_question_set_details':
            $setId = (int)($_GET['set_id'] ?? $_POST['set_id'] ?? 0);
            
            if (!$setId) {
                throw new Exception('套题ID不能为空');
            }
            
            $result = $manager->getQuestionSetDetails($setId);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'get_categories':
            $categories = $manager->getCategories();
            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
            break;
            
        case 'get_available_question_sets':
            $questionSets = $manager->getAvailableQuestionSets();
            echo json_encode([
                'success' => true,
                'data' => $questionSets
            ]);
            break;
            
        default:
            throw new Exception('无效的操作');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>