<?php
namespace App\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;


class BnetOAuthUser implements UserInterface
{
    /** @var string $bnet_id */
    private $bnet_id;

    /** @var string $bnet_sub */
    private $bnet_sub;

    /** @var string $bnet_battletag */
    private $bnet_battletag;

    /** @var string $bnet_access_token */
    private $bnet_access_token;

    /** @var string $api_token */
    private $api_token;

    /** @var string $username */
    private $username;

    /** @var string $password */
    private $password;

    /** @var string $plainPassword */
    private $plainPassword;

    /**
     * @return string
     */
    public function getBnetId(): string
    {
        return $this->bnet_id;
    }

    /**
     * @param string $bnet_id
     * @return BnetOAuthUser
     */
    public function setBnetId(string $bnet_id): BnetOAuthUser
    {
        $this->bnet_id = $bnet_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getBnetSub(): string
    {
        return $this->bnet_sub;
    }

    /**
     * @param string $bnet_sub
     * @return BnetOAuthUser
     */
    public function setBnetSub(string $bnet_sub): BnetOAuthUser
    {
        $this->bnet_sub = $bnet_sub;

        return $this;
    }

    /**
     * @return string
     */
    public function getBnetBattletag(): string
    {
        return $this->bnet_battletag;
    }

    /**
     * @param string $bnet_battletag
     * @return BnetOAuthUser
     */
    public function setBnetBattletag(string $bnet_battletag): BnetOAuthUser
    {
        $this->bnet_battletag = $bnet_battletag;

        return $this;
    }

    /**
     * @return string
     */
    public function getBnetAccessToken(): string
    {
        return $this->bnet_access_token;
    }

    /**
     * @param string $bnet_access_token
     * @return BnetOAuthUser
     */
    public function setBnetAccessToken(string $bnet_access_token): BnetOAuthUser
    {
        $this->bnet_access_token = $bnet_access_token;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->api_token;
    }

    /**
     * @param string $api_token
     * @return BnetOAuthUser
     */
    public function setApiToken(string $api_token): BnetOAuthUser
    {
        $this->api_token = $api_token;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return BnetOAuthUser
     */
    public function setUsername(string $username): BnetOAuthUser
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return BnetOAuthUser
     */
    public function setPassword(string $password): BnetOAuthUser
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return BnetOAuthUser
     */
    public function setPlainPassword(string $plainPassword): BnetOAuthUser
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array('ROLE_USER', 'ROLE_OAUTH_USER');
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }


    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(UserInterface $user)
    {
        return $user->getUsername() === $this->username;
    }

}
