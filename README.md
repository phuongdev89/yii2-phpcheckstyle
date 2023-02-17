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

```
php yii coverage
```
