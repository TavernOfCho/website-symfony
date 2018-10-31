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

    /** @var string $username */
    private $username;

    /** @var string $password */
    private $password;

    /** @var string $plainPassword */
    private $plainPassword;

    /** @var array $roles */
    private $roles;

    /** @var bool $enabled */
    private $enabled;

    /** @var string $jwt_token */
    private $jwt_token;

    /** @var \DateTime $jwt_token_expiration */
    private $jwt_token_expiration;

    /**
     * @return string
     */
    public function getBnetId(): ?string
    {
        return $this->bnet_id;
    }

    /**
     * @param string $bnet_id
     * @return BnetOAuthUser
     */
    public function setBnetId(?string $bnet_id): BnetOAuthUser
    {
        $this->bnet_id = $bnet_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getBnetSub(): ?string
    {
        return $this->bnet_sub;
    }

    /**
     * @param string $bnet_sub
     * @return BnetOAuthUser
     */
    public function setBnetSub(?string $bnet_sub): BnetOAuthUser
    {
        $this->bnet_sub = $bnet_sub;

        return $this;
    }

    /**
     * @return string
     */
    public function getBnetBattletag(): ?string
    {
        return $this->bnet_battletag;
    }

    /**
     * @param string $bnet_battletag
     * @return BnetOAuthUser
     */
    public function setBnetBattletag(?string $bnet_battletag): BnetOAuthUser
    {
        $this->bnet_battletag = $bnet_battletag;

        return $this;
    }

    /**
     * @return string
     */
    public function getBnetAccessToken(): ?string
    {
        return $this->bnet_access_token;
    }

    /**
     * @param string $bnet_access_token
     * @return BnetOAuthUser
     */
    public function setBnetAccessToken(?string $bnet_access_token): BnetOAuthUser
    {
        $this->bnet_access_token = $bnet_access_token;

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
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return BnetOAuthUser
     */
    public function setRoles(array $roles): BnetOAuthUser
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return BnetOAuthUser
     */
    public function setEnabled(bool $enabled): BnetOAuthUser
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getJwtToken(): ?string
    {
        return $this->jwt_token;
    }

    /**
     * @param string $jwt_token
     * @return BnetOAuthUser
     */
    public function setJwtToken(string $jwt_token): BnetOAuthUser
    {
        $this->jwt_token = $jwt_token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getJwtTokenExpiration(): ?\DateTime
    {
        return $this->jwt_token_expiration;
    }

    /**
     * @param \DateTime $jwt_token_expiration
     * @return BnetOAuthUser
     */
    public function setJwtTokenExpiration(?\DateTime $jwt_token_expiration): BnetOAuthUser
    {
        $this->jwt_token_expiration = $jwt_token_expiration;

        return $this;
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
