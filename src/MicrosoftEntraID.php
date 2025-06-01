<?php

/**
 * Microsoft Entra ID provider for Hybridauth
 * Copyright (c) e.World Technology Limited. All rights reserved.
*/

namespace PHPMaker2025\ucarsip;

use Hybridauth\Adapter\OAuth2;
use HybridauthException\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;
use Exception;

class MicrosoftEntraID extends OAuth2
{
    public string $providerName = "Microsoft";
    public static string $graphEndpoint = "graph.microsoft.com";
    public static string $endpoint = "login.microsoftonline.com";
    public static string $tenantId = "common";

    /**
     * {@inheritdoc}
     */
    public $scope = "openid profile email offline_access https://%s/User.Read";

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = "https://%s/%s/oauth2/v2.0/authorize";

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = "https://%s/%s/oauth2/v2.0/token";
    protected $userInfoUrl = "https://%s/oidc/userinfo";

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();
        $this->scope = sprintf($this->scope, self::$graphEndpoint);
        $this->userInfoUrl = sprintf($this->userInfoUrl, self::$graphEndpoint);
        $this->authorizeUrl = sprintf($this->authorizeUrl, self::$endpoint, self::$tenantId);
        $this->accessTokenUrl = sprintf($this->accessTokenUrl, self::$endpoint, self::$tenantId);
        $this->AuthorizeUrlParameters += [
            "access_type" => "offline"
        ];
        $this->tokenRefreshParameters = ($this->tokenRefreshParameters ?? []) + [
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAccessTokenExchange($response)
    {
        $collection = parent::validateAccessTokenExchange($response);
        if ($collection->exists("id_token")) {
            $idToken = $collection->get("id_token");
            $parts = explode(".", $idToken);
            list($headb64, $payload) = $parts;
            $data = UrlBase64Decode($payload); // JWT token is url-safe base64 encoded
            $this->storeData("user_data", $data);
        } else {
            throw new Exception("No id_token was found.");
        }
        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $userData = $this->getStoredData("user_data");
        $user = json_decode($userData);
        $data = new Data\Collection($user);
        $userProfile = new User\Profile();
        $userProfile->identifier = $data->get("sub");
        $userProfile->displayName = $data->get("name") ?? $data->get("preferred_username");
        $userProfile->photoURL = $data->get("picture");
        $userProfile->email = $data->get("email");
        $userProfile->data = $data->toArray();
        if (
            !empty($this->userInfoUrl)
            && !isset(
                $userProfile->displayName,
                $userProfile->photoURL,
                $userProfile->email,
                $userProfile->data["groups"]
            )
        ) {
            $profile = new Data\Collection($this->apiRequest($this->userInfoUrl));
            $userProfile->firstName = $profile->get("givenname");
            $userProfile->lastName = $profile->get("familyname");
            $userProfile->language = $profile->get("locale");
            if (empty($userProfile->displayName)) {
                $userProfile->displayName = $profile->get("name") ?? $profile->get("nickname");
            }
            if (empty($userProfile->photoURL)) {
                $userProfile->photoURL = $profile->get("picture") ?? $profile->get("avatar");
                if (preg_match('/<img.+src=["\'](.+?)["\']/i', $userProfile->photoURL, $m)) {
                    $userProfile->photoURL = $m[1];
                }
            }
            if (empty($userProfile->email)) {
                $userProfile->email = $profile->get("preferred_username");
            }
            $userProfile->data += $profile->toArray();
        }
        return $userProfile;
    }
}
