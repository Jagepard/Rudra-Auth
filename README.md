[![PHPunit](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Auth/?branch=master)
[![Maintainability](https://qlty.sh/badges/1346d77c-b7f1-4488-b73c-b47582166061/maintainability.svg)](https://qlty.sh/gh/Jagepard/projects/Rudra-Auth)
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

Auth::authentication($user, "password", ["admin/dashboard", "login"], ["error" => "Wrong access data"]);
Auth::exitAuthenticationSession();
Auth::restoreSessionIfSetRememberMe("login");
```
