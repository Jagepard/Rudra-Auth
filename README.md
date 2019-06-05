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
### Авторизация / Auth

#### Установка / Install
```composer require rudra/auth```
#### Использование / Usage
```php
use Rudra\Auth;
use Rudra\Container;
use Rudra\ContainerInterface;
```
```php
$role  = [
    'admin' => ['C', 'U', 'D']
    'editor' => ['C', 'U']
    'moderator' => ['U']
];
```
##### Вызов из контейнера / use container
```php
$services = [
    'contracts' => [
        ContainerInterface::class => rudra(),
    ],
    
    'services' => [
        // Another services
        
        'auth' => ['Rudra\Auth', ['env' => 'development', 'role' => $role],
        
        // Another services
    ]
];
```
```php
$rudra->setServices($services); 
```
```php
$rudra->get('auth')->login('somePassword', $user = [
    'email'    => 'some@email.com',
    'password' => 'someHash'
], 'dashboard', 'Укажите верные данные');
$rudra->get('auth')->logout('');
$rudra->get('auth')->updateSessionIfSetRememberMe('login');
```
![Rudra-Auth](https://github.com/Jagepard/Rudra-Auth/blob/master/UML.png)
