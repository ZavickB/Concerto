<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class GithubAuthenticator extends AbstractAuthenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_github_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('github');
        $accessToken = $client->getAccessToken();

        try {
            $githubUser = $client->fetchUserFromToken($accessToken);
           
            $avatarUrl = $githubUser->toArray()['avatar_url'] ?? null;

            $email = $githubUser->getEmail();
            $githubId = $githubUser->getId();
            $login = $githubUser->getNickname();

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['githubId' => $githubId]);

            if (!$user) {
                // Check if a user with the same email exists
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if (!$user) {
                    // Generate a random password for new users
                    $randomPassword = sha1(random_bytes(18));

                    $user = new User();
                    $user->setGithubId($githubId);
                    $user->setEmail($email);
                    $user->setUsername($login);
                    $user->setPassword($randomPassword);
                }

                // Set avatar URL in the User entity
                $user->setAvatar($avatarUrl);

                $newProject = new Project();
                $newProject
                    ->setTitle("My first Project")
                    ->setDescription("This is a simple description of your first project")
                    ->setOwner($user)
                    ->setStartDate(new \DateTime());
                    
                $this->entityManager->persist($user);
                $this->entityManager->persist($newProject);
                $this->entityManager->flush();
            }

            return new SelfValidatingPassport(new UserBadge($email, function ($userIdentifier) use ($user) {
                return $user;
            }));

        } catch (IdentityProviderException $e) {
            throw new CustomUserMessageAuthenticationException('GitHub authentication failed: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
