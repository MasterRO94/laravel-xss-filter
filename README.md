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

This allows to you render html in views based on users inputs and don'b be afraid of XSS attacks and embed elements.