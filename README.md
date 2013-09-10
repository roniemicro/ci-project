Codeigniter Enhanced Edition!
=============================

Welcome to the Codeigniter Enhanced Edition - a fully-functional Codeigniter2
application that you can use as the skeleton for your new applications.

This document contains information on how to download, install, and start
using Codeigniter2.

1) Installing the Enhanced Edition
----------------------------------

### Use Composer

[Download](https://github.com/roniemicro/ci-project/archive/v1.1.zip) and extract or Clone this project first. Go to downloaded directory.

If you don't have [Composer][1] yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

If you want some [customization](./docs/customization.md), first make it in the composer.json file. Then, use the `install` command to download all dependencies along with codeigniter framework.

    php composer.phar install

Composer will install Codeigniter2 and all its dependencies under the working directory.


2) Browsing the Demo Application
--------------------------------

Congratulations! You're now ready to use Codeigniter2.

Edit the files with your preferences (domain, languages, database, authentication):

- config.php
- database.php
- thirdparty/ezRbac/config/ez_rbac.php.php

Create a virtualhost setting the document root pointing to /path/of/web directory

	<VirtualHost *:80>
		ServerName mydomain.com
		ServerAlias www.mydomain.com
		DocumentRoot /path/to/web
	</VirtualHost>


##Backend user and password

The default user to access to the private zone is:

    user: 		admin@admin.com

    password: 	123456


3) Getting started with Codeigniter2 Extend Edition
----------------------------------------------------

This distribution is meant to be the starting point for your Codeigniter2
applications, but it also contains some sample code that you can learn from
and play with.


What's inside?
---------------

The Codeigniter2 Enhanced Edition is configured with the following defaults:

  * Twig as template engine(if you chose to add it);

  * Swiftmailer is configured(if you chose to add it);


It comes pre-configured with the following libraries:

  * [EzRbac][2] Role Based Access Control Library

  * [MY_Model][3] An extension of CodeIgniter's base Model class

  * Enhanced [Controller](./docs/controller.md) Library

  * Enhanced Loader Library(For support twig template engine  and basic layout)

  * Enhanced Language Library(gettext localization implementation)

  * JS/CSS Minifier. you can use (assets/css/mini.php?files=file1,file2) and (assets/js/mini.php?files=file1,file2)


Enjoy!

[1]:  http://getcomposer.org/
[2]:  https://github.com/xiidea/ezRbac
[3]:  https://github.com/ronisaha/MY_Model
