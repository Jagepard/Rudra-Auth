[![PHPunit](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/?branch=master)
[![Code Climate](https://codeclimate.com/github/Jagepard/Rudra-Auth/badges/gpa.svg)](https://codeclimate.com/github/Jagepard/Rudra-Auth)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-auth/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-auth)
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
```php
use Rudra\Auth\AuthFacade as Auth;

$user = [
    "email"    => "user@email.com",
    "password" => "password"
];

Auth::authentication($user[0], $user[1], "admin/dashboard", "Укажите верные данные");
Auth::exitAuthenticationSession();
Auth::restoreSessionIfSetRememberMe("login");
```
