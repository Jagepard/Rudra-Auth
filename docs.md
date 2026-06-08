## Table of contents
- [Rudra\Auth\Auth](#rudra_auth_auth)
- [Rudra\Auth\AuthFacade](#rudra_auth_authfacade)
- [Rudra\Auth\AuthInterface](#rudra_auth_authinterface)


---



<a id="rudra_auth_auth"></a>

### Class: Rudra\Auth\Auth
| Visibility | Function |
|:-----------|:---------|
| public | `__construct(Rudra\Container\Interfaces\RudraInterface $rudra)`<br> |
| public | `authentication(array $user, string $password, array $redirect, array $notice): void`<br> |
| private | `setCookiesIfSetRememberMe(array $user, string $token): void`<br> |
| private | `setAuthenticationSession(array $user, string $token): void`<br> |
| public | `logout(string $redirect): void`<br> |
| private | `unsetRememberMeCookie(): void`<br> |
| public | `authorization(?string $token, ?string $redirect): bool`<br> |
| public | `roleBasedAccess(string $role, string $privilege, ?string $redirect): bool`<br> |
| public | `restoreSessionIfSetRememberMe(string $redirect): void`<br> |
| public | `bcrypt(string $password, int $cost): string`<br> |
| private | `handleRedirect(string $redirect, array $jsonResponse): void`<br> |
| public | `getSessionHash(): string`<br> |
| private | `encrypt(string $data, string $secret): string`<br> |
| private | `decrypt(string $data, string $secret): string`<br> |


<a id="rudra_auth_authfacade"></a>

### Class: Rudra\Auth\AuthFacade
| Visibility | Function |
|:-----------|:---------|
| public static | `__callStatic(string $method, array $parameters): mixed`<br>Handles static method calls for the Facade class<br>It dynamically resolves the underlying class name by removing "Facade" from the class name<br>If the resolved class does not exist, it attempts to clean up the class name by removing spaces<br>If the resolved class is not already registered in the container, it registers it<br>Finally, it delegates the static method call to the resolved class instance |


<a id="rudra_auth_authinterface"></a>

### Class: Rudra\Auth\AuthInterface
| Visibility | Function |
|:-----------|:---------|
| abstract public | `authentication(array $user, string $password, array $redirect, array $notice): void`<br> |
| abstract public | `logout(string $redirect): void`<br> |
| abstract public | `authorization(?string $token, ?string $redirect): bool`<br> |
| abstract public | `roleBasedAccess(string $role, string $privilege, ?string $redirect): bool`<br> |
| abstract public | `bcrypt(string $password, int $cost): string`<br> |


---

###### created with [Rudra-Documentation-Collector](https://github.com/Jagepard/Rudra-Documentation-Collector)
