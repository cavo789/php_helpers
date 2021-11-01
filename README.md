# php_helpers

![Banner](./banner.svg)

> Some helpers to develop faster in PHP 7

## Table of Contents

- [Table of Contents](#table-of-contents)
- [Build & test](#build--test)
- [Install the library in your project](#install-the-library-in-your-project)
- [Classes](#classes)
  - [App](#app)
  - [Error](#error)
  - [Session](#session)
- [Helpers](#helpers)
  - [ArrayHelper](#arrayhelper)
  - [Debug](#debug)
    - [Method enable()](#method-enable)
  - [Enum](#enum)
  - [Files](#files)
  - [HTML](#html)
  - [Strings](#strings)
  - [Template](#template)
- [License](#license)

## Build & test

Make sure dependencies are up-to-date

```
composer update
```

During development phase, to make sure the package is valid, run the following command. This will check that the composer.json file is correctly set up.

```
composer validate
```

To test the package locally, without publishing a new version of [Packagist](https://packagist.org/), just execute the following command on the prompt. This will create (or update) a `/vendor` folder and install a fresh version of the library.

```
composer install
```

## Install the library in your project

Installation is done through composer, just run `composer require cavo789/php_helpers`.

## Classes

List of classes

### App

`cavo789\Class\App` aimed to provide features for
working with the application like enabling or not a debug mode

This class implements the LoggerInterface and, thus, expose the methods
for writing an information into a log file (thanks to Monolog).

Because this class can be instantiated in more than one script
of the same application, the class is a Singleton: only one instance
will be instantiated and loaded into memory.

How to:

```php
use \cavo789\Classes\App as App;

// true = enable the debug mode
$app = App::getInstance(true, ['folder' => __DIR__.'/logs']);

$app->debug('This is a debug message');
$app->info('This is a information');
```

Rely on `monolog/monolog`

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

Note: you can also use a template file `$error = new Error("templates/error.html");`

### Session

`cavo789\Class\Session` aimed to provide features for working with the `$_SESSION` object

Because this class can be instantiated in more than one script of the same application, the class is a Singleton: only one instance will be instantiated and loaded into memory.

How to:

```php
use \cavo789\Classes\Session as Session;

$session = Session::getInstance('MyApp_');
$session->set('Password', md5('MyPassword'));

echo '<pre>'.print_r($session->getAll(), true).'</pre>';

if ($session->get('Password', '') === md5('MyPassword')) {
    echo 'Great, correct password';
}

unset($session);
```

## Helpers

List of helpers

### ArrayHelper

Generic helper functions for working with Arrays.

### Debug

Quick debug helper.

#### Method enable()

To easily configure Apache for showing errors on screen and output everything (notice, warning, ... errors).

For instance, put in the very first lines in your PHP script these two lines.

```php
use \cavo789\Helpers\Debug;

Debug::enable();
```

Once done, notices, warnings, ... will be echoed on screen.

### Enum

Enumeration helper.

### Files

Files and folders generic helper.

### HTML

HTML Helper.

### Strings

Strings generic helper

### Template

Make it easier to work with html templates.

## License

[MIT](LICENSE)
