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
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class GoogleAuthenticator extends AbstractAuthenticator
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
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();

        try {
            $googleUser = $client->fetchUserFromToken($accessToken);

            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleId]);

            if (!$user) {
                // Vérifiez si un utilisateur avec le même email existe déjà
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $random = sha1(random_bytes(18));

                    $user = new User();
                    $user->setEmail($email);
                    $user->setUsername($googleUser->getName());
                    $user->setPassword($random);
                }

                // Liez l'utilisateur existant ou nouvel utilisateur avec le googleId
                $user->setGoogleId($googleId);

                if (!$user->getId()) {
                    $newProject = new Project();
                    $newProject
                        ->addContributor($user)
                        ->setTitle("Your first Project")
                        ->setDescription("This is a simple description of your first project")
                        ->setOwner($user)
                        ->setStartDate(new \DateTime());

                        
                    $this->entityManager->persist($newProject);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            return new SelfValidatingPassport(new UserBadge($email, function ($userIdentifier) use ($user) {
                return $user;
            }));

        } catch (IdentityProviderException $e) {
            throw new CustomUserMessageAuthenticationException('Google authentication failed: ' . $e->getMessage());
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
