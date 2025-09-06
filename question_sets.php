<?php
// 数据库配置类
class DatabaseConfig {
    private $config;
    private $pdo;
    
    public function __construct() {
        $this->loadConfig();
        $this->connect();
    }
    
    private function loadConfig() {
        $configFile = __DIR__ . '/config.json';
        if (!file_exists($configFile)) {
            throw new Exception('配置文件不存在');
        }
        
        $configContent = file_get_contents($configFile);
        $this->config = json_decode($configContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('配置文件格式错误');
        }
    }
    
    private function connect() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['database']['host'],
                $this->config['database']['port'],
                $this->config['database']['database_name'],
                $this->config['database']['charset']
            );
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['database']['username'],
                $this->config['database']['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// 套题管理类
class QuestionSetManager {
    private $pdo;
    
    public function __construct() {
        $dbConfig = new DatabaseConfig();
        $this->pdo = $dbConfig->getConnection();
    }
    
    public function getQuestionSets($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = 'qs.id LIKE ?';
            $params[] = '%' . $search . '%';
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM question_sets qs $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // 获取数据
        $sql = "SELECT qs.*, 
                       CASE 
                           WHEN qs.question_ids IS NULL OR qs.question_ids = '' THEN 0
                           ELSE (LENGTH(qs.question_ids) - LENGTH(REPLACE(qs.question_ids, ',', '')) + 1)
                       END as question_count
                FROM question_sets qs 
                $whereClause 
                ORDER BY qs.id ASC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $questionSets = $stmt->fetchAll();
        
        // 为每个套题添加知识点信息
        foreach ($questionSets as &$set) {
            if (!empty($set['question_ids'])) {
                $questionIds = explode(',', $set['question_ids']);
                $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
                $knowledgeSql = "SELECT DISTINCT knowledge_category FROM questions WHERE id IN ($placeholders)";
                $knowledgeStmt = $this->pdo->prepare($knowledgeSql);
                $knowledgeStmt->execute($questionIds);
                $categories = $knowledgeStmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($categories) > 0) {
                    $set['knowledge_points'] = implode(', ', array_unique($categories));
                } else {
                    $set['knowledge_points'] = '无知识点';
                }
            } else {
                $set['knowledge_points'] = '无题目';
            }
        }
        
        return [
            'question_sets' => $questionSets,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function addQuestionSet($data) {
        // 验证套题编号是否已存在
        $checkSql = "SELECT COUNT(*) as count FROM question_sets WHERE id = ?";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([$data['set_number']]);
        
        if ($checkStmt->fetch()['count'] > 0) {
            throw new Exception('套题编号已存在');
        }
        
        // 验证题目ID
        // 如果是JSON字符串，先解析；如果是逗号分隔的字符串，直接分割
        if (is_string($data['question_ids']) && (strpos($data['question_ids'], '[') === 0 || strpos($data['question_ids'], '{') === 0)) {
            $questionIds = json_decode($data['question_ids'], true);
            if (!is_array($questionIds)) {
                throw new Exception('题目ID格式错误');
            }
        } else {
            $questionIds = array_filter(explode(',', $data['question_ids']));
        }
        
        if (empty($questionIds)) {
            throw new Exception('请选择题目');
        }
        
        $this->validateQuestionIds($questionIds);
        
        try {
            // 插入套题
            $sql = "INSERT INTO question_sets (id, question_ids, created_at) VALUES (?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['set_number'], implode(',', $questionIds)]);
            
            return $data['set_number'];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getQuestionSet($setNumber) {
        $sql = "SELECT * FROM question_sets WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$setNumber]);
        
        return $stmt->fetch();
    }
    
    public function updateQuestionSet($setNumber, $data) {
        // 验证题目ID
        // 如果是JSON字符串，先解析；如果是逗号分隔的字符串，直接分割
        if (is_string($data['question_ids']) && (strpos($data['question_ids'], '[') === 0 || strpos($data['question_ids'], '{') === 0)) {
            $questionIds = json_decode($data['question_ids'], true);
            if (!is_array($questionIds)) {
                throw new Exception('题目ID格式错误');
            }
        } else {
            $questionIds = array_filter(explode(',', $data['question_ids']));
        }
        
        if (empty($questionIds)) {
            throw new Exception('请选择题目');
        }
        
        $this->validateQuestionIds($questionIds);
        
        try {
            // 更新套题的题目ID列表
            $sql = "UPDATE question_sets SET question_ids = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([implode(',', $questionIds), $setNumber]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function deleteQuestionSet($setNumber) {
        try {
            // 删除套题
            $deleteSetSql = "DELETE FROM question_sets WHERE id = ?";
            $deleteSetStmt = $this->pdo->prepare($deleteSetSql);
            $deleteSetStmt->execute([$setNumber]);
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getQuestionSetDetails($setNumber) {
        // 首先获取套题信息
        $setSql = "SELECT * FROM question_sets WHERE id = ?";
        $setStmt = $this->pdo->prepare($setSql);
        $setStmt->execute([$setNumber]);
        $questionSet = $setStmt->fetch();
        
        if (!$questionSet || empty($questionSet['question_ids'])) {
            return [];
        }
        
        // 获取题目详情
        $questionIds = explode(',', $questionSet['question_ids']);
        $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
        
        $sql = "SELECT q.id, q.question, q.knowledge_category,
                       q.option_1, q.option_2, q.option_3, q.option_4,
                       q.correct_answer,
                       CASE 
                           WHEN q.option_1 IS NOT NULL AND q.option_2 IS NOT NULL AND q.option_3 IS NOT NULL AND q.option_4 IS NOT NULL 
                           THEN '单选题' 
                           ELSE '判断题' 
                       END as type
                FROM questions q 
                WHERE q.id IN ($placeholders) 
                ORDER BY FIELD(q.id, $placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($questionIds, $questionIds));
        
        $questions = $stmt->fetchAll();
        
        // 为每个题目添加套题信息
        foreach ($questions as &$question) {
            $question['set_id'] = $questionSet['id'];
            $question['created_at'] = $questionSet['created_at'];
            $question['updated_at'] = $questionSet['updated_at'];
        }
        
        return $questions;
    }
    
    /**
     * 获取编辑套题时需要的题目列表
     * 包括未被任何套题使用的题目和当前套题已选中的题目
     */
    public function getQuestionsForEdit($setNumber) {
        try {
            // 获取未被使用的题目
            $questionManager = new QuestionManager();
            $availableQuestions = $questionManager->getQuestions(1, 1000, '', '', true);
            
            // 获取当前套题的题目
            $currentQuestions = $this->getQuestionSetDetails($setNumber);
            
            // 合并题目列表并去重
            $questionMap = [];
            
            // 添加未被使用的题目
            foreach ($availableQuestions['questions'] as $question) {
                $questionMap[$question['id']] = $question;
            }
            
            // 添加当前套题的题目（可能与未使用题目重复，但会被去重）
            foreach ($currentQuestions as $question) {
                $questionMap[$question['id']] = $question;
            }
            
            return array_values($questionMap);
            
        } catch (Exception $e) {
            throw new Exception('获取编辑题目列表失败: ' . $e->getMessage());
        }
    }
    
    private function validateQuestionIds($questionIds) {
        if (empty($questionIds)) {
            throw new Exception('题目ID列表不能为空');
        }
        
        // 确保题目ID都是字符串类型，便于比较
        $questionIds = array_map('strval', $questionIds);
        
        // 检查题目是否存在
        $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
        $sql = "SELECT id FROM questions WHERE id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($questionIds);
        
        $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        // 确保从数据库获取的ID也是字符串类型
        $existingIds = array_map('strval', $existingIds);
        
        $missingIds = array_diff($questionIds, $existingIds);
        
        if (!empty($missingIds)) {
            throw new Exception('题目ID不存在: ' . implode(', ', $missingIds));
        }
        
        return true;
    }
}

// 题目管理类（用于获取可用题目）
class QuestionManager {
    private $pdo;
    
    public function __construct() {
        $dbConfig = new DatabaseConfig();
        $this->pdo = $dbConfig->getConnection();
    }
    
    public function getQuestions($page = 1, $limit = 10, $search = '', $category = '', $excludeUsed = false) {
        $offset = ($page - 1) * $limit;
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = 'q.question LIKE ?';
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($category)) {
            $whereConditions[] = 'q.knowledge_category = ?';
            $params[] = $category;
        }
        
        if ($excludeUsed) {
            // 获取所有已被使用的题目ID
            $usedQuestionsSql = "SELECT question_ids FROM question_sets WHERE question_ids IS NOT NULL AND question_ids != ''";
            $usedQuestionsStmt = $this->pdo->prepare($usedQuestionsSql);
            $usedQuestionsStmt->execute();
            $usedQuestionSets = $usedQuestionsStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $usedQuestionIds = [];
            foreach ($usedQuestionSets as $questionIds) {
                $ids = array_filter(array_map('trim', explode(',', $questionIds)));
                $usedQuestionIds = array_merge($usedQuestionIds, $ids);
            }
            
            $usedQuestionIds = array_unique($usedQuestionIds);
            
            if (!empty($usedQuestionIds)) {
                $placeholders = str_repeat('?,', count($usedQuestionIds) - 1) . '?';
                $whereConditions[] = "q.id NOT IN ($placeholders)";
                $params = array_merge($params, $usedQuestionIds);
            }
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM questions q $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // 获取数据
        $sql = "SELECT q.*, 
                       CASE 
                           WHEN q.option_1 IS NOT NULL AND q.option_2 IS NOT NULL AND q.option_3 IS NOT NULL AND q.option_4 IS NOT NULL 
                           THEN '单选题' 
                           ELSE '判断题' 
                       END as type
                FROM questions q 
                $whereClause 
                ORDER BY q.id ASC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $questions = $stmt->fetchAll();
        
        return [
            'questions' => $questions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
}

// 处理AJAX请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? '';
        $questionSetManager = new QuestionSetManager();
        
        switch ($action) {
            case 'get_question_sets':
                $page = intval($_POST['page'] ?? 1);
                $limit = intval($_POST['per_page'] ?? $_POST['limit'] ?? 10);
                $search = $_POST['search'] ?? '';
                
                $result = $questionSetManager->getQuestionSets($page, $limit, $search);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'add_question_set':
                $data = [
                    'set_number' => $_POST['set_number'] ?? '',
                    'question_ids' => $_POST['question_ids'] ?? ''
                ];
                
                $id = $questionSetManager->addQuestionSet($data);
                echo json_encode(['success' => true, 'message' => '套题创建成功', 'id' => $id]);
                break;
                
            case 'get_question_set':
                $setNumber = $_POST['set_number'] ?? '';
                $questionSet = $questionSetManager->getQuestionSet($setNumber);
                echo json_encode(['success' => true, 'data' => $questionSet]);
                break;
                
            case 'update_question_set':
                $setNumber = $_POST['set_number'] ?? '';
                $data = [
                    'question_ids' => $_POST['question_ids'] ?? ''
                ];
                
                $questionSetManager->updateQuestionSet($setNumber, $data);
                echo json_encode(['success' => true, 'message' => '套题更新成功']);
                break;
                
            case 'delete_question_set':
                $setNumber = $_POST['set_number'] ?? '';
                $questionSetManager->deleteQuestionSet($setNumber);
                echo json_encode(['success' => true, 'message' => '套题删除成功']);
                break;
                
            case 'get_question_set_details':
                $setNumber = $_POST['set_number'] ?? '';
                $details = $questionSetManager->getQuestionSetDetails($setNumber);
                echo json_encode(['success' => true, 'data' => $details]);
                break;
                
            case 'get_available_questions':
                $page = intval($_POST['page'] ?? 1);
                $limit = intval($_POST['limit'] ?? 10);
                $search = $_POST['search'] ?? '';
                $category = $_POST['category'] ?? '';
                $excludeUsed = isset($_POST['exclude_used']) ? (bool)$_POST['exclude_used'] : false;
                
                $questionManager = new QuestionManager();
                $result = $questionManager->getQuestions($page, $limit, $search, $category, $excludeUsed);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'get_questions_for_edit':
                $setNumber = $_POST['set_number'] ?? '';
                if (empty($setNumber)) {
                    throw new Exception('套题编号不能为空');
                }
                
                $questions = $questionSetManager->getQuestionsForEdit($setNumber);
                echo json_encode(['success' => true, 'questions' => $questions]);
                break;
                
            case 'test_connection':
                try {
                    $dbConfig = new DatabaseConfig();
                    $pdo = $dbConfig->getConnection();
                    echo json_encode([
                        'success' => true,
                        'message' => '数据库连接成功'
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => '数据库连接失败: ' . $e->getMessage()
                    ]);
                }
                break;
                
            default:
                throw new Exception('未知操作');
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}