Icons
=====

Load Windows icon files in PHP.


Requirements
------------

* PHP 5.3.0 or later
* GD

If you are able to install [Imagick](http://www.php.net/manual/en/class.imagick.php) on your server, you can stop reading and use that natively instead.

This library is for those of us that have to stick with GD (which includes every PHP developer using Google App Engine).


Usage
-----

```
<?php
//  Load the library
    include_once('./icon.php');
	
//  Load icon file
    $icon = \Codefocus\Icons\Icon::createFromFile('test.ico');
    
//  Extract the "best" icon that matches your specifications
    $image = $icon->getImage(
        16,  //  minimum width
        4,   //  minimum bitcount
        64,  //  maximum width
        32   //  maximum bitcount
    );
    
//  Render as PNG
    $pngData = $image->renderPng();
    header('Content-type: image/png');
    echo $pngData;
```
