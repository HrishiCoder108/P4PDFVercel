P4PDF API - PHP Library
------------------------

A powerful PHP library for [P4PDF API](https://developer.p4pdf.com) - Professional PDF Processing Solutions

You can sign up for a P4PDF account at https://developer.p4pdf.com

Develop and automate PDF processing tasks like Compress PDF, Merge PDF, Split PDF, convert Office to PDF, PDF to JPG, Images to PDF, add Page Numbers, Rotate PDF, Unlock PDF, stamp a Watermark and Repair PDF. Each one with several settings to get your desired results.

## Requirements

PHP 7.4 and later.

## Install

You can install the library via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require p4pdf/p4pdf-php
```

To use the library, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Getting Started

Simple usage looks like:

```php
use P4Pdf\P4Pdf;

$p4pdf = new P4Pdf('project_public_id','project_secret_key');
$myTask = $p4pdf->newTask('compress');
$file1 = $myTask->addFile('file1.pdf');
$myTask->execute();
$myTask->download();
```

## Samples

See samples folder for comprehensive examples of all available operations.

## Documentation

Please see https://developer.ilovepdf.com/docs for up-to-date documentation.

## API Server

By default, the library connects to the iLovePDF API at `https://api.ilovepdf.com`. You can change this using:

```php
P4Pdf::setStartServer('https://your-custom-api-server.com');
```
