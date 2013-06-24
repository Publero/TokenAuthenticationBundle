<?php
namespace Publero\TokenAuthenticationBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class AccessToken
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var UserInterface|string
     */
    protected $user;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $roles = array();

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $expires;

    /**
     * @var bool
     */
    protected $revoked = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return UserInterface|string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface|string $user
     * @return self
     */
    public function setUser($user)
    {
        if (!($user instanceof UserInterface || is_string($user))) {
            throw new \InvalidArgumentException(sprintf(
                'User must be either instance of UserInterface or a string (username), % given',
                gettype($user)
            ));
        }

        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $role
     * @return self
     */
    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return self
     */
    public function setRoles(array $roles)
    {
        $this->roles = array_values($roles);

        return $this;
    }

    /**
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    /**
     * @param string $role
     * @return self
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     * @return self
     */
    public function setExpires(\DateTime $expires = null)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires ? ($this->expires->getTimestamp() - time()) <= 0 : false;
    }

    /**
     * @return bool
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    /**
     * @param bool $revoked
     * @return self
     */
    public function setRevoked($revoked)
    {
        $this->revoked = $revoked;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return !($this->isRevoked() || $this->isExpired());
    }

    public function prePersist()
    {
        if ($this->created === null) {
            $this->created = new \DateTime();
        }
    }
}
