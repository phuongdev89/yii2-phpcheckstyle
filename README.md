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

Add to `console/config/main.php`

```php
'controllerMap' => [
    'coverage' => [
        'class' => \phuongdev89\phpcheckstyle\commands\CoverageController::class,
    ],
]
```

# Usage
### Scan only git modified
```
php yii coverage
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
php yii help converage
```
