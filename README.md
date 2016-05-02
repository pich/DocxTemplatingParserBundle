DocxTemplatingParserBundle
==========================

#Project

The purpose of this project is to use a docx file as a template for generate docx file in Symfony.
The docx template will use twig. Then the bundle parse the xml and give the twig to the twig parser and finaly regenerate a docx.  

For this project i use an original idea of my employer http://www.echosystems.fr/. 
I'm allow to reproduce and improve the idea.


#Installation

 * Edit your composer.json to add
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
 
 * Do:
 ```bash
 $ curl -sS https://getcomposer.org/installer | php
 $ php composer.phar update northvik/DocxTemplatingParserBundle
 ```
 
 * Edit your app/AppKernel to add
 ```php
 new \northvik\DocxTemplatingParserBundle\northvikDocxTemplatingParserBundle()
 ```
 
 * Make sure your php.ini a have timezone configure and a tmp dir with the good rights.
 
 
 
 #Compatibility
 
 I work on a Symfony 2.3 for this Bundle with php 5.6
 
