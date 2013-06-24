<?php
namespace Publero\TokenAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEvent extends Event
{
    private $request;
    private $user;

    /**
     * @param UserInterface|string $user
     * @param Request $request
     */
    public function __construct($user, Request $request)
    {
        if (!($user instanceof UserInterface || is_string($user))) {
            throw new \InvalidArgumentException(sprintf(
                'User must be either instance of UserInterface or a string (username), % given',
                gettype($user)
            ));
        }

        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @return UserInterface|string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
