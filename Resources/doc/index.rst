DocxTemplatingParserBundle
==========================

Project
-------

The purpose of this project is to use a docx file as a template for generate docx file in Symfony.
The docx template will use twig. Then the bundle parse the xml and give the twig to the twig parser and finaly regenerate a docx.

For this project i use an original idea of my employer http://www.echosystems.fr/s.
I'm allow to reproduce and improve the idea.


Installation
------------

 1) Edit your composer.json to add
 ```json
 [...]
     "require" : {
         [...]
         "northvik/DocxTemplatingParserBundle" : "dev-master"
     },
     "repositories" : [{
         "type" : "vcs",
         "url" : "https://github.com/northvik/DocxTemplatingParserBundle.git"
     }],
     [...]
 ```

 2) Do:
 ```bash
 $ curl -sS https://getcomposer.org/installer | php
 $ php composer.phar update northvik/DocxTemplatingParserBundle
 ```

 3) Edit your app/AppKernel to add
 ```php
 new \northvik\DocxTemplatingParserBundle\northvikDocxTemplatingParserBundle()
 ```

Compatibility
-------------

 I work on a Symfony 2.3 for this Bundle with php 5.6
