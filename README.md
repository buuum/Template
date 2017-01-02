Simple, fast and secure template engine for PHP
===============================================

[![Packagist](https://img.shields.io/packagist/v/buuum/template.svg)](https://packagist.org/packages/buuum/template)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg?maxAge=2592000)](#license)

## Install

### System Requirements

You need PHP >= 5.5.0 to use Buuum\Config but the latest stable version of PHP is recommended.

### Composer

Buuum\Template is available on Packagist and can be installed using Composer:

```
composer require buuum/template
```

### Manually

You may use your own autoloader as long as it follows PSR-0 or PSR-4 standards. Just put src directory contents in your vendor directory.

## INITIALIZE
```php
$dir = __DIR__.'/views',
$supportView = new ViewSupport();
$view = new View($dir, $supportView);
```

### TEMPLATE TAGS

#### FOREACH
* {{foreach $items as $item}}
* {{endforeach}}

#### IF
* {{if $success}}
* {{elseif $error}}
* {{else}}
* {{endif}}

#### INCLUDES
* {{@value}} => include $value
* {{@@value}} => include __DIR__.'/'.$value;
* {{@/include/header}} => include __DIR__.'/include/header.php';

#### PRINT
* {{$var}}
* {{var_dump($var)}}
* {{$var or 'deafault text'}}

#### FORMS
* %input(checked:check){:type=>"checkbox", :name=>"checkm[]", :value=>"1"}
* <input <?=($check)? 'checked' : ''?> name="checkm[]" type="checkbox" value="1">

* %option(selected:select){:value=>"2"} 2
* <option <?=($select)? 'selected' : ''?> value="2">2</option>


### TEMPLATE

| TAG | PARSER |
|---|---|
| {{URLIMG}}  | getImgPath() |
| {{\*index}}  | getUrl($name)|
| {{\*demo:id\|32}} | getUrl($name, $options) |
| {{e. text}} | getText($text) |
| {{e. text %s:::hola}} | getText($text, $params) |
| {{\*\*index::classname}} | isPageActual($url, $classname) |

### HEADERS
```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta content="<?= $this->getHeader('description') ?>" name="description">
    <meta content="<?= $this->getHeader('keywords') ?>" name="keywords">
    <title><?= $this->getHeader('title') ?></title> 
    <?php if ($this->getHeader('index')): ?>
        <?php if ($this->getHeader('canonical')): ?>
        <link href="<?= $this->getHeader('canonical') ?>" rel="canonical"> 
        <?php endif; ?>
        <meta content="index, follow" name="robots"> 
    <?php else: ?>
        <meta content="noindex, follow" name="robots"> 
    <?php endif; ?>
    <link href="<?= $this->getHeader('favicon') ?>" rel="shortcut icon">
    <link href="<?= $this->getLink('css') ?>" rel="stylesheet">
    <script src="<?= $this->getLink('js') ?>"></script>
</head>
```

## ADD SUPPORT FOR FILTERS
on viewsupprot
```php

class ViewSupport implements ParseViewInt
{
    public function filter_number($value, $params)
    {
        if (!empty($params)) {
            return number_format($value, ...$params);
        }
        return number_format($value, 0, '.',',');
    }
}

```

on template
```
{{$var|number}}
{{$var|number(2,'.',',')}}
{{2000|number}}
```

## LICENSE

The MIT License (MIT)

Copyright (c) 2016

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.