## Table of contents

- [\Rudra\Auth](#class-rudraauth)
- [\Rudra\AuthBase](#class-rudraauthbase)
- [\Rudra\Interfaces\AuthInterface (interface)](#interface-rudrainterfacesauthinterface)

<hr /><a id="class-rudraauth"></a>
### Class: \Rudra\Auth

> Class Auth

| Visibility | Function |
|:-----------|:---------|
| public | <strong>access(</strong><em>\boolean</em> <strong>$access=false</strong>, <em>\string</em> <strong>$userToken=null</strong>, <em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>mixed</em><br /><em>Предоставление доступа к общим ресурсам, либо личным ресурсам пользователя</em> |
| public | <strong>bcrypt(</strong><em>\string</em> <strong>$password</strong>, <em>\integer</em> <strong>$cost=10</strong>)</strong> : <em>bool/string</em><br /><em>Получить хеш пароля</em> |
| public | <strong>checkCookie(</strong><em>string</em> <strong>$redirect=`'login'`</strong>)</strong> : <em>void</em><br /><em>Проверка авторизации</em> |
| public | <strong>login(</strong><em>\string</em> <strong>$password</strong>, <em>array</em> <strong>$user</strong>, <em>\string</em> <strong>$redirect=`'admin'`</strong>, <em>\string</em> <strong>$notice</strong>)</strong> : <em>callable</em><br /><em>Аутентификация, Авторизация</em> |
| public | <strong>logout(</strong><em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>void</em><br /><em>Завершить сессию</em> |
| public | <strong>role(</strong><em>\string</em> <strong>$role</strong>, <em>\string</em> <strong>$privilege</strong>, <em>\boolean</em> <strong>$access=false</strong>, <em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>bool</em><br /><em>Проверка прав доступа</em> |

*This class extends [\Rudra\AuthBase](#class-rudraauthbase)*

*This class implements [\Rudra\Interfaces\AuthInterface](#interface-rudrainterfacesauthinterface)*

<hr /><a id="class-rudraauthbase"></a>
### Class: \Rudra\AuthBase

> Class AuthBase

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\Rudra\Interfaces\ContainerInterface</em> <strong>$container</strong>, <em>\string</em> <strong>$env</strong>, <em>array</em> <strong>$roles=array()</strong>)</strong> : <em>void</em><br /><em>AbstractAuth constructor. Устанавливает роли, окружение, время жизни куки, хеш сессии</em> |
| public | <strong>container()</strong> : <em>\Rudra\Interfaces\ContainerInterface</em> |
| protected | <strong>__setContainerTraitConstruct(</strong><em>\Rudra\Interfaces\ContainerInterface</em> <strong>$container</strong>)</strong> : <em>void</em><br /><em>SetContainerTrait constructor.</em> |
| protected | <strong>handleRedirect(</strong><em>\string</em> <strong>$redirect</strong>, <em>array</em> <strong>$jsonResponse</strong>, <em>\callable</em> <strong>$redirectCallable=null</strong>)</strong> : <em>callable</em><br /><em>Обрабатывает перенаправление</em> |
| protected | <strong>loginRedirectWithFlash(</strong><em>\string</em> <strong>$notice</strong>)</strong> : <em>void</em><br /><em>Переадресует с добавлением уведомления в 'alert'</em> |
| protected | <strong>setCookie(</strong><em>\string</em> <strong>$name</strong>, <em>\string</em> <strong>$value</strong>, <em>int/\integer</em> <strong>$expire</strong>)</strong> : <em>void</em><br /><em>Устанавливает куки</em> |
| protected | <strong>unsetCookie()</strong> : <em>void</em><br /><em>Сбрасывает куки</em> |

<hr /><a id="interface-rudrainterfacesauthinterface"></a>
### Interface: \Rudra\Interfaces\AuthInterface

> Interface AuthInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>access(</strong><em>\boolean</em> <strong>$access=false</strong>, <em>\string</em> <strong>$userToken=null</strong>, <em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>callable</em><br /><em>Предоставление доступа к общим ресурсам, либо личным ресурсам пользователя</em> |
| public | <strong>bcrypt(</strong><em>\string</em> <strong>$password</strong>, <em>\integer</em> <strong>$cost=10</strong>)</strong> : <em>bool/string</em><br /><em>Получить хеш пароля</em> |
| public | <strong>checkCookie(</strong><em>string</em> <strong>$redirect=`'login'`</strong>)</strong> : <em>void</em><br /><em>Проверка авторизации</em> |
| public | <strong>login(</strong><em>\string</em> <strong>$password</strong>, <em>array</em> <strong>$user</strong>, <em>\string</em> <strong>$redirect=`'admin'`</strong>, <em>\string</em> <strong>$notice</strong>)</strong> : <em>mixed</em><br /><em>Аутентификация, Авторизация</em> |
| public | <strong>logout(</strong><em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>void</em><br /><em>Завершить сессию</em> |
| public | <strong>role(</strong><em>\string</em> <strong>$role</strong>, <em>\string</em> <strong>$privilege</strong>, <em>\boolean</em> <strong>$redirectOrAccess=false</strong>, <em>\string</em> <strong>$redirect=`''`</strong>)</strong> : <em>bool</em><br /><em>Проверка прав доступа</em> |

