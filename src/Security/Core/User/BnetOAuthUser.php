<?php
namespace App\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;


class BnetOAuthUser implements UserInterface
{
    /** @var int $id */
    private $id;

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

    /** @var array $roles */
    private $roles;

    /** @var bool $enabled */
    private $enabled;

    /** @var string $jwt_token */
    private $jwt_token;

    /** @var \DateTime $jwt_token_expiration */
    private $jwt_token_expiration;

    /** @var string $jwt_refresh_token */
    private $jwt_refresh_token;

    /** @var \DateTime $jwt_refresh_token_expiration */
    private $jwt_refresh_token_expiration;

    /** @var string $email */
    private $email;

    /** @var bool $mail_enabled */
    private $mail_enabled = true;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return BnetOAuthUser
     */
    public function setId(?int $id): BnetOAuthUser
    {
        $this->id = $id;

        return $this;
    }

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
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return BnetOAuthUser
     */
    public function setUsername(?string $username): BnetOAuthUser
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return BnetOAuthUser
     */
    public function setPassword(?string $password): BnetOAuthUser
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
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return BnetOAuthUser
     */
    public function setEnabled(?bool $enabled): BnetOAuthUser
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
    public function setJwtToken(?string $jwt_token): BnetOAuthUser
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
     * @return string
     */
    public function getJwtRefreshToken(): ?string
    {
        return $this->jwt_refresh_token;
    }

    /**
     * @param string $jwt_refresh_token
     * @return BnetOAuthUser
     */
    public function setJwtRefreshToken(?string $jwt_refresh_token): BnetOAuthUser
    {
        $this->jwt_refresh_token = $jwt_refresh_token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getJwtRefreshTokenExpiration(): ?\DateTime
    {
        return $this->jwt_refresh_token_expiration;
    }

    /**
     * @param \DateTime $jwt_refresh_token_expiration
     * @return BnetOAuthUser
     */
    public function setJwtRefreshTokenExpiration(?\DateTime $jwt_refresh_token_expiration): BnetOAuthUser
    {
        $this->jwt_refresh_token_expiration = $jwt_refresh_token_expiration;

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

    /**
     * @return BnetOAuthUser
     */
    public function resetTokenExpiration()
    {
        $this->setJwtTokenExpiration((new \DateTime())->add(new \DateInterval("PT3600S")));
        $this->setJwtRefreshTokenExpiration((new \DateTime())->add(new \DateInterval("P1M")));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return BnetOAuthUser
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isMailEnabled(): ?bool
    {
        return $this->mail_enabled;
    }

    /**
     * @param bool|null $mail_enabled
     * @return BnetOAuthUser
     */
    public function setMailEnabled(?bool $mail_enabled): self
    {
        $this->mail_enabled = $mail_enabled;

        return $this;
    }
}
