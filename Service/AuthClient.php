<?php declare(strict_types=1);

namespace Creative\AuthClientBundle\Service;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $oauthServerUrl,
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri,
    ) {
    }

    private function retrieveToken(array $formData): array
    {
        try {
            $response = $this->httpClient->request('POST', \sprintf('%s/token', $this->oauthServerUrl), [
                'body' => $formData,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new CustomUserMessageAuthenticationException(message: $e->getMessage(), previous: $e);
        }

        try {
            $jsonData = $response->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            throw new CustomUserMessageAuthenticationException(message: $e->getMessage(), previous: $e);
        }

        $tokenData = \json_decode(json: $jsonData, associative: true, flags: JSON_THROW_ON_ERROR);
        if (($tokenData['access_token'] ?? null) === null) {
            throw new CustomUserMessageAccountStatusException('Token not found');
        }

        return $tokenData;
    }

    public function getUserToken(string $username): array
    {
        $formData = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $username,
        ];

        return $this->retrieveToken($formData);
    }

    public function getClientToken(array $scope = ['profile']): array
    {
        $formData = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => \join(' ', $scope),
        ];

        return $this->retrieveToken($formData);
    }

    public function getUserData(string $username): array
    {
        $clientToken = $this->getClientToken()['access_token'];

        try {
            $response = $this->httpClient->request('GET', \sprintf('%s/api/users/%s', $this->oauthServerUrl, $username), ['auth_bearer' => $clientToken]);
        } catch (TransportExceptionInterface $e) {
            throw new CustomUserMessageAuthenticationException(message: $e->getMessage(), previous: $e);
        }

        try {
            $rawUserData = $response->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            throw new CustomUserMessageAuthenticationException(message: $e->getMessage(), previous: $e);
        }

        return \json_decode($rawUserData, true, flags: JSON_THROW_ON_ERROR);
    }
}
