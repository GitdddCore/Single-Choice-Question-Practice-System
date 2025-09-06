# 单选题练习系统（SCQP-System）

>**English Version: [Click Here](./README_EN.md)**

## 项目简介

单选题练习系统（Single Choice Question Practice System）是提供便捷单选题练习的Web程序。

### 它可以做什么？

本程序向用户提供三个练习模式：
- 随机题目练习模式：随机抽取题库中20道题进行练习
- 随机套题练习模式：随机抽取题库中一个套题进行练习
- 自选套题练习模式：自选题库中的一个套题进行练习

### 项目截图:

![IMAGE-INDEX](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/index.png)
![IMAGE-QUESTION](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/question.png)
![IMAGE-RESULT](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/result.png)
![IMAGE-ADMINLOGIN](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/adminlogin.png)
![IMAGE-ADMINPANEL](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/adminpanel.png)

## 如何部署

### 系统环境：

- PHP 版本：7.4 或更高版本
- 数据库版本：MySQL 5.7 或更高版本
- Web 服务器：Apache 或 Nginx

### 安装步骤：

1. 下载项目已发布的最新发行版压缩包
2. 解压压缩包到您的Web服务器的根目录下
3. 导入数据库模板文件 `"0 - System Templates/SQL Template.sql"` 到您的MySQL数据库中
4. 配置 `"config.json"` 参数
5. 运行 `composer install` 命令以下载并安装composer.json内的依赖文件
6. 运行服务
7. 完成

### 注意事项

管理员密码为明文存储，在config.json内

## 快速开始

**恭喜您已安装成功！请观看此段以快速了解如何使用此系统**

### 如何配置题目、套题？

>管理员后台URL: http://域名/admin.php，请使用设置的密码登录

#### 配置题目：

##### 关于单次添加
1. 点击“添加题目”按钮
2. 填写题目信息
3. 点击“保存”按钮

##### 关于批量添加
1. 点击“批量导入”按钮
2. 点击“下载模板”按钮
3. 在EXCEL文件内填写题目信息
4. 导入填写完成的EXCEL文件
5. 点击“开始导入”按钮

#### 配置套题:
1. 点击“创建套题”按钮
2. 输入套题编号
3. 选中题目（可多选）
- 所有被其他套题选中的题目不会在列表内显示
4. 点击“创建套题”按钮

**执行以上操作后，系统将可正常使用，恭喜你！**

## 常见问题

Q1：导入功能出错？

解决方法：可能是依赖文件缺失导致的，需要重新运行 `composer install` 命令以下载并安装composer.json内的依赖文件

(等待issue以继续编写...)

## 更新日志

### 1.0.0 更新
- 初始版本发布

## 声明

**本项目采用 MIT 协议开源，允许任何人自由使用、修改和分发本软件**

### 免责声明

**本软件按"原样"提供，不提供任何形式的明示或暗示保证，包括但不限于对适销性、特定用途适用性和非侵权性的保证。在任何情况下，作者或版权持有人均不对任何索赔、损害或其他责任负责，无论是在合同诉讼、侵权行为还是其他方面，由软件或软件的使用或其他交易引起、由此产生或与之相关。**

### 贡献说明
**我们欢迎任何形式的贡献！若您发现项目内出现BUG或有改进建议，请向我们提出issue或提交 Pull Request。感谢您对本项目的关注和支持！**