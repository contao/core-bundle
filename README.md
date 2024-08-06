# Contao core bundle

[![](https://img.shields.io/packagist/v/contao/core-bundle.svg?style=flat-square)](https://packagist.org/packages/contao/core-bundle)
[![](https://img.shields.io/packagist/dt/contao/core-bundle.svg?style=flat-square)](https://packagist.org/packages/contao/core-bundle)

Contao is an Open Source PHP Content Management System for people who want a professional website that is easy to
maintain. Visit the [project website][1] for more information.

Contao has been designed as a [Symfony][2] bundle, which can be used to add CMS functionality to any Symfony
application. If you do not have an existing Symfony application yet, we recommend using the [Contao managed edition][3]
as basis for your application.

## Prerequisites

The Contao core bundle has a recipe in the [symfony/recipes-contrib][6] repository. Be sure to either enable contrib
recipes for your project by running the following command or follow the instructions to use the contrib recipe during
the installation process.

```
composer config extra.symfony.allow-contrib true
```

Add the `contao-component-dir` to the `extra` section of your `composer.json` file.

```
composer config extra.contao-component-dir assets
```

## Installation

Install Contao and all its dependencies by executing the following command:

```
composer require \
    contao/core-bundle:4.8.* \
    php-http/guzzle6-adapter:^1.1
```

Note that you can exchange the `php-http/guzzle6-adapter` package with any other [HTTP client implementation][4]. If you
already have an HTTP client implementation, you can omit the package entirely.

## Configuration

Configure the `DATABASE_URL` in your environment, either using environment variables or by using the
[Dotenv component][7].

Enable ESI in the `config/packages/framework.yaml` file.

```yaml
framework:
    esi: true
```

Add the Contao routes to your `config/routing.yaml` file, and be sure to load the `ContaoCoreBundle` at the very end, so
the catch-all route does not catch your application routes.

```yml
ContaoCoreBundle:
    resource: "@ContaoCoreBundle/config/routes.yaml"
```

Edit your `config/security.yaml` file and merge all the `providers`, `encoders`, `firewalls` and `access_control`
sections:

```yml
security:
    password_hashers:
        Contao\User: auto
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: auto

    providers:
        contao.security.backend_user_provider:
            id: contao.security.backend_user_provider

        contao.security.frontend_user_provider:
            id: contao.security.frontend_user_provider

    firewalls:
        contao_backend:
            request_matcher: contao.routing.backend_matcher
            provider: contao.security.backend_user_provider
            user_checker: contao.security.user_checker
            switch_user: true
            login_throttling: ~

            login_link:
                check_route: contao_backend_login_link
                signature_properties: [username, lastLogin]
                success_handler: contao.security.authentication_success_handler

            contao_login:
                remember_me: false

            logout:
                path: contao_backend_logout

        contao_frontend:
            request_matcher: contao.routing.frontend_matcher
            provider: contao.security.frontend_user_provider
            user_checker: contao.security.user_checker
            access_denied_handler: contao.security.access_denied_handler
            switch_user: false
            login_throttling: ~

            contao_login:
                remember_me: true

            remember_me:
                secret: '%kernel.secret%'
                remember_me_parameter: autologin
                token_provider:
                    doctrine: true

            logout:
                path: contao_frontend_logout

    access_control:
        - { path: ^%contao.backend.route_prefix%/login$, roles: PUBLIC_ACCESS }
        - { path: ^%contao.backend.route_prefix%/logout$, roles: PUBLIC_ACCESS }
        - { path: ^%contao.backend.route_prefix%(/|$), roles: ROLE_USER }
        - { path: ^/, roles: [PUBLIC_ACCESS] }
```

The Contao core-bundle is now installed and activated. Use the `contao:migrate` command to upgrade the database and the
`contao:user:create` command to create your first back end user.

## License

Contao is licensed under the terms of the LGPLv3.

## Getting support

Visit the [support page][5] to learn about the available support options.

[1]: https://contao.org
[2]: https://symfony.com
[3]: https://github.com/contao/managed-edition
[4]: https://packagist.org/providers/php-http/client-implementation
[5]: https://to.contao.org/support
[6]: https://github.com/symfony/recipes-contrib
[7]: http://symfony.com/doc/current/components/dotenv.html
