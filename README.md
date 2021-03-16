[![Build Status](https://travis-ci.org/Jagepard/Rudra-Auth.svg?branch=master)](https://travis-ci.org/Jagepard/Rudra-Auth)
[![codecov](https://codecov.io/gh/Jagepard/Rudra-Auth/branch/master/graph/badge.svg)](https://codecov.io/gh/Jagepard/Rudra-Auth)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/?branch=master)
[![Code Climate](https://codeclimate.com/github/Jagepard/Rudra-Auth/badges/gpa.svg)](https://codeclimate.com/github/Jagepard/Rudra-Auth)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/f95dcf6a2227482db74b1232ef30b635)](https://www.codacy.com/app/Jagepard/Rudra-Auth?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Jagepard/Rudra-Auth&amp;utm_campaign=Badge_Grade)
-----
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/rudra/auth/v/stable)](https://packagist.org/packages/rudra/auth)
[![Total Downloads](https://poser.pugx.org/rudra/auth/downloads)](https://packagist.org/packages/rudra/auth)
![GitHub](https://img.shields.io/github/license/jagepard/Rudra-Auth.svg)

# Rudra-Auth | [API](https://github.com/Jagepard/Rudra-Auth/blob/master/docs.md "Documentation API")
### Authorization

#### Install / Установка
```composer require rudra/auth```

##### Setting up roles / Настройка ролей
```
[
    'admin'     => 0,
    'editor'    => 1,
    'moderator' => 2,
    'user'      => 3
]
```
#### Usage / Использование
##### use container
```php
use Rudra\Auth\Auth;
use Rudra\Container\Rudra;
use Rudra\Container\Interfaces\RudraInterface;

$services = [
    "contracts" => [
        RudraInterface::class => Rudra::run(),
    ],
    
    "services" => [
        // Another services
        
        Auth::class => Auth::class,
        
        // Another services
    ]
];
```
```php
Application::run()->setServices($services); 
```
```php
$user = [
    "email"    => "some@email.com",
    "password" => "someHash"
];
    
Application::run()->objects()->get("auth")->login("somePassword", $user, "dashboard", "Please enter correct information");
Application::run()->objects()->get("auth")->logout("");
Application::run()->objects()->get("auth")->updateSessionIfSetRememberMe("login");
```
