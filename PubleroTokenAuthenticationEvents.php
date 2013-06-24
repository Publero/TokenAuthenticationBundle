<?php
namespace Publero\TokenAuthenticationBundle;

final class PubleroTokenAuthenticationEvents
{
    /**
     * The SECURITY_ACCESS_TOKEN_LOGIN event occurs when the user is logged in using access token.
     *
     * The event listener method receives a Publero\TokenAuthenticationBundle\Event\UserEvent instance.
     */
    const SECURITY_LOGIN = 'publero_token_authentication.security.login';
}
