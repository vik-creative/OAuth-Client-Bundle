<?php declare(strict_types=1);

namespace Creative\AuthClientBundle\Security;

use Creative\AuthClientBundle\Service\AuthClient;
use Creative\AuthClientBundle\Service\JwtParser;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class CreativeOauthAuthenticator extends AbstractGuardAuthenticator
{
    public function __construct(private AuthClient $authClient, private JwtParser $jwtParser,)
    {
    }

    protected const LOGIN_ROUTE = 'login';
    protected const CODE_FIELD = 'code';

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === self::LOGIN_ROUTE
            && $request->query->get(self::CODE_FIELD) !== null;
    }

    public function getCredentials(Request $request)
    {
        return $request->query->get(self::CODE_FIELD) ?? throw new CustomUserMessageAuthenticationException('Wrong request');
    }

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $jwt = $this->authClient->getUserToken($credentials);
        $token = $this->jwtParser->parse($jwt['access_token']);

        $username = $token->claims()->get('sub');
        if ($username === null) {
            throw new InvalidTokenStructure('Username was not retrieved. Invalid token was received');
        }

        return $userProvider->loadUserByUsername($username);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse('failure');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new JsonResponse($this->authClient->getUserToken($this->getCredentials($request)));
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
