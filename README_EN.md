# Single Choice Question Practice System (SCQP-System)

>**Sorry, this project has not been translated into English yet**

>**中文版本: [点击这里](./README.md)**

## Project Introduction

The Single Choice Question Practice System is a web application that provides convenient single choice question practice.

### What can it do?

This program provides users with three practice modes:
- Random Question Practice Mode: Randomly select 20 questions from the question bank for practice
- Random Question Set Practice Mode: Randomly select a question set from the question bank for practice
- Custom Question Set Practice Mode: Select a specific question set from the question bank for practice

### Project Screenshots:

![IMAGE-INDEX](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/index.png)
![IMAGE-QUESTION](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/question.png)
![IMAGE-RESULT](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/result.png)
![IMAGE-ADMINLOGIN](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/adminlogin.png)
![IMAGE-ADMINPANEL](https://github.com/GitdddCore/Single-Choice-Question-Practice-System/blob/main/TemplateImage/adminpanel.png)

## How to Deploy

### System Requirements:

- PHP Version: 7.4 or higher
- Database Version: MySQL 5.7 or higher
- Web Server: Apache or Nginx

### Installation Steps:

1. Download the latest release package of the project
2. Extract the package to your web server's root directory
3. Import the database template file `"0 - System Templates/SQL Template.sql"` into your MySQL database
4. Configure the `"config.json"` parameters
5. Run `composer install` command to download and install dependencies from composer.json
6. Start the service
7. Complete

### Notes

The administrator password is stored in plain text in config.json

## Quick Start

**Congratulations on your successful installation! Please read this section to quickly learn how to use this system**

### How to configure questions and question sets?

>Admin panel URL: http://domain/admin.php, please login with the configured password

#### Configure Questions:

##### About Single Addition
1. Click the "Add Question" button
2. Fill in the question information
3. Click the "Save" button

##### About Batch Addition
1. Click the "Batch Import" button
2. Click the "Download Template" button
3. Fill in the question information in the EXCEL file
4. Import the completed EXCEL file
5. Click the "Start Import" button

#### Configure Question Sets:
1. Click the "Create Question Set" button
2. Enter the question set number
3. Select questions (multiple selection allowed)
- Questions already selected by other question sets will not be displayed in the list
4. Click the "Create Question Set" button

**After performing the above operations, the system will work normally. Congratulations!**

## Frequently Asked Questions

Q1: Import function error?

Solution: This may be caused by missing dependency files. You need to re-run the `composer install` command to download and install the dependencies in composer.json

(Waiting for issues to continue writing...)

## Update Log

### 1.0.0 Update
- Initial version release

## Declaration

**This project is open source under the MIT license, allowing anyone to freely use, modify and distribute this software**

### Disclaimer

**This software is provided "as is", without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and non-infringement. In no event shall the authors or copyright holders be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the software.**

### Contribution Guidelines
**We welcome contributions of any kind! If you find bugs in the project or have suggestions for improvement, please submit an issue or Pull Request. Thank you for your attention and support to this project!**