[![PHPunit](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml)
[![Maintainability](https://qlty.sh/badges/1346d77c-b7f1-4488-b73c-b47582166061/maintainability.svg)](https://qlty.sh/gh/Jagepard/projects/Rudra-Auth)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-auth/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-auth)
[![Coverage Status](https://coveralls.io/repos/github/Jagepard/Rudra-Auth/badge.svg?branch=master)](https://coveralls.io/github/Jagepard/Rudra-Auth?branch=master)
-----

# Rudra-Auth | [API](https://github.com/Jagepard/Rudra-Auth/blob/master/docs.md "Documentation API")
### Authorization

#### Install / Установка
```composer require rudra/auth```

##### Usage / Использование
```php
use Rudra\Auth\AuthFacade as Auth;
```
##### Configuration / Конфигурация
>For the component to work correctly, you need to add the following parameters to the Rudra configuration file

>Для корректной работы компонента необходимо добавить следующие параметры в конфигурационный файл Rudra:

```php
return [
    /**
     * ---------------------------------------------------------------|
     * Secret key for encrypting cookies and generating session hashes
     * ---------------------------------------------------------------|
     * Секретный ключ для шифрования cookie и генерации хэшей
     * ---------------------------------------------------------------|
     */
    "secret" => "your_super_secret_key_here",
    
    /**
     * --------------------------------------------------------------------------------------|
     * Roles for Role-Based Access Control (the smaller the number, the higher the privilege)
     * --------------------------------------------------------------------------------------|
     * Роли для Role-Based Access Control (чем меньше число, тем выше привилегия)
     * --------------------------------------------------------------------------------------|
     */
    "roles"  => [
        "admin"  => 1,
        "editor" => 2,
        "user"   => 3,
    ],
    
    /**
     * --------------------------------------------------------------------------|
     * Environment (in the 'test' environment, cookies are not deleted on logout)
     * --------------------------------------------------------------------------|
     * Окружение (в среде 'test' не удаляются cookie при logout)
     * --------------------------------------------------------------------------|
     */
    "environment" => "prod", 
];
```

##### User registration / Регистрация пользователя
```php
$user = [
    "email"    => "user@email.com",
    "password" => Auth::bcrypt("password")
];
```
##### Getting a user from the database / Получение пользователя из базы данных
```php
$user = [
    "email"    => "user@email.com",
    "password" => "password_hash"
];
```
##### Authentication / Аутентификация
> The second argument is the **plain text password** entered by the user.
> The `$user['password']` must contain a **hash** from the database.

> Второй аргумент — это **пароль в открытом виде**, введённый пользователем.
> В `$user['password']` должен быть **хэш** из базы данных.
```php
Auth::authentication(
    $user, 
    "password", 
    ["admin/dashboard", "login"],
    ["error" => "Wrong access data"]
);
```
> **Note:** For the "Remember Me" feature to work, the login form must contain a checkbox with the name `remember_me`.

> **Примечание:** Чтобы работала функция "Запомнить меня", форма входа должна содержать чекбокс с именем `remember_me`.
##### Login form example / Пример формы входа
```html
<form method="POST" action="/login">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <label>
        <input type="checkbox" name="remember_me"> Remember me / Запомнить меня
    </label>
    <button type="submit">Login</button>
</form>
```
##### Restoring session (Remember Me) / Восстановление сессии
>Called at the beginning of the application loading (before authorization check).
>If the user has valid 'Remember Me' cookies, the session will be restored automatically.

>Вызывается в начале загрузки приложения (до проверки авторизации). 
>Если у пользователя есть валидные cookie "Remember Me", сессия будет восстановлена автоматически.
```php
Auth::restoreSessionIfSetRememberMe("login");
```
##### Authorization check / Проверка авторизации

###### General authorization check / Общая проверка авторизации
>Check if the user is authorized. If not — redirect to 'login' 

>Проверяем, авторизован ли пользователь. Если нет — редирект на 'login'
```php
if (!Auth::authorization(null, "login")) {
    exit;
}
```
>If you just need to get a boolean value without a redirect (for example, for an API):

>Если нужно просто получить булево значение без редиректа (например, для API):
```php
$isLoggedIn = Auth::authorization(); 
```
###### Access to personal user resources / Доступ к личным ресурсам пользователя
>For access control to a specific user's resources (e.g., profile, personal data), a token is used.
The token is generated from the user's password, email, and session hash.

>Для контроля доступа к ресурсам конкретного пользователя (например, профиль, личные данные) используется токен.
Токен генерируется из пароля, email пользователя и хэша сессии.

```php
/**
 * ------------------------------------------|
 * Generate token for the current user
 * ------------------------------------------|
 * Генерируем токен для текущего пользователя
 * ------------------------------------------|
 */
$token = md5($user['password'] . $user['email'] . Auth::getSessionHash());

/**
 * --------------------------------------------|
 * Check if the token matches the session token
 * --------------------------------------------|
 * Проверяем, совпадает ли токен с сессионным
 * --------------------------------------------|
 */
if (!Auth::authorization($token, "login")) {
    exit;
}
```
##### Role-Based Access Control / Ролевой доступ
>Check if the current role ('admin') has privileges not lower than the requested ones ('editor').
>If privileges are insufficient — redirect to 'error/403'

>Проверяем, имеет ли текущая роль ('admin') права не ниже запрашиваемых ('editor').
>Если прав не хватает — редирект на 'error/403'
```php
use Rudra\Container\Facades\Session;

/**
 * ------------------------------------------------------------------------------------|
 * Get the role of the current user from the session (for example, after authorization)
 * ------------------------------------------------------------------------------------|
 * Получаем роль текущего пользователя из сессии (например, после авторизации)
 * ------------------------------------------------------------------------------------|
 */
if (Session::has("user")) {
   $currentRole = Session::get("user")['role'] ?? 'user';
}

/**
 * --------------------------------------------------------------------------------------------|
 * Check if the permissions are sufficient for access (for example, 'editor' level is required)
 * --------------------------------------------------------------------------------------------|
 * Проверяем, достаточно ли прав для доступа (например, требуется уровень 'editor')
 * --------------------------------------------------------------------------------------------|
 */
if (!Auth::roleBasedAccess($currentRole, "editor", "error/403")) {
    exit;
}
```
##### Log out of the authentication session with a redirect to the login page / Выход из сеанса аутентификации с редиректом на страницу входа
```php
Auth::logout("login");
```
## License

This project is licensed under the **Mozilla Public License 2.0 (MPL-2.0)** — a free, open-source license that:

- Requires preservation of copyright and license notices,
- Allows commercial and non-commercial use,
- Requires that any modifications to the original files remain open under MPL-2.0,
- Permits combining with proprietary code in larger works.

📄 Full license text: [LICENSE](./LICENSE)  
🌐 Official MPL-2.0 page: https://mozilla.org/MPL/2.0/

--------------------------
Проект распространяется под лицензией **Mozilla Public License 2.0 (MPL-2.0)**. Это означает:
 - Вы можете свободно использовать, изменять и распространять код.
 - При изменении файлов, содержащих исходный код из этого репозитория, вы обязаны оставить их открытыми под той же лицензией.
 - Вы **обязаны сохранять уведомления об авторстве** и ссылку на оригинал.
 - Вы можете встраивать код в проприетарные проекты, если исходные файлы остаются под MPL.

📄  Полный текст лицензии (на английском): [LICENSE](./LICENSE)  
🌐 Официальная страница: https://mozilla.org/MPL/2.0/