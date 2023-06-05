[![PHPunit](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/?branch=master)
[![Code Climate](https://codeclimate.com/github/Jagepard/Rudra-Auth/badges/gpa.svg)](https://codeclimate.com/github/Jagepard/Rudra-Auth)
![GitHub](https://img.shields.io/github/license/jagepard/Rudra-Auth.svg)
-----

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
