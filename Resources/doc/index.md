Getting Started With PubleroTokenAuthenticationBundle
=====================================================

The Symfony2 security component provides a flexible security framework that
allows you to load users from configuration, a database, or anywhere else
you can imagine. The PubleroTokenAuthenticationBundle builds on top of this
to allow you to authenticate users using access token.

Access token authentication is ment to be per-request, and therefore is ideal
for securing API.

**Note:** The firewall using access token authentication must use stateless authentication.

## Prerequisites

This version of the bundle requires Symfony 2.3.

## Installation

Installation is a quick 6 step process:

1. Download PubleroTokenAuthenticationBundle using composer
2. Enable the Bundle
3. Create your AccessToken class
4. Configure PubleroTokenAuthenticationBundle and your application's security.yml
5. Update your database schema

### Step 1: Download PubleroTokenAuthenticationBundle using composer

Add PubleroTokenAuthenticationBundle in your composer.json:

``` js
{
    "require": {
        "publero/token-authentication-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update publero/token-authentication-bundle
```

Composer will install the bundle to your project's `vendor/publero` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Publero\TokenAuthenticationBundle\PubleroTokenAuthenticationBundle(),
    );
}
```

### Step 3: Create your AccessToken class

You can implement `AccessToken` class as [Doctrine ORM](http://symfony.com/doc/current/book/doctrine.html) entity or
[Doctrine MongoDB ODM](http://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html#a-simple-example-a-product) document.

The bundle provides base `Publero\TokenAuthenticationBundle\Model\AccessToken` class. Here is how you use it:
1. Extend the base `AccessToken` class.
2. Map the `id` field. It must be protected as it is inherited from the parent class.
3. Map the `user` field. It can contain either username (string) or mapping to user entity/document.
   This behavior allows you to use the bundle even if you don't store users in database -
   the user will be loaded from [user provider](http://symfony.com/doc/current/book/security.html#where-do-users-come-from-user-providers).
   It also must be protected as it is inherited from the parent class.

**Warning:**
> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.

Example Doctrine ORM AccessToken class (using annotation mapping):

``` php
<?php
// src/Acme/UserBundle/Entity/AccessToken.php

namespace Acme\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_token")
 */
class AccessToken extends Publero\TokenAuthenticationBundle\Model\AccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Acme\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;
}

### Step 4: Configure your application's security.yml

In order for Symfony's security component to use the PubleroTokenAuthenticationBundle, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic security configuration for your application is contained.

Below is a minimal example of the configuration necessary to use the PubleroTokenAuthenticationBundle
in your application:

``` yaml
# app/config/security.yml
security:
    encoders:
        Acme\UserBundle\Entity\User: sha512

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH ]

    providers:
        main:
            entity:
                class: Acme\UserBundle\Entity\User
                property: username

    firewalls:
        api:
            pattern: ^/api
            access_token: true
            anonymous: true     # if you provide api for non-authenticated users
            stateless: true     # stateless authentication is needed for the bundle to work properly

    access_control:
        - { path: ^/api/user, role: ROLE_USER } # only authenticated will be allowed to access part of api,
                                                # which url starts with /api/user
```

Under the `providers` section, you are defining from where the user for security is loaded.

Next, take a look at and examine the `firewalls` section. Here we have declared a
firewall named `api`. By specifying `access_token`, you have told the Symfony2
framework that any time a request is made to this firewall that leads to the
user needing to authenticate himself by providing the access token.

For more information on configuring the `security.yml` file please read the Symfony2
security component [documentation](http://symfony.com/doc/current/book/security.html).

### Step 5: Configure the PubleroTokenAuthenticationBundle

Add the following configuration to your `config.yml` file according to which type
of datastore you are using.

``` yaml
publero_token_authentication:
    db_driver: orm # other valid value is 'mongodb'
    access_token_class: Acme\UserBundle\Entity\AccessToken
```

### Step 6: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `AccessToken` class which you
created in Step 4.

For ORM run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

For MongoDB you can run the following command to create the indexes.

``` bash
$ php app/console doctrine:mongodb:schema:create --index
```

### Next Steps

Now that you have completed the basic installation and configuration of the
PubleroTokenAuthenticationBundle, you are ready to learn about more advanced features and usages
of the bundle.

The following documents are available:

- [Configuration Reference](configuration_reference.md)
- [Command Line Tools](command_line_tools.md)
- [More about binding AccessToken to users](users.md)
- [More about the Doctrine implementations](doctrine.md)
