# php_helpers

> Some helpers to develop faster in PHP 7

## Table of Contents

- [Install](#install)
- [Classes](#classes)
  \_ [Error](#error)
- [Helpers](#helpers)
  _ [App](#app)
  _ [ArrayHelper](#arrayhelper)
  _ [Files](#files)
  _ [HTML](#html)
  \_ [Strings](#strings)
- [License](#license)

## Install

`composer require cavo789/php_helpers`

## Classes

List of classes

### Error

Catch all exceptions and display the message using a nice HTML template.

Just instantiate the class at the very beginning of your application and you're done.

```php
use \cavo789\Classes\Error as Error;

$error = new Error("<h1>Houston we' ve a problem</h1>" . PHP_EOL .
	'<h2>Error {{ error_code }} - {{ error_title }} encountered</h2>' . PHP_EOL .
	'<div style="font-color:red;">{{ error_message }}</div>' . PHP_EOL .
	'<hr/>' . PHP_EOL .
	'<div>Please email us</div>');

throw new \RuntimeException('Action not supported');
```

Note: you can also use a template file like `$error = new Error("templates/error.html");`

## Helpers

List of helpers

### App

### ArrayHelper

### Files

### HTML

### Strings

## Install

## Usage

## License

[MIT](LICENSE)
