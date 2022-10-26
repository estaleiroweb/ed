# ED (Easy Data Framwork)
Easy Data Framework. An easy way to get a CRUD form added a filter grid builded by reverse engineer and other a lot of tools.

**Database reverse engineering** for Model Classes, view and ***CRUD***.


The Framework speeds up the time to market a job based in databases.
It keep updated all the Model classes sicronized with Meta Data of ***DBMS***.
Through the DB Client the developer can easily build a ***ERM*** (*Entity Relation Model*) and get a PHP Model class.
Easy Data get all meta data of ***DBMS*** and build classes to use without writing any lines of PHP. Only enjoy!
It even implements a report grid with filters, a ***CRUD*** form and many other features.

## Instalation

By Composer, load the pack
```bash
composer require estaleiroweb/ed
```
After the conclusion Composer, do the instalation of Easy Data.
You can do it for 2 ways.
> This example was ran on the root project.

### 1 - Simbol Link (recomendated)
```bash
ln -s vendor/estaleiroweb/ed/admin.php

#To first install 
./admin.php install

#To admitrate
./admin.php
```

### 2 - Direcly
```bash
php -r "require 'vendor/autoload.php'; new EstaleiroWeb\ED\IO\Admin(true);"
```
To run administrator, do without _true_:
```bash
php -r "require 'vendor/autoload.php'; new EstaleiroWeb\ED\IO\Admin;"
```

### 3 - Creting a _admin.php_ file
You can see a exmaple code in vendor folder
```php
#!/usr/bin/env php
require 'vendor/autoload.php';
new EstaleiroWeb\ED\IO\Admin();
```
Change permitions to execute
```bash
chmod +x admin.php
```
So you can run passing parameter _install_
```bash
./admin.php install
```
With this method you can run without _install_ parameter to acess menu of administration.
```bash
./admin.php
```

Result:
<pre style='font-weight: normal;'>
   <b><u style='color:green'>Easy Data Main Menu</u></b>

   <b>1</b> - Alter the main Key
   <b>2</b> - Manager DSN Connections
   <b>3</b> - Config Directories
   <b>4</b> - Repopulate Database

Choice your option [<b>0/ESC</b> - To Exit]:
</pre>

## Donate

Contact: helbert@estaleiroweb.com.br

> Be a Funder of the Project
                               