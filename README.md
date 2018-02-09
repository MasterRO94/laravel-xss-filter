# Laravel XSS Filter 

Laravel 5.4+ Middleware to filter user inputs from XSS and iframes and other embed elements.

It does not remove the html, it is only escaped script tags and embeds.

But it does delete inline html event listeners such as `onclick`.


For example 

```php
<html>
<head>
    <script src="app.js"></script>
    <script>window.init()</script> 
    <meta name="test" />
    <script>
    let Iframe = new Iframe('#iframe');
    </script>
<head>
<body>
    <div class="hover" onhover="show()" data-a="b"><p onclick="click"><span class="span" ondblclick="hide()"></span>Aawfawfaw f awf aw  </p></div>
    <iframe id="iframe">Not supported!</iframe>
</body>
</html>
```

will be transformed to 

```php
<html>
<head>
&lt;script src=&quot;app.js&quot;&gt;&lt;/script&gt;
&lt;script&gt;window.init()&lt;/script&gt; 
<meta name="test" />
&lt;script&gt;
let Iframe = new Iframe(&#039;#iframe&#039;);
&lt;/script&gt;
<head>
<body>
<div class="hover"  data-a="b"><p ><span class="span" ></span>Aawfawfaw f awf aw  </p></div>
&lt;iframe id=&quot;iframe&quot;&gt;Not supported!&lt;/iframe&gt;
</body>
</html>

```

This allows to render html in views based on users' input and don't be afraid of XSS attacks and embed elements.

# Installation

## Step 1: Composer
From command line
```
composer require masterro/laravel-xss-filter
```

## Step 2: register Service provider and Facade(optional) (for Laravel 5.4)
For your Laravel app, open `config/app.php` and, within the `providers` array, append:

```php
MasterRO\LaravelXSSFilter\XSSFilterServiceProvider::class
```
within the `aliases` array, append:
```php
'XSSCleaner' => MasterRO\LaravelXSSFilter\XSSCleanerFacade::class
```

## Step 3: publish configs (optional)
From command line
```
php artisan vendor:publish --provider="MasterRO\LaravelXSSFilter\XSSFilterServiceProvider"
```

## Step 4: Middleware
Add `\MasterRO\LaravelXSSFilter\FilterXSS::class` middleware to your web or global middleware group in `App\Http\Kernel.php`

# Usage
After adding middleware every request will be filtered.

If you need to specify attributes that should not be filtered add them to `xss-filter.except` config. By default filter excepts `password` and `password_confirmation` fields.
 
If you want to clean some value in other place you can use `XSSCleaner` Facade
```php
$clean = XSSCleaner::clean($string);
```
 

#### _I will be grateful if you star this project :)_

 