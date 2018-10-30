<?php
/**
 * Created by PhpStorm.
 * User: fma
 * Date: 30/10/18
 * Time: 12:58
 */

namespace App\Security\Core\User;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;


class BnetOAuthUser extends OAuthUser
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

    /**
     * BnetOAuthUser constructor.
     * @param string $bnet_id
     * @param string $bnet_sub
     * @param string $bnet_battletag
     * @param string $bnet_access_token
     */
    public function __construct(string $bnet_id, string $bnet_sub, string $bnet_battletag, string $bnet_access_token)
    {
        parent::__construct($bnet_battletag);
        $this->bnet_id = $bnet_id;
        $this->bnet_sub = $bnet_sub;
        $this->bnet_battletag = $bnet_battletag;
        $this->bnet_access_token = $bnet_access_token;
    }

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
}
