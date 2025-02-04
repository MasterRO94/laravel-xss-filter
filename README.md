<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg">
</p>

<p align="center">
    <a href="https://packagist.org/packages/masterro/laravel-xss-filter">
        <img src="https://img.shields.io/packagist/v/masterro/laravel-xss-filter.svg?style=flat-rounded" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/masterro/laravel-xss-filter">
        <img src="https://img.shields.io/packagist/dt/masterro/laravel-xss-filter.svg?style=flat-rounded" alt="Total Downloads">
    </a>
    <a href="https://github.com/MasterRO94/laravel-xss-filter/actions">
        <img src="https://github.com/MasterRO94/laravel-xss-filter/workflows/Tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://github.com/MasterRO94/laravel-xss-filter/blob/master/LICENSE">
        <img src="https://img.shields.io/github/license/MasterRO94/laravel-xss-filter" alt="License">
    </a>
</p>

<p align="center">
    <a href="https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md">
        <img src="https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg" alt="StandWithUkraine">
    </a>
</p>

# XSS Filter/Sanitizer for Laravel 

### Configure once and forget about XSS attacks!

It does not remove the html, it is only escaped script tags and embeds.  
However, by default, it does delete inline event listeners such as `onclick`. 
Optionally they also can be escaped (set `escape_inline_listeners` to `true` in `xss-filter.php` config file).

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

## Step 2: publish configs (optional)
From command line
```
php artisan vendor:publish --provider="MasterRO\LaravelXSSFilter\XSSFilterServiceProvider"
```

## Step 3: Middleware
You can register `\MasterRO\LaravelXSSFilter\FilterXSS::class` for filtering in global middleware stack, group middleware stack or for specific routes.
> Have a look at [Laravel's middleware documentation](https://laravel.com/docs/middleware#registering-middleware), if you need any help.

### Livewire
If you are using Livewire you can either register global middleware to all the update livewire requests. This special middleware will clean only required part of Livewire request payload and will not touch snapshot so the component checksum still would be valid. 
```php
// AppServiceProvider.php

public function boot(): void
{
    Livewire::setUpdateRoute(static function ($handle) {
        return Route::post('/livewire/update', $handle)
            ->middleware(['web', FilterXSSLivewire::class]);
    });
}
```

Or you can apply middleware to specific routes and add it to persistent list to ensure inputs are cleared on subsequent component requests:
```php
// AppServiceProvider.php

public function boot(): void
{
    Livewire::addPersistentMiddleware([
        FilterXSSLivewire::class,
    ]);
}
```

NOTE! If you have both Livewire components and traditional Controllers you can apply only `FilterXSSLivewire::class` middleware for all required routes or globally. It will fall back to base logic for non Livewire requests.

# Usage
After adding middleware, every request will be filtered.

If you need to specify attributes that should not be filtered add them to `xss-filter.except` config. By default, filter excepts `password` and `password_confirmation` fields.
 
If you want to clean some value in other place (i.e. Controller) you can use `XSSCleaner` Facade.

```php
$clean = XSSCleaner::clean($string);
```
 
#### Runtime configuration


```php
XSSCleaner::config()
    ->allowElement('iframe')
    ->allowMediaHosts(['youtube.com', 'youtu.be'])
    ->denyElement('a');
    
$clean = XSSCleaner::clean($string);
```
 

#### _I will be grateful if you star this project :)_

 
