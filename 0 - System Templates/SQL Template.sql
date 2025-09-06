-- 题目表
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '题目ID',
    question TEXT NOT NULL COMMENT '题目内容',
    option_1 VARCHAR(500) NOT NULL COMMENT '选项1',
    option_2 VARCHAR(500) NOT NULL COMMENT '选项2', 
    option_3 VARCHAR(500) NOT NULL COMMENT '选项3',
    option_4 VARCHAR(500) NOT NULL COMMENT '选项4',
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL COMMENT '正确答案',
    explanation TEXT COMMENT '解析',
    knowledge_category VARCHAR(100) NOT NULL COMMENT '知识点分类',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='题目表';

-- 套题表
CREATE TABLE question_sets (
    id INT PRIMARY KEY COMMENT '套题ID',
    question_ids TEXT COMMENT '包含的题目ID列表',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套题表';

-- 创建索引
CREATE INDEX idx_question_id ON questions(id);
CREATE INDEX idx_set_id ON question_sets(id);