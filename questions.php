<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    
    public function testConnection() {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

class QuestionManager {
    private $pdo;
    
    public function __construct() {
        $dbConfig = new DatabaseConfig();
        $this->pdo = $dbConfig->getConnection();
    }
    
    private function getQuestionTypeSQL() {
        return "CASE WHEN option_1 IS NOT NULL AND option_2 IS NOT NULL AND option_3 IS NOT NULL AND option_4 IS NOT NULL THEN '单选题' ELSE '判断题' END";
    }
    
    private function removeQuestionFromSets($questionId) {
        $checkSql = "SELECT id, question_ids FROM question_sets WHERE question_ids IS NOT NULL AND question_ids != '' AND FIND_IN_SET(?, question_ids) > 0";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([$questionId]);
        $usedInSets = $checkStmt->fetchAll();
        
        $removedFromSets = [];
        foreach ($usedInSets as $set) {
            $questionIds = array_filter(explode(',', $set['question_ids']), function($qid) use ($questionId) {
                return trim($qid) != $questionId;
            });
            
            $updateSql = "UPDATE question_sets SET question_ids = ? WHERE id = ?";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([implode(',', $questionIds), $set['id']]);
            
            $removedFromSets[] = $set['id'];
        }
        
        return $removedFromSets;
    }
    
    public function getQuestions($page = 1, $limit = 10, $search = '', $category = '', $excludeUsed = false) {
        $offset = ($page - 1) * $limit;
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = '(q.id LIKE ? OR q.question LIKE ? OR q.knowledge_category LIKE ?)';
            $searchParam = '%' . $search . '%';
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($category)) {
            $whereConditions[] = 'q.knowledge_category = ?';
            $params[] = $category;
        }
        
        if ($excludeUsed) {
            $usedQuestionIds = [];
            $usedStmt = $this->pdo->query("SELECT question_ids FROM question_sets WHERE question_ids IS NOT NULL AND question_ids != ''");
            while ($row = $usedStmt->fetch()) {
                $ids = json_decode($row['question_ids'], true);
                if (is_array($ids)) {
                    $usedQuestionIds = array_merge($usedQuestionIds, $ids);
                }
            }
            
            if (!empty($usedQuestionIds)) {
                $placeholders = str_repeat('?,', count($usedQuestionIds) - 1) . '?';
                $whereConditions[] = "q.id NOT IN ($placeholders)";
                $params = array_merge($params, $usedQuestionIds);
            }
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $countSql = "SELECT COUNT(*) as total FROM questions q $whereClause";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        $sql = "SELECT q.*, {$this->getQuestionTypeSQL()} as type FROM questions q $whereClause ORDER BY q.id ASC LIMIT ? OFFSET ?";
        $params = array_merge($params, [$limit, $offset]);
        
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
    
    public function addQuestion($data) {
        $sql = "INSERT INTO questions (question, option_1, option_2, option_3, option_4, correct_answer, explanation, knowledge_category, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['question'],
            $data['option_1'],
            $data['option_2'],
            $data['option_3'],
            $data['option_4'],
            $data['correct_answer'],
            $data['explanation'],
            $data['knowledge_category']
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    public function getQuestion($id) {
        $sql = "SELECT *, {$this->getQuestionTypeSQL()} as type FROM questions WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateQuestion($id, $data) {
        $sql = "UPDATE questions SET 
                question = ?, option_1 = ?, option_2 = ?, option_3 = ?, option_4 = ?, 
                correct_answer = ?, explanation = ?, knowledge_category = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['question'],
            $data['option_1'],
            $data['option_2'],
            $data['option_3'],
            $data['option_4'],
            $data['correct_answer'],
            $data['explanation'],
            $data['knowledge_category'],
            $id
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function deleteQuestion($id) {
        $removedFromSets = $this->removeQuestionFromSets($id);
        
        $sql = "DELETE FROM questions WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        return [
            'success' => $stmt->rowCount() > 0,
            'removed_from_sets' => $removedFromSets
        ];
    }
    
    public function getCategories() {
        $sql = "SELECT DISTINCT knowledge_category FROM questions WHERE knowledge_category IS NOT NULL AND knowledge_category != '' ORDER BY knowledge_category";
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function batchDeleteQuestions($questionIds) {
        if (empty($questionIds) || !is_array($questionIds)) {
            throw new Exception('题目ID列表不能为空');
        }
        
        $deletedCount = 0;
        $errors = [];
        $allRemovedFromSets = [];
        
        foreach ($questionIds as $id) {
            try {
                $removedFromSets = $this->removeQuestionFromSets($id);
                if (!empty($removedFromSets)) {
                    $allRemovedFromSets[$id] = $removedFromSets;
                }
                
                $sql = "DELETE FROM questions WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    $deletedCount++;
                }
            } catch (Exception $e) {
                $errors[] = "删除题目ID {$id} 时出错: " . $e->getMessage();
            }
        }
        
        return [
            'deleted_count' => $deletedCount,
            'total_count' => count($questionIds),
            'errors' => $errors,
            'removed_from_sets' => $allRemovedFromSets
        ];
    }
    
    public function batchImportQuestions($filePath) {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // 跳过标题行
            array_shift($rows);
            
            $imported = 0;
            $errors = [];
            
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // 考虑标题行
                
                // 检查格式和必填字段
                if (count($row) < 8) {
                    $errors[] = "第{$rowNumber}行格式错误";
                    continue;
                }
                
                // 检查所有字段都不能为空
                $emptyFields = [];
                $fieldNames = ['题目内容', '选项A', '选项B', '选项C', '选项D', '正确答案', '知识点分类', '解析'];
                for ($i = 0; $i < 8; $i++) {
                    $value = trim($row[$i]);
                    if ($value === '' || $value === null) {
                        $emptyFields[] = $fieldNames[$i];
                    }
                }
                
                if (!empty($emptyFields)) {
                    $errors[] = "第{$rowNumber}行有未填写的信息：" . implode('、', $emptyFields);
                    continue;
                }
                
                try {
                    $data = array_combine(
                        ['question', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_answer', 'knowledge_category', 'explanation'],
                        array_slice($row, 0, 8)
                    );
                    
                    $this->addQuestion($data);
                    $imported++;
                } catch (Exception $e) {
                    $errors[] = "第{$rowNumber}行：" . $e->getMessage();
                }
            }
            
            return [
                'imported' => $imported,
                'errors' => $errors,
                'total' => count($rows)
            ];
        } catch (Exception $e) {
            throw new Exception('文件解析失败: ' . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'download_template') {
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['题目内容', '选项A', '选项B', '选项C', '选项D', '正确答案', '知识点分类', '解析'];
        $sheet->fromArray($headers, null, 'A1');
            
        $exampleData = [
                ['计算机的基本组成包括哪些部分？', '运算器和控制器', '存储器', '输入输出设备', '以上都是', 'D', '计算机基础', '计算机由运算器、控制器、存储器、输入设备和输出设备组成'],
                ['下列哪个不是操作系统的功能？', '进程管理', '内存管理', '文件管理', '程序编译', 'D', '操作系统', '程序编译是编译器的功能，不是操作系统的功能'],
                ['', '', '', '', '', '', '', ''],
                ['【重要提示】请删除本行及以下所有说明行，只保留实际题目数据！', '', '', '', '', '', '', ''],
                ['使用说明：', '', '', '', '', '', '', ''],
                ['1. 题目内容：填写完整的题目描述（必填）', '', '', '', '', '', '', ''],
                ['2. 选项A-D：填写四个选择项（必填）', '', '', '', '', '', '', ''],
                ['3. 正确答案：只能填写A、B、C或D其中一个（必填）', '', '', '', '', '', '', ''],
                ['4. 知识点分类：填写题目所属的知识点（必填）', '', '', '', '', '', '', ''],
                ['5. 解析：填写题目的详细解答（必填）', '', '', '', '', '', '', ''],
                ['6. 导入前请务必删除示例数据和所有说明行！', '', '', '', '', '', '', ''],
                ['7. 每行必须包含完整的题目信息，缺少必填项会导入失败', '', '', '', '', '', '', '']
            ];
            $sheet->fromArray($exampleData, null, 'A2');
            
            // 设置样式
            $styles = [
                'A1:H1' => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]],
                'A2:H3' => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']]],
                'A5:H5' => ['font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6E6']]],
                'A6:H13' => ['font' => ['italic' => true, 'color' => ['rgb' => '666666']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]]
            ];
            
            foreach ($styles as $range => $style) {
                $sheet->getStyle($range)->applyFromArray($style);
            }
            
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
        $headers = [
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition: attachment;filename="题目导入模板.xlsx"',
            'Cache-Control: max-age=0'
        ];
        array_map('header', $headers);
        
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo '模板生成失败: ' . $e->getMessage();
        exit;
    }
}

// 处理AJAX请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? '';
        $questionManager = new QuestionManager();
        
        switch ($action) {
            case 'test_connection':
                try {
                    (new DatabaseConfig())->getConnection()->query('SELECT 1');
                    echo json_encode(['success' => true, 'message' => '数据库连接正常']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => '数据库连接失败: ' . $e->getMessage()]);
                }
                break;
                
            case 'get_questions':
                $page = intval($_POST['page'] ?? 1);
                $limit = intval($_POST['per_page'] ?? $_POST['limit'] ?? 10);
                $search = $_POST['search'] ?? '';
                $category = $_POST['category'] ?? '';
                $excludeUsed = isset($_POST['exclude_used']) ? (bool)$_POST['exclude_used'] : false;
                
                $result = $questionManager->getQuestions($page, $limit, $search, $category, $excludeUsed);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'add_question':
                $data = [
                    'question' => $_POST['question'] ?? '',
                    'option_1' => $_POST['option_1'] ?? '',
                    'option_2' => $_POST['option_2'] ?? '',
                    'option_3' => $_POST['option_3'] ?? '',
                    'option_4' => $_POST['option_4'] ?? '',
                    'correct_answer' => $_POST['correct_answer'] ?? '',
                    'explanation' => $_POST['explanation'] ?? '',
                    'knowledge_category' => $_POST['knowledge_category'] ?? ''
                ];
                
                $id = $questionManager->addQuestion($data);
                echo json_encode(['success' => true, 'message' => '题目添加成功', 'id' => $id]);
                break;
                
            case 'get_question':
                $id = intval($_POST['id'] ?? 0);
                $question = $questionManager->getQuestion($id);
                echo json_encode(['success' => true, 'data' => $question]);
                break;
                
            case 'update_question':
                $id = intval($_POST['id'] ?? 0);
                $data = [
                    'question' => $_POST['question'] ?? '',
                    'option_1' => $_POST['option_1'] ?? '',
                    'option_2' => $_POST['option_2'] ?? '',
                    'option_3' => $_POST['option_3'] ?? '',
                    'option_4' => $_POST['option_4'] ?? '',
                    'correct_answer' => $_POST['correct_answer'] ?? '',
                    'explanation' => $_POST['explanation'] ?? '',
                    'knowledge_category' => $_POST['knowledge_category'] ?? ''
                ];
                
                $questionManager->updateQuestion($id, $data);
                echo json_encode(['success' => true, 'message' => '题目更新成功']);
                break;
                
            case 'delete_question':
                $id = intval($_POST['id'] ?? 0);
                $result = $questionManager->deleteQuestion($id);
                
                $message = '题目删除成功';
                if (!empty($result['removed_from_sets'])) {
                    $setNumbers = implode('、', $result['removed_from_sets']);
                    $message .= "，已从套题 {$setNumbers} 中移除该题目";
                }
                
                echo json_encode([
                    'success' => $result['success'], 
                    'message' => $message,
                    'removed_from_sets' => $result['removed_from_sets']
                ]);
                break;
                
            case 'get_categories':
                $categories = $questionManager->getCategories();
                echo json_encode(['success' => true, 'data' => $categories]);
                break;
                
            case 'batch_import':
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('文件上传失败');
                }
                
                $result = $questionManager->batchImportQuestions($_FILES['file']['tmp_name']);
                
                // 如果有验证错误，返回失败状态
                if (!empty($result['errors'])) {
                    $message = "导入完成，但存在错误：\n" . implode('；', $result['errors']);
                    if ($result['imported'] > 0) {
                        $message = "成功导入 {$result['imported']} 道题目，但存在错误：\n" . implode('；', $result['errors']);
                    }
                    echo json_encode(['success' => false, 'message' => $message, 'data' => $result]);
                } else {
                    echo json_encode(['success' => true, 'message' => "成功导入 {$result['imported']} 道题目", 'data' => $result]);
                }
                break;
                
            case 'check_question_usage':
                $id = intval($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new Exception('无效的题目ID');
                }
                
                $pdo = (new DatabaseConfig())->getConnection();
                $checkStmt = $pdo->prepare("SELECT id, question_ids FROM question_sets WHERE question_ids IS NOT NULL AND question_ids != '' AND FIND_IN_SET(?, question_ids) > 0");
                $checkStmt->execute([$id]);
                $usedInSets = $checkStmt->fetchAll();
                
                $setIds = array_column($usedInSets, 'id');
                
                echo json_encode([
                    'success' => true,
                    'is_used' => !empty($setIds),
                    'used_in_sets' => $setIds
                ]);
                break;
                
            case 'batch_check_question_usage':
                if (!isset($_POST['question_ids'])) {
                    throw new Exception('缺少题目ID参数');
                }
                
                $questionIds = json_decode($_POST['question_ids'], true);
                if (!is_array($questionIds) || empty($questionIds)) {
                    throw new Exception('题目ID参数格式错误');
                }
                
                $pdo = (new DatabaseConfig())->getConnection();
                $checkStmt = $pdo->prepare("SELECT id, question_ids FROM question_sets WHERE question_ids IS NOT NULL AND question_ids != '' AND FIND_IN_SET(?, question_ids) > 0");
                $usageInfo = [];
                
                foreach ($questionIds as $id) {
                    $checkStmt->execute([$id]);
                    $setIds = array_column($checkStmt->fetchAll(), 'id');
                    if (!empty($setIds)) {
                        $usageInfo[$id] = $setIds;
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'usage_info' => $usageInfo
                ]);
                break;
                
            case 'batch_delete_questions':
                if (!isset($_POST['question_ids'])) {
                    throw new Exception('缺少题目ID参数');
                }
                
                $questionIds = json_decode($_POST['question_ids'], true);
                if (!is_array($questionIds) || empty($questionIds)) {
                    throw new Exception('题目ID参数格式错误');
                }
                
                $result = $questionManager->batchDeleteQuestions($questionIds);
                
                $message = "成功删除 {$result['deleted_count']} 道题目";
                if (!empty($result['removed_from_sets'])) {
                    $affectedSets = [];
                    foreach ($result['removed_from_sets'] as $questionId => $setIds) {
                        foreach ($setIds as $setId) {
                            $affectedSets[] = $setId;
                        }
                    }
                    $affectedSets = array_unique($affectedSets);
                    if (!empty($affectedSets)) {
                        $setNumbers = implode('、', $affectedSets);
                        $message .= "，已从套题 {$setNumbers} 中移除相关题目";
                    }
                }
                
                if (!empty($result['errors'])) {
                    $message .= "\n错误信息：" . implode('；', $result['errors']);
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'data' => $result
                ]);
                break;
                
            default:
                throw new Exception('未知操作');
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}