<?php declare(strict_types=1);

namespace Creative\AuthClientBundle\Security;

use Creative\AuthClientBundle\Dto\User;
use Creative\AuthClientBundle\Service\AuthClient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method UserInterface loadUserByIdentifier(string $identifier)
 */
class CreativeOauthProvider implements UserProviderInterface
{
    public function __construct(
        private AuthClient $authClient,
    ) {
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->makeUser($username);
    }

    private function makeUser(string $username): UserInterface
    {
        $user = (new User())->setUsername($username);
        $this->loadUserData($user);

        return $user;
    }

    private function loadUserData(User $user): void
    {
        $userData = $this->authClient->getUserData($user->getUsername());

        $user
            ->setUsername($userData['username'] ?? null)
            ->setEmail($userData['email'] ?? null)
        ;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return \is_a($class, UserInterface::class, true);
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method UserInterface loadUserByIdentifier(string $identifier)
    }
}
