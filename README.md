[![PHPunit](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Auth/actions/workflows/php.yml)
[![Maintainability](https://qlty.sh/badges/1346d77c-b7f1-4488-b73c-b47582166061/maintainability.svg)](https://qlty.sh/gh/Jagepard/projects/Rudra-Auth)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-auth/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-auth)
[![Coverage Status](https://coveralls.io/repos/github/Jagepard/Rudra-Auth/badge.svg?branch=master)](https://coveralls.io/github/Jagepard/Rudra-Auth?branch=master)
-----

## Authentication, session management and RBAC | [API](https://github.com/Jagepard/Rudra-Auth/blob/master/docs.md "Documentation API")

#### Install
```composer require rudra/auth```

##### Usage
```php
use Rudra\Auth\AuthFacade as Auth;
```
##### Configuration
>For the component to work correctly, you need to add the following parameters to the Rudra configuration file:
```yml
# Secret key for encrypting cookies and generating session hashes
secret: your_super_secret_key_here

# Roles for Role-Based Access Control (the smaller the number, the higher the privilege)
roles:
    admin: 0
    editor: 1
    moderator: 2
    user: 3

# Environment (in the 'test' environment, cookies are not deleted on logout)
environment: production
```
##### User registration
```php
$user = [
    "email"    => "user@email.com",
    "password" => Auth::bcrypt("password")
];
```
##### Getting a user from the database
```php
$user = [
    "email"    => "user@email.com",
    "password" => "password_hash",
    "role"     => "admin"
];
```
##### Authentication
> The second argument is the **plain text password** entered by the user.
> The `$user['password']` must contain a **hash** from the database.
```php
Auth::authentication(
    $user, 
    "password", 
    ["admin/dashboard", "login"],
    ["error" => "Wrong access data"]
);
```
> **Note:** For the "Remember Me" feature to work, the login form must contain a checkbox with the name `remember_me`.
##### Login form example
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
##### Restoring session (Remember Me)
>Called at the beginning of the application loading (before authorization check).
>If the user has valid 'Remember Me' cookies, the session will be restored automatically.
```php
Auth::restoreSessionIfSetRememberMe("login");
```
##### Authorization check

###### General authorization check
>Check if the user is authorized. If not — redirect to 'login' 
```php
if (!Auth::authorization(null, "login")) {
    exit;
}
```
>If you just need to get a boolean value without a redirect (for example, for an API):
```php
$isLoggedIn = Auth::authorization(); 
```
###### Access to personal user resources
>For access control to a specific user's resources (e.g., profile, personal data), a token is used.
The token is generated from the user's password, email, and session hash.
```php
// Generate token for the current user
$token = md5($user['password'] . $user['email'] . Auth::getSessionHash());

// Check if the token matches the session token
if (!Auth::authorization($token, "login")) {
    exit;
}
```
##### Role-Based Access Control
>Check if the current role ('admin') has privileges not lower than the requested ones ('editor').
>If privileges are insufficient — redirect to 'error/403'
```php
use Rudra\Container\Facades\Session;

// Get the role of the current user from the session (for example, after authorization)
if (Session::has("user")) {
   $currentRole = Session::get("user")['role'] ?? 'user';
}

// Check if the permissions are sufficient for access (for example, 'editor' level is required)
if (!Auth::roleBasedAccess($currentRole, "editor", "error/403")) {
    exit;
}
```
##### Log out of the authentication session with a redirect to the login page
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