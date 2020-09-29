# Laravel Factory Extended 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/soyhuce/laravel-factories-extended.svg?style=flat-square)](https://packagist.org/packages/soyhuce/laravel-factories-extended)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/soyhuce/laravel-factories-extended/run-tests?label=tests)
[![Total Downloads](https://img.shields.io/packagist/dt/soyhuce/laravel-factories-extended.svg?style=flat-square)](https://packagist.org/packages/soyhuce/laravel-factories-extended)

This package provides extensions for Laravel 8 Model Factories

# Installation

You should install this package using composer :

```shell script
composer require --dev soyhuce/laravel-factories-extended
``` 

You're done !

# Usage 

Your model Factory has to extend `Soyhuce\LaravelFactoriesExtended\Factory` :

```php
<?php

use Soyhuce\LaravelFactoriesExtended\Factory;

class UserFactory extends Factory
{
    // Same like usual factories
}
```

More documentations about factories [here](https://laravel.com/docs/8.x/database-testing#creating-factories)

## Extensions

### of and dynamic of

Sometimes we need to use both a model and its related models in a test. With `of`, you can define a relation between the created models and an existing model :
```php
$user = UserFactory::new()->create();
$posts = PostFactory::times(3)->of($user)->create();
```

In case the relation cannot be guessed directly from the model, we can define it explicitly :
```php
$posts = PostFactory::times(3)->of($user, 'author')->create();
```

You can also use `of` with `MorphTo` relations : 
```php
$comments = CommentFactory::times(3)->of($post, 'commentable')->create();
```

and with multiple models :
```php
$comments = CommentFactory::times(3)->of($post, 'commentable')->of($user, 'user')->create();
```

You can use dynamic of to define the relation name :
```php
$user = UserFactory::new()->create();
$post = PostFactory::new()->ofAuthor($user)->create();
$comments = CommentFactory::times(3)->ofCommentable($post)->ofUser($user)->create();
```

If you use `soyhuce/next-ide-helper` (version ^0.2.4), we provide for you a factory extension to automatically add dynamic of methods. You just have to add `Soyhuce\LaravelFactoriesExtended\DynamicOfResolver::class` in your `next-ide-helper.factories.extensions` config.  

# Contributing

You are welcome to contribute to this project ! Please see [CONTRIBUTING.md](CONTRIBUTING.md).

# License

This package is provided under the [MIT License](LICENSE.md)
