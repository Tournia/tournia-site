<?php

namespace TS\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class OauthRefreshToken extends BaseRefreshToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="OauthClient")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="LoginAccount")
     */
    protected $loginAccount;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set client
     *
     * @param \TS\ApiBundle\Entity\OauthClient $client
     * @return OauthRefreshToken
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \TS\ApiBundle\Entity\OauthClient 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set loginAccount
     *
     * @param \TS\ApiBundle\Entity\LoginAccount $loginAccount
     * @return OauthRefreshToken
     */
    public function setLoginAccount(\TS\ApiBundle\Entity\LoginAccount $loginAccount = null)
    {
        $this->loginAccount = $loginAccount;

        return $this;
    }

    /**
     * Get loginAccount
     *
     * @return \TS\ApiBundle\Entity\LoginAccount 
     */
    public function getLoginAccount()
    {
        return $this->loginAccount;
    }

    /**
     * Set user / LoginAccount
     *
     * @param \TS\ApiBundle\Entity\LoginAccount $user
     * @return OauthAccessToken
     */
    public function setUser(UserInterface $user = null)
    {
        $this->loginAccount = $user;

        return $this;
    }

    /**
     * Get user / LoginAccount
     *
     * @return \TS\ApiBundle\Entity\LoginAccount
     */
    public function getUser()
    {
        return $this->loginAccount;
    }
}
