<?php
namespace Publero\TokenAuthenticationBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class AccessTokenUserToken extends AbstractToken
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getCredentials()
    {
        return $this->accessToken;
    }
}
