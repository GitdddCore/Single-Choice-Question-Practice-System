<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>单选题练习系统</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @font-face {
            font-family: 'HarmonyOS Sans Bold';
            src: url('assets/fonts/HarmonyOS_Sans.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        
        body {
            font-family: 'HarmonyOS Sans Bold', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: bold;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #333;
            margin: 0;
            box-sizing: border-box;
        }
        
        .container {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        h1 {
            color: #333;
            margin-bottom: 8px;
            font-size: 24px;
            font-weight: bold;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 32px;
            font-size: 14px;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .btn {
            padding: 16px 20px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: white;
            color: #333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            border-color: #5a6268;
        }
        
        .btn-secondary:disabled {
            background: #e9ecef;
            color: #6c757d;
            border-color: #e9ecef;
            cursor: not-allowed;
        }
        
        #next-btn {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        #next-btn:hover:not(:disabled) {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        #next-btn:disabled {
            background: #e9ecef;
            color: #6c757d;
            border-color: #e9ecef;
            cursor: not-allowed;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
            border-color: #1e7e34;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
            border-color: #ffc107;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            border-color: #e0a800;
        }
        
        /* 答题页面样式 */
        .quiz-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px 20px;
            background: #f6f8fa;
            border-radius: 8px;
            border: 1px solid #d1d9e0;
        }
        
        .timer {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #0969da;
        }
        
        .question-counter {
            font-weight: 600;
            color: #656d76;
        }
        
        .question-content {
            margin-bottom: 30px;
        }
        
        #question-text {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: #1f2328;
            width: 100%;
            height: 150px;
            max-width: 100%;
            min-width: 100%;
            max-height: 150px;
            min-height: 150px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            overflow-y: auto;
            box-sizing: border-box;
        }
        
        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .option {
            padding: 15px 20px;
            border: 2px solid #d1d9e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            min-height: 60px;
            display: flex;
            align-items: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }
        
        .option:hover {
            border-color: #0969da;
            background: #f6f8fa;
        }
        
        .option.selected {
            border-color: #0969da;
            background: #dbeafe;
        }
        
        .option.correct {
            border-color: #1a7f37;
            background: #dcfce7;
        }
        
        .option.wrong {
            border-color: #d1242f;
            background: #ffeef0;
        }
        
        .explanation {
            margin-top: 20px;
            padding: 15px;
            background: #fff8dc;
            border-left: 4px solid #bf8700;
            border-radius: 4px;
        }
        
        .explanation h4 {
            margin: 0 0 8px 0;
            color: #bf8700;
        }
        
        .quiz-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f1f3f4;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: #0969da;
            transition: width 0.3s ease;
        }
        
        .result-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-height: 80vh;
        }
        
        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .questions-review {
            margin-top: 40px;
            text-align: left;
        }
        
        .questions-review h3 {
            margin-bottom: 20px;
            color: #1f2328;
            text-align: center;
        }
        
        .questions-detail {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .question-review-item {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .question-review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .question-number {
            font-weight: bold;
            color: #656d76;
        }
        
        .question-result {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .question-result.correct {
            background: #dcfce7;
            color: #1a7f37;
        }
        
        .question-result.wrong {
            background: #ffeef0;
            color: #d1242f;
        }
        
        .question-result.unanswered {
            background: #fff8dc;
            color: #bf8700;
        }
        
        .question-review-content {
            margin-bottom: 15px;
        }
        
        .question-review-text {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
            color: #1f2328;
        }
        
        .review-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .review-option {
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #d1d9e0;
            background: white;
        }
        
        .review-option.user-answer {
            border-color: #0969da;
            background: #dbeafe;
        }
        
        .review-option.correct-answer {
            border-color: #1a7f37;
            background: #dcfce7;
        }
        
        .review-option.wrong-answer {
            border-color: #d1242f;
            background: #ffeef0;
        }
        
        .question-explanation {
            padding: 15px;
            background: #fff8dc;
            border-left: 4px solid #bf8700;
            border-radius: 4px;
        }
        
        .question-explanation h4 {
            margin: 0 0 8px 0;
            color: #bf8700;
            font-size: 14px;
        }
        
        .question-explanation p {
            margin: 0;
            color: #333;
            line-height: 1.5;
        }
        
        .stat-item {
            padding: 15px;
            background: #f6f8fa;
            border-radius: 8px;
            border: 1px solid #d1d9e0;
        }
        
        .stat-label {
            display: block;
            font-size: 14px;
            color: #656d76;
            margin-bottom: 5px;
        }
        
        .correct {
            color: #1a7f37;
            font-weight: bold;
        }
        
        .wrong {
            color: #d1242f;
            font-weight: bold;
        }
        
        .result-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #d1d9e0;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            flex-shrink: 0;
        }
        
        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        
        .close {
            font-size: 24px;
            cursor: pointer;
            color: #656d76;
        }
        
        .question-set-item {
            padding: 15px;
            border: 1px solid #d1d9e0;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .question-set-item:hover {
            border-color: #0969da;
            background: #f6f8fa;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                padding: 25px 20px;
                margin: 0;
                border-radius: 6px;
                max-width: none;
                width: calc(100% - 30px);
                margin: 0 auto;
            }
            
            h1 {
                font-size: 20px;
            }
            
            .btn {
                font-size: 14px;
                padding: 14px 18px;
            }
            
            .quiz-container {
                position: fixed;
                top: 10px;
                left: 10px;
                right: 10px;
                bottom: 10px;
                width: auto;
                height: auto;
                max-width: none;
                max-height: none;
                transform: none;
                margin: 0;
                padding: 15px;
            }
            
            .quiz-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .quiz-controls {
                flex-direction: column;
            }
            
            .quiz-controls #back-to-home {
                order: 3;
            }
            
            .result-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .modal-content {
                width: calc(100vw - 20px);
                margin: 10px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>单选题练习系统</h1>
        <p class="subtitle">选择您的练习模式</p>
        
        <div class="button-group">
            <button class="btn btn-primary" onclick="startRandomQuiz()">
                <i class="fas fa-random"></i> 随机题目练习
            </button>
            
            <button class="btn btn-success" onclick="startRandomSetQuiz()">
                <i class="fas fa-shuffle"></i> 随机套题练习
            </button>
            
            <button class="btn btn-warning" onclick="startCustomSetQuiz()">
                <i class="fas fa-list-check"></i> 自选套题练习
            </button>
        </div>
    </div>
    
    <!-- 答题页面 -->
    <div id="quiz-container" class="quiz-container" style="display: none;">
        <div class="quiz-header">
            <div class="timer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#0969da" stroke-width="2"/>
                    <polyline points="12,6 12,12 16,14" stroke="#0969da" stroke-width="2"/>
                </svg>
                <span id="timer-display">00:00</span>
            </div>
            <div class="question-counter">
                <span id="set-info" style="display: none;"></span>
                <span id="current-question">1</span> / <span id="total-questions">10</span>
            </div>
        </div>
        
        <div class="question-content">
            <h2 id="question-text">题目加载中...</h2>
            
            <div class="options" id="options-container">
                <!-- 选项将通过JavaScript动态生成 -->
            </div>
            
            <div class="explanation" id="explanation" style="display: none;">
                <h4>解析：</h4>
                <p id="explanation-text"></p>
            </div>
        </div>
        
        <div class="quiz-controls">
            <button id="back-to-home" class="btn btn-outline">返回主页</button>
            <button id="prev-btn" class="btn btn-secondary" disabled>上一题</button>
            <button id="next-btn" class="btn btn-secondary" disabled>下一题</button>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>
    </div>
    
    <!-- 结果页面 -->
    <div id="result-container" class="result-container" style="display: none;">
        <div class="result-content">
            <h2>答题完成！</h2>
            <div class="result-stats">
                <div class="stat-item">
                    <span class="stat-label">正确/总题数</span>
                    <span id="result-score"><span class="correct">0</span>/<span style="color: #333;">0</span></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">错误率</span>
                    <span id="result-wrong" class="wrong">0%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">用时</span>
                    <span id="result-time">00:00</span>
                </div>
            </div>
            <div class="result-actions">
                <button id="back-to-home-result" class="btn btn-primary">返回主页</button>
            </div>
            
            <!-- 题目详情区域 -->
            <div class="questions-review">
                <h3>题目详情</h3>
                <div id="questions-detail" class="questions-detail">
                    <!-- 题目详情将通过JavaScript动态生成 -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- 套题选择模态框 -->
    <div id="question-set-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>选择套题</h3>
                <span class="close" onclick="closeQuestionSetModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="question-sets-list">
                    <!-- 套题列表将通过JavaScript动态生成 -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function startRandomQuiz() {
            try {
                const response = await fetch('get_question.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_random_questions&limit=20&category=all`
                });
                const data = await response.json();
                if (data.success) {
                    startQuiz(data.data, 'random');
                } else {
                    alert('获取题目失败: ' + data.message);
                }
            } catch (error) {
                alert('网络错误，请稍后重试');
            }
        }
        
        async function startRandomSetQuiz() {
            try {
                const response = await fetch('get_question.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_available_question_sets`
                });
                const data = await response.json();
                if (data.success && data.data.length > 0) {
                    // 随机选择一个套题
                    const randomSet = data.data[Math.floor(Math.random() * data.data.length)];
                    loadQuestionSet(randomSet.id, randomSet.id);
                } else {
                    alert('没有可用的套题');
                }
            } catch (error) {
                alert('网络错误，请稍后重试');
            }
        }
        
        async function startCustomSetQuiz() {
            try {
                // 首先获取可用的套题列表
                const response = await fetch('get_question.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_available_question_sets`
                });
                const data = await response.json();
                if (data.success) {
                    showQuestionSetSelection(data.data);
                } else {
                    alert('获取套题列表失败: ' + data.message);
                }
            } catch (error) {
                alert('网络错误，请稍后重试');
            }
        }
        
        // 答题相关变量
        let currentQuestions = [];
        let currentQuestionIndex = 0;
        let userAnswers = [];
        let startTime = null;
        let timerInterval = null;
        let quizType = 'random';
        
        // 开始答题
        function startQuiz(questions, type = 'random', setNumber = null) {
            currentQuestions = questions;
            currentQuestionIndex = 0;
            userAnswers = new Array(questions.length).fill(null);
            startTime = new Date();
            quizType = type;
            
            // 隐藏主页，显示答题页面
            document.querySelector('.container').style.display = 'none';
            document.getElementById('quiz-container').style.display = 'block';
            
            // 初始化界面
            document.getElementById('total-questions').textContent = questions.length;
            
            // 显示套题信息（如果是套题模式）
             const setInfoElement = document.getElementById('set-info');
             if (type === 'set' && setNumber) {
                 setInfoElement.textContent = `第 ${setNumber} 套 | `;
                 setInfoElement.style.display = 'inline';
             } else {
                 setInfoElement.style.display = 'none';
             }
            
            updateQuestionDisplay();
            startTimer();
            
            // 绑定事件
            bindQuizEvents();
        }
        
        // 绑定答题事件
        function bindQuizEvents() {
            document.getElementById('prev-btn').onclick = () => navigateQuestion(-1);
            document.getElementById('next-btn').onclick = () => navigateQuestion(1);
            document.getElementById('back-to-home').onclick = backToHome;
            document.getElementById('back-to-home-result').onclick = backToHome;
        }
        
        // 更新题目显示
        function updateQuestionDisplay() {
            const question = currentQuestions[currentQuestionIndex];
            
            document.getElementById('current-question').textContent = currentQuestionIndex + 1;
            document.getElementById('question-text').textContent = question.question;
            
            // 生成选项
            const optionsContainer = document.getElementById('options-container');
            optionsContainer.innerHTML = '';
            
            const options = ['A', 'B', 'C', 'D'];
            const optionTexts = [question.option_1, question.option_2, question.option_3, question.option_4];
            
            options.forEach((option, index) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'option';
                optionDiv.textContent = `${option}. ${optionTexts[index]}`;
                optionDiv.onclick = () => selectOption(option);
                
                // 如果已经选择过答案，显示选中状态
                if (userAnswers[currentQuestionIndex] === option) {
                    optionDiv.classList.add('selected');
                }
                
                optionsContainer.appendChild(optionDiv);
            });
            
            // 更新按钮状态
            document.getElementById('prev-btn').disabled = currentQuestionIndex === 0;
            const nextBtn = document.getElementById('next-btn');
            nextBtn.disabled = false; // 允许未选择答案就提交
            
            // 更新按钮文本
            if (currentQuestionIndex === currentQuestions.length - 1) {
                nextBtn.textContent = '提交答案';
            } else {
                nextBtn.textContent = '下一题';
            }
            
            // 更新进度条
            const progress = ((currentQuestionIndex + 1) / currentQuestions.length) * 100;
            document.getElementById('progress-fill').style.width = progress + '%';
            
            // 隐藏解析
            document.getElementById('explanation').style.display = 'none';
        }
        
        // 选择选项
        function selectOption(option) {
            // 移除所有选中状态
            document.querySelectorAll('.option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // 添加选中状态
            event.target.classList.add('selected');
            
            // 记录答案
            userAnswers[currentQuestionIndex] = option;
            
            // 自动跳转到下一题（延迟300ms以显示选中效果）
            setTimeout(() => {
                if (currentQuestionIndex < currentQuestions.length - 1) {
                    navigateQuestion(1);
                } else {
                    // 最后一题，显示结果
                    showResults();
                }
            }, 300);
        }
        

        
        // 导航题目
        function navigateQuestion(direction) {
            const newIndex = currentQuestionIndex + direction;
            if (newIndex >= 0 && newIndex < currentQuestions.length) {
                currentQuestionIndex = newIndex;
                updateQuestionDisplay();
            } else if (newIndex >= currentQuestions.length) {
                // 到达最后一题，显示结果
                showResults();
            }
        }
        
        // 开始计时器
        function startTimer() {
            timerInterval = setInterval(() => {
                const elapsed = new Date() - startTime;
                const minutes = Math.floor(elapsed / 60000);
                const seconds = Math.floor((elapsed % 60000) / 1000);
                document.getElementById('timer-display').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        }
        
        // 停止计时器
        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }
        
        // 显示结果
        function showResults() {
            stopTimer();
            
            const totalQuestions = currentQuestions.length;
            let correctCount = 0;
            
            // 计算正确答案数
            userAnswers.forEach((answer, index) => {
                if (answer === currentQuestions[index].correct_answer) {
                    correctCount++;
                }
            });
            
            const wrongCount = userAnswers.filter(answer => answer !== null).length - correctCount;
            const totalTime = new Date() - startTime;
            const minutes = Math.floor(totalTime / 60000);
            const seconds = Math.floor((totalTime % 60000) / 1000);
            
            // 更新结果显示
            document.getElementById('result-score').innerHTML = `<span class="correct">${correctCount}</span>/<span style="color: #333;">${totalQuestions}</span>`;
            const unansweredCount = totalQuestions - correctCount - wrongCount;
            const totalWrongRate = totalQuestions > 0 ? Math.round(((wrongCount + unansweredCount) / totalQuestions) * 100) : 0;
            document.getElementById('result-wrong').textContent = totalWrongRate + '%';
            document.getElementById('result-time').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // 生成题目详情
            generateQuestionsDetail();
            
            // 隐藏答题页面，显示结果页面
            document.getElementById('quiz-container').style.display = 'none';
            document.getElementById('result-container').style.display = 'block';
        }
        
        // 生成题目详情
        function generateQuestionsDetail() {
            const detailContainer = document.getElementById('questions-detail');
            detailContainer.innerHTML = '';
            
            currentQuestions.forEach((question, index) => {
                const userAnswer = userAnswers[index];
                const correctAnswer = question.correct_answer;
                const isCorrect = userAnswer === correctAnswer;
                const isAnswered = userAnswer !== null;
                
                const questionItem = document.createElement('div');
                questionItem.className = 'question-review-item';
                
                // 题目状态
                let resultClass = 'unanswered';
                let resultText = '未答';
                if (isAnswered) {
                    if (isCorrect) {
                        resultClass = 'correct';
                        resultText = '正确';
                    } else {
                        resultClass = 'wrong';
                        resultText = '错误';
                    }
                }
                
                // 选项数据
                const options = ['A', 'B', 'C', 'D'];
                const optionTexts = [question.option_1, question.option_2, question.option_3, question.option_4];
                
                questionItem.innerHTML = `
                    <div class="question-review-header">
                        <span class="question-number">第 ${index + 1} 题</span>
                        <span class="question-result ${resultClass}">${resultText}</span>
                    </div>
                    <div class="question-review-content">
                        <div class="question-review-text">${question.question}</div>
                        <div class="review-options">
                            ${options.map((option, optIndex) => {
                                let optionClass = 'review-option';
                                if (userAnswer === option) {
                                    optionClass += isCorrect ? ' correct-answer' : ' wrong-answer';
                                }
                                if (correctAnswer === option && !isCorrect) {
                                    optionClass += ' correct-answer';
                                }
                                return `<div class="${optionClass}">${option}. ${optionTexts[optIndex]}</div>`;
                            }).join('')}
                        </div>
                        ${question.explanation ? `
                            <div class="question-explanation">
                                <h4>解析：</h4>
                                <p>${question.explanation}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
                
                detailContainer.appendChild(questionItem);
            });
        }
        

        
        // 返回主页
        function backToHome() {
            stopTimer();
            document.getElementById('quiz-container').style.display = 'none';
            document.getElementById('result-container').style.display = 'none';
            document.querySelector('.container').style.display = 'block';
        }
        
        // 加载套题
        async function loadQuestionSet(setId, setNumber = null) {
            try {
                const response = await fetch('get_question.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_question_set_details&set_id=${setId}`
                });
                const data = await response.json();
                if (data.success) {
                    startQuiz(data.data.questions, 'set', setNumber);
                } else {
                    alert('获取套题失败: ' + data.message);
                }
            } catch (error) {
                alert('网络错误，请稍后重试');
            }
        }
        
        // 显示套题选择
        function showQuestionSetSelection(questionSets) {
            const listContainer = document.getElementById('question-sets-list');
            listContainer.innerHTML = '';
            
            questionSets.forEach(set => {
                const setDiv = document.createElement('div');
                setDiv.className = 'question-set-item';
                setDiv.innerHTML = `
                    <h4>第 ${set.id} 套</h4>
                    <p>题目数量: ${set.question_count}</p>
                `;
                setDiv.onclick = () => {
                    closeQuestionSetModal();
                    loadQuestionSet(set.id, set.id);
                };
                listContainer.appendChild(setDiv);
            });
            
            document.getElementById('question-set-modal').style.display = 'flex';
        }
        
        // 关闭套题选择模态框
        function closeQuestionSetModal() {
            document.getElementById('question-set-modal').style.display = 'none';
        }
    </script>
    
    <!-- GitHub 开源项目按钮 -->
    <div id="github-btn" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
        <a href="https://www.github.com/GitdddCore/SCQ-Practice" target="_blank" 
           style="display: inline-flex; align-items: center; justify-content: center; 
                  width: 44px; height: 44px; background: #24292e; color: white; 
                  text-decoration: none; border-radius: 50%; 
                  box-shadow: 0 2px 8px rgba(0,0,0,0.15); 
                  transition: all 0.3s ease; cursor: pointer;" 
           title="GitHub开源项目 - 单选题练习系统（MIT协议）"
           onmouseover="this.style.background='#1b1f23'; this.style.transform='translateY(-2px) scale(1.1)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.25)';" 
           onmouseout="this.style.background='#24292e'; this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)';">
            <i class="fab fa-github" style="font-size: 20px;"></i>
        </a>
    </div>
    
    <style>
        @media (max-width: 768px) {
            #github-btn {
                display: none !important;
            }
        }
    </style>
</body>
</html>