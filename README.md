Yii2 phpcheckstyle
==================

PHPCheckstyle is an open-source tool that helps PHP programmers adhere to certain coding conventions. The tools checks
the input PHP source code and reports any deviations from the coding convention.

The tool uses the PEAR Coding Standards as the default coding convention. But it allows you to configure it to suit your
coding standards.

# Installation

### Installation with Composer

```
composer require phuongdev89/yii2-phpcheckstyle
```
Add to your root `composer.json`
```
"repositories" : [
    {
        "type" : "git",
        "url" : "https://github.com/phuongdev89/phpcheckstyle"
    }
]
```
Add to `console/config/main.php`

```php
'controllerMap' => [
    'coverage' => [
        'class' => \phuongdev89\phpcheckstyle\commands\CoverageController::class,
    ],
]
```
# Options
| Name           | Type          | Description                                                                                                 | Required | Default value                               |
|----------------|---------------|-------------------------------------------------------------------------------------------------------------|----------|---------------------------------------------|
| **$progress**  | bool          | Display progress file when checking If not set, default is 1                                                | No       | true                                        |
| **$format**    | string\|array | Output format (html/text/xml/xml_console/console/html_console). Can be multiple formats separator by comma. | No       | html                                        |
| **$level**     | string        | Level to report Value is: INFO ERROR WARNING IGNORE                                                         | No       | INFO                                        |
| **$maxErrors** | int           |                                                                                                             | No       | 100                                         |
| **$language**  | string        |                                                                                                             | No       | en-us                                       |
| **$outdir**    | string\|null  | Output directory of report                                                                                  | No       | runtime/phpcheckstyle                       |
| **$config**    | string\|null  | Config path                                                                                                 | No       | phuongdev89/phpcheckstyle/phpcheckstyle.xml |
| **$debug**     | bool          | Debug output                                                                                                | No       | false                                       |
# Usage
### Scan only git modified
```
php yii coverage
```
or
```
php yii coverage/git
```
### Scan all project, exclude `environments` `vendor` `test`
```
php yii coverage 1
```
### Scan file or folder
```
php yii coverage/run frontend/controllers
```
### Scan multiple files or folders
```
php yii coverage/run frontend/controllers,frontend/models
```
### Scan multiple files or folders and exclude some files or folders
```
php yii coverage/run frontend/controllers,frontend/models frontend/components,frontend/helpers
```
### Help
```
php yii help coverage/index
php yii help coverage/git
php yii help coverage/run
```
