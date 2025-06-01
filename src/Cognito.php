<?php

/**
 * Cognito provider for Hybridauth
 * Copyright (c) e.World Technology Limited. All rights reserved.
*/

namespace PHPMaker2025\ucarsip;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Cognito OAuth2 provider adapter.
 */
class Cognito extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'openid profile email phone';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = ''; // e.g. https://auth.company.com/, https://<domain>.auth.<region>.amazoncognito.com/

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'oauth2/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'oauth2/token';

    /**
     * Logout URL
     *
     * @var string
     */
    protected $logoutUrl = 'logout?client_id=%s&logout_uri=%s'; // logout_uri is one of the allowed sign-out URLs

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://docs.aws.amazon.com/cognito/';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        if (!$this->config->get('domain')) {
            throw new InvalidArgumentException('Missing domain.');
        }
        parent::initialize();
        $this->apiBaseUrl = IncludeTrailingDelimiter($this->config->get('domain'), false);
        $this->authorizeUrl = $this->apiBaseUrl . $this->authorizeUrl;
        $this->accessTokenUrl = $this->apiBaseUrl . $this->accessTokenUrl;
        if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ];
        }
    }

    public function getLogoutUrl()
    {
        return $this->apiBaseUrl . sprintf($this->logoutUrl, urlencode($this->clientId), urlencode(FullUrlFor("logout")));
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('oauth2/userInfo');
        $data = new Data\Collection($response);
        if (!$data->exists('sub')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();
        $userProfile->identifier = $data->get('sub');
        $userProfile->displayName = $data->get('username');
        $userProfile->email = $data->get('email');
        $userProfile->emailVerified = $data->get('email_verified');
        $userProfile->phone = $data->get('phone_number');
        $userProfile->data = $data->toArray();
        return $userProfile;
    }
}
