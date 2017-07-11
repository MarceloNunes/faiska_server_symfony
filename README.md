**Faiska** (*fah-is-kah*) Server - Symfony version
==============================================

This is a PHP back-end project that showcases the use the **Symfony** framework. This project implements a RESTful API interface that serves a basic login and user management. 

Faiska Project is a personal workout routine for my programming skills, and keeping up to date with new tools in Web Development. The goal of the project is to develop exactly the same API using different back-end tools and frameworks (PHP/Symfony, PHP/Laravel, Node.js/Express, Python/Flask, etc) and create different front-end apps who consumes this API and implements its interface (Angular, React, Vue.js, Ionic, etc).

## API Documentation

This repository contains an implementation of a RESTful API. Refer to  **[api_doc.html](http://htmlpreview.github.io/?https://github.com/MarceloNunes/faiska_server_symfony/blob/master/api_doc.html)** for the API documentation.

## Installation instructions

To install this source code on a GNU Linux system run the following commands on a bash terminal.

```
$ mysql -u root -p -e "create database faiska;"
$ git clone https://github.com/MarceloNunes/faiska_server_symfony.git
$ cd faiska_server_symfony/
$ composer install
$ php bin/console doctrine:schema:create
$ php bin/console server:run
```

Don't forget to provide correct database user and password information. After you install this Faiska Synfony server, you can test it's services using a generic API client like **Postman**.

## Structure overview 

Here I describe how this *Faiska Server Synfony* implementation is organized, by highlighting some specific namespaces (folders) and which role they play in the project.

#### [AppBundle/Controller](https://github.com/MarceloNunes/faiska_server_symfony/tree/master/src/AppBundle/Controller)

These are the endpoints of the API interface. These classes are responsible for checking Authentication, issuing commands from the repository, handling exceptions and returning the result values to the client. 

This folder\namespace is the equivalent to the **view** level on a MVC based system.

#### [AppBundle/Controller/Helper](https://github.com/MarceloNunes/faiska_server_symfony/tree/master/src/AppBundle/Controller/Helper)

Implements several Helpers for the Controllers. Some examples:

+ [Authorizator](https://github.com/MarceloNunes/faiska_server_symfony/blob/master/src/AppBundle/Controller/Helper/Authorizator.php) Abstracts the checking of access permissions providing a diversity of rules combinations for each operation. 

+ [BrowseParameters](https://github.com/MarceloNunes/faiska_server_symfony/blob/master/src/AppBundle/Controller/Helper/BrowseParameters.php) Abstracts the management of paging  and sorting for browsing operations. This class gets the paging/sorting options from the URL data and creates a normalized parameters object.

+ [UnifiedRequest](https://github.com/MarceloNunes/faiska_server_symfony/blob/master/src/AppBundle/Controller/Helper/UnifiedRequest.php) Synfony has a pretty good `Request` object to extract input data from GET or POST requests. However, as this application handles a wider set of HTTP methods (PUT, PATCH, DELETE, etc), this class unifies the original requests with raw parsed requests for these other methods. 

#### [AppBundle/Repository](https://github.com/MarceloNunes/faiska_server_symfony/tree/master/src/AppBundle/Repository)

These classes are the command issuers that are called by the Controllers. They implement the logic of the operations. As a main directive, Repository operations only return success values, all erros and exceptions must be raised immediately when they ocur and are to be catched by the Controller (endpoint). 

This folder\namespace is the equivalent to the **controller** level on a MVC based system.

#### [AppBundle/Repository/Helper/Validator](https://github.com/MarceloNunes/faiska_server_symfony/tree/master/src/AppBundle/Repository/Helper/Validator)

This folder/namespace separates the vlidation rules from the standard Repository folder. Tasks like checking mandatory parameters, well formed input data or unique keys and foreign keys constraints are performed at this level letting the Repository to focus on the main business rules. 

#### [AppBundle/Entity](https://github.com/MarceloNunes/faiska_server_symfony/tree/master/src/AppBundle/Entity)

This folder/namespace describes the data entities of the system. Only minor behaviours related to getters and setters are implemented on this level. These classes may always implement a `toArray()`method that retrieves the object data on a formatted array to be consumed by the client.

This folder\namespace is the equivalent to the **model** level on a MVC based system.

