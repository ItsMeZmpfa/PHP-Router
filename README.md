# ZmpfaRouter

Simple, fast and yet powerful PHP router that is easy to get integrated and in any project.

**It takes only a few lines of code to get started:**

```php
ZmpfaRouter::get('/', function() {
    return 'Hello world';
});
```


# Getting started

Add the latest version of the simple-router project running this command.

```
composer require zmpfa/zmpfa-router
```

## Notes

The goal of this project is to create a router that being lightweight.

## Requirements

- PHP 8.0 or greater

## Features
- Basic routing (`GET`, `POST`, `PUT`, `PATCH`, `UPDATE`, `DELETE`) with support for custom multiple verbs.
- Regular Expression Constraints for parameters.
- Generating url to routes.
- Route prefixes.
- Input manager; easily manage `GET`, `POST`
- Optional parameters

## Future Feature Implementation Example
- Middleware
- CSRF protection
- Named routes

## Installation
1. Navigate to your project folder in terminal and run the following command:

```php
composer require ItsMeZmpfa/ZmpfaRouter
```

### Setting up Apache

Nothing special is required for Apache to work. We've include the `.htaccess` file in the `public` folder. If rewriting is not working for you, please check that the `mod_rewrite` module (htaccess support) is enabled in the Apache configuration.

#### .htaccess example

Below is an example of an working `.htaccess` file used by ZmpfaRouter.

Simply create a new `.htaccess` file in your projects `public` directory and paste the contents below in your newly created file. This will redirect all requests to your `index.php` file (see Configuration section below).

```
RewriteEngine on
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteCond %{SCRIPT_FILENAME} !-l
RewriteRule ^(.*)$ index.php/$1
```

### Configuration

Create a new file, name it routes.php and place it in your library folder. This will be the file where you define all the routes for your project.

### WARNING: NEVER PLACE YOUR ROUTES.PHP IN YOUR PUBLIC FOLDER!

In your index.php require your newly-created routes.php and call the SimpleRouter::start() method. This will trigger and do the actual routing of the requests

```
<?php
use Demo\ZmpfaRouter\ZmpfaRouter;

/* Load external routes file */
require_once 'routes.php';


// Start the routing
ZmpfaRouter::start();
```

## Routes
Remember the routes.php file you required in your index.php? This file be where you place all your custom rules for routing

## Basic Routing

Below is a very basic example of setting up a route. First parameter is the url which the route should match - next parameter is a Closure or callback function that will be triggered once the route matches.

```
ZmpfaRouter::get('/', function() {
    return 'Hello world';
});
```

## Available Methods
```
ZmpfaRouter::get($url, $callback, $settings);
ZmpfaRouter::post($url, $callback, $settings);
ZmpfaRouter::put($url, $callback, $settings);
ZmpfaRouter::patch($url, $callback, $settings);
ZmpfaRouter::delete($url, $callback, $settings);
```

## Route parameters

You'll properly wondering by know how you parse parameters from your urls. For example, you might want to capture the users id from an url. You can do so by defining route-parameters.
```
ZmpfaRouter::get('/user/{id}', function ($userId) {
    return 'User with id: ' . $userId;
});
```
You may define as many route parameters as required by your route:

```
ZmpfaRouter::get('/posts/{post}/comments/{comment}', function ($postId, $commentId) {
    // ...
});
```

