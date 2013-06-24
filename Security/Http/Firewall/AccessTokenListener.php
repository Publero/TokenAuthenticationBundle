<?php
namespace Publero\TokenAuthenticationBundle\Security\Http\Firewall;

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

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $accessToken = $event->getRequest()->headers->get('Authentication', null);
        if ($accessToken === null) {
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
