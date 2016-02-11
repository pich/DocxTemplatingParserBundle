DocxTemplatingParserBundle
==========================

#Instalation

 * edit your composer.json to add
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
 
 * edit your app/AppKernel to add
 ```php
 new \northvik\DocxTemplatingParserBundle\northvikDocxTemplatingParserBundle()
 ```
 