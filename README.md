[![PHPunit](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml)
[![Maintainability](https://qlty.sh/badges/1346d77c-b7f1-4488-b73c-b47582166061/maintainability.svg)](https://qlty.sh/gh/Jagepard/projects/Rudra-Auth)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-auth/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-auth)
[![Coverage Status](https://coveralls.io/repos/github/Jagepard/Rudra-Auth/badge.svg?branch=master)](https://coveralls.io/github/Jagepard/Rudra-Auth?branch=master)
-----

# Rudra-Auth | [API](https://github.com/Jagepard/Rudra-Auth/blob/master/docs.md "Documentation API")
### Authorization

#### Install / Установка
```composer require rudra/auth```

##### User registration / Регистрация пользователя
```php
$user = [
    "email"    => "user@email.com",
    "password" => Auth::bcrypr("password")
];
```
##### Getting a user from the database / Получение пользователя из базы данных
```php
$user = [
    "email"    => "user@email.com",
    "password" => "password_hash"
];
```

##### Usage / Использование
```php
use Rudra\Auth\AuthFacade as Auth;
```
##### Authentication / Аутентификация
```php
Auth::authentication(
    $user, 
    "password", 
    ["admin/dashboard", "login"],
    ["error" => "Wrong access data"]
);
```
##### Logout from authentication session / Выход из сеанса аутентификации
```php
Auth::logout();
```