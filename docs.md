## Table of contents

- [\Rudra\Auth](#class-rudraauth)
- [\Rudra\AuthBase](#class-rudraauthbase)
- [\Rudra\Interfaces\AuthInterface (interface)](#interface-rudrainterfacesauthinterface)

<hr /><a id="class-rudraauth"></a>
### Class: \Rudra\Auth

| Visibility | Function |
|:-----------|:---------|
| public | <strong>access(</strong><em>\string</em> <strong>$token=null</strong>, <em>\string</em> <strong>$redirect=null</strong>)</strong> : <em>bool/callable/mixed</em> |
| public | <strong>bcrypt(</strong><em>\string</em> <strong>$password</strong>, <em>\integer</em> <strong>$cost=10</strong>)</strong> : <em>bool/string</em> |
| public | <strong>login(</strong><em>\string</em> <strong>$password</strong>, <em>array</em> <strong>$user</strong>, <em>\string</em> <strong>$redirect=`'admin'`</strong>, <em>\string</em> <strong>$notice=`'Укажите верные данные'`</strong>)</strong> : <em>callable</em> |
| public | <strong>logout(</strong><em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>void</em> |
| public | <strong>role(</strong><em>\string</em> <strong>$role</strong>, <em>\string</em> <strong>$privilege</strong>, <em>\string</em> <strong>$redirect=null</strong>)</strong> : <em>bool</em> |
| public | <strong>updateSessionIfSetRememberMe(</strong><em>string</em> <strong>$redirect=`'login'`</strong>)</strong> : <em>void</em> |
| protected | <strong>setAuthSession(</strong><em>\string</em> <strong>$email</strong>, <em>\string</em> <strong>$token</strong>)</strong> : <em>void</em> |
| protected | <strong>setCookiesIfSetRememberMe(</strong><em>array</em> <strong>$user</strong>, <em>\string</em> <strong>$token</strong>)</strong> : <em>void</em> |

*This class extends [\Rudra\AuthBase](#class-rudraauthbase)*

*This class implements [\Rudra\Interfaces\AuthInterface](#interface-rudrainterfacesauthinterface)*

<hr /><a id="class-rudraauthbase"></a>
### Class: \Rudra\AuthBase

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\Rudra\Interfaces\ContainerInterface</em> <strong>$container</strong>, <em>\string</em> <strong>$env</strong>, <em>array</em> <strong>$roles=array()</strong>)</strong> : <em>void</em><br /><em>AbstractAuth constructor. Устанавливает роли, окружение, время жизни куки, хеш сессии</em> |
| public | <strong>container()</strong> : <em>\Rudra\Interfaces\ContainerInterface</em> |
| protected | <strong>__setContainerTraitConstruct(</strong><em>\Rudra\Interfaces\ContainerInterface</em> <strong>$container</strong>)</strong> : <em>void</em><br /><em>SetContainerTrait constructor.</em> |
| protected | <strong>handleRedirect(</strong><em>\string</em> <strong>$redirect</strong>, <em>array</em> <strong>$jsonResponse</strong>, <em>\callable</em> <strong>$redirectCallable=null</strong>)</strong> : <em>callable</em> |
| protected | <strong>loginRedirectWithFlash(</strong><em>\string</em> <strong>$notice</strong>)</strong> : <em>void</em> |
| protected | <strong>setCookie(</strong><em>\string</em> <strong>$name</strong>, <em>\string</em> <strong>$value</strong>, <em>int/\integer</em> <strong>$expire</strong>)</strong> : <em>void</em> |
| protected | <strong>unsetCookie()</strong> : <em>void</em> |

<hr /><a id="interface-rudrainterfacesauthinterface"></a>
### Interface: \Rudra\Interfaces\AuthInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>access(</strong><em>\string</em> <strong>$token=null</strong>, <em>\string</em> <strong>$redirect=null</strong>)</strong> : <em>mixed</em> |
| public | <strong>bcrypt(</strong><em>\string</em> <strong>$password</strong>, <em>\integer</em> <strong>$cost=10</strong>)</strong> : <em>bool/string</em> |
| public | <strong>login(</strong><em>\string</em> <strong>$password</strong>, <em>array</em> <strong>$user</strong>, <em>\string</em> <strong>$redirect</strong>, <em>\string</em> <strong>$notice</strong>)</strong> : <em>callable</em> |
| public | <strong>logout(</strong><em>\string</em> <strong>$redirect</strong>)</strong> : <em>void</em> |
| public | <strong>role(</strong><em>\string</em> <strong>$role</strong>, <em>\string</em> <strong>$privilege</strong>, <em>\string</em> <strong>$redirect=null</strong>)</strong> : <em>bool</em> |
| public | <strong>updateSessionIfSetRememberMe(</strong><em>string</em> <strong>$redirect</strong>)</strong> : <em>void</em> |

