## Table of contents
- [Rudra\Auth\Auth](#rudra_auth_auth)
- [Rudra\Auth\AuthFacade](#rudra_auth_authfacade)
- [Rudra\Auth\AuthInterface](#rudra_auth_authinterface)
<hr>

<a id="rudra_auth_auth"></a>

### Class: Rudra\Auth\Auth
##### implements [Rudra\Auth\AuthInterface](#rudra_auth_authinterface)
| Visibility | Function |
|:-----------|:---------|
|public|<em><strong>__construct</strong>( Rudra\Container\Interfaces\RudraInterface $rudra )</em><br>Sets cookie lifetime, session hash|
|public|<em><strong>authentication</strong>( array $user  string $password  array $redirect  array $notice )</em><br>|
|private|<em><strong>setCookiesIfSetRememberMe</strong>( array $user  string $token ): void</em><br>|
|private|<em><strong>setAuthenticationSession</strong>( array $user  string $token ): void</em><br>|
|public|<em><strong>exitAuthenticationSession</strong>( string $redirect ): void</em><br>|
|private|<em><strong>unsetRememberMeCookie</strong>(): void</em><br>Removes the $_POST["remember_me"] cookie|
|public|<em><strong>authorization</strong>( ?string $token  ?string $redirect )</em><br>|
|public|<em><strong>roleBasedAccess</strong>( string $role  string $privilege  ?string $redirect )</em><br>|
|public|<em><strong>restoreSessionIfSetRememberMe</strong>(  $redirect ): void</em><br>|
|public|<em><strong>bcrypt</strong>( string $password  int $cost ): string</em><br>|
|private|<em><strong>handleRedirect</strong>( string $redirect  array $jsonResponse ): void</em><br>|
|public|<em><strong>getSessionHash</strong>(): string</em><br>|
|public|<em><strong>encrypt</strong>( string $data  string $secret )</em><br>|
|public|<em><strong>decrypt</strong>( string $data  string $secret )</em><br>|


<a id="rudra_auth_authfacade"></a>

### Class: Rudra\Auth\AuthFacade
| Visibility | Function |
|:-----------|:---------|
|public static|<em><strong>__callStatic</strong>( string $method  array $parameters ): mixed</em><br>|


<a id="rudra_auth_authinterface"></a>

### Class: Rudra\Auth\AuthInterface
| Visibility | Function |
|:-----------|:---------|
|abstract public|<em><strong>authentication</strong>( array $user  string $password  array $redirect  array $notice )</em><br>|
|abstract public|<em><strong>exitAuthenticationSession</strong>( string $redirect ): void</em><br>|
|abstract public|<em><strong>authorization</strong>( ?string $token  ?string $redirect )</em><br>|
|abstract public|<em><strong>roleBasedAccess</strong>( string $role  string $privilege  ?string $redirect )</em><br>|
|abstract public|<em><strong>bcrypt</strong>( string $password  int $cost ): string</em><br>|
<hr>

###### created with [Rudra-Documentation-Collector](#https://github.com/Jagepard/Rudra-Documentation-Collector)
