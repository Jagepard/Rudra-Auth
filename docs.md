## Table of contents
- [Rudra\Auth\Auth](#rudra_auth_auth)
- [Rudra\Auth\AuthFacade](#rudra_auth_authfacade)
- [Rudra\Auth\AuthInterface](#rudra_auth_authinterface)
<hr>

<a id="rudra_auth_auth"></a>

### Class: Rudra\Auth\Auth
| Visibility | Function |
|:-----------|:---------|
| public | `__construct(Rudra\Container\Interfaces\RudraInterface $rudra)`<br>Sets cookie lifetime, session hash |
| public | `authentication(array $user, string $password, array $redirect, array $notice)`<br> |
| private | `setCookiesIfSetRememberMe(array $user, string $token): void`<br> |
| private | `setAuthenticationSession(array $user, string $token): void`<br> |
| public | `logout(string $redirect): void`<br> |
| private | `unsetRememberMeCookie(): void`<br>Removes the \$_POST["remember_me"] cookie |
| public | `authorization(?string $token, ?string $redirect)`<br> |
| public | `roleBasedAccess(string $role, string $privilege, ?string $redirect)`<br> |
| public | `restoreSessionIfSetRememberMe( $redirect): void`<br> |
| public | `bcrypt(string $password, int $cost): string`<br> |
| private | `handleRedirect(string $redirect, array $jsonResponse): void`<br> |
| public | `getSessionHash(): string`<br> |
| public | `encrypt(string $data, string $secret)`<br> |
| public | `decrypt(string $data, string $secret)`<br> |


<a id="rudra_auth_authfacade"></a>

### Class: Rudra\Auth\AuthFacade
| Visibility | Function |
|:-----------|:---------|
| public static | `__callStatic(string $method, array $parameters): ?mixed`<br> |


<a id="rudra_auth_authinterface"></a>

### Class: Rudra\Auth\AuthInterface
| Visibility | Function |
|:-----------|:---------|
| abstract public | `authentication(array $user, string $password, array $redirect, array $notice)`<br> |
| abstract public | `logout(string $redirect): void`<br> |
| abstract public | `authorization(?string $token, ?string $redirect)`<br> |
| abstract public | `roleBasedAccess(string $role, string $privilege, ?string $redirect)`<br> |
| abstract public | `bcrypt(string $password, int $cost): string`<br> |
<hr>

###### created with [Rudra-Documentation-Collector](#https://github.com/Jagepard/Rudra-Documentation-Collector)
