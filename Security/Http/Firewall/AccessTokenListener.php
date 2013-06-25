<?php
namespace Publero\TokenAuthenticationBundle\Security\Http\Firewall;

use OAuth2\OAuth2;
use Publero\TokenAuthenticationBundle\Event\UserEvent;
use Publero\TokenAuthenticationBundle\PubleroTokenAuthenticationEvents;
use Publero\TokenAuthenticationBundle\Security\Core\Authentication\Token\AccessTokenUserToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class AccessTokenListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var OAuth2
     */
    private $oauth;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, OAuth2 $oauth)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->oauth = $oauth;
    }

    public function handle(GetResponseEvent $event)
    {
        if (null === $accessToken = $this->oauth->getBearerToken($event->getRequest(), true)) {
            return;
        }

        $userToken = new AccessTokenUserToken();
        $userToken->setAccessToken($accessToken);

        try {
            $authenticatedUserToken = $this->authenticationManager->authenticate($userToken);
            $this->securityContext->setToken($authenticatedUserToken);

            $event->getDispatcher()->dispatch(PubleroTokenAuthenticationEvents::SECURITY_LOGIN, new UserEvent(
                $authenticatedUserToken->getUser(),
                $event->getRequest()
            ));
        } catch (AuthenticationException $e) {
            $response = new Response();
            $response->setStatusCode(403);
            $event->setResponse($response);
        }
    }
}
