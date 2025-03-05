<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $urlGenerator;
    private $userRepository;
    private $entityManager;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->clientRegistry = $clientRegistry;
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): bool
    {
        return 'connect_google_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();
        $googleUser = $client->fetchUserFromToken($accessToken);

        $user = $this->userRepository->findOneBy(['email' => $googleUser->getEmail()]);

        if (!$user) {
            $user = new User();
            $user->setEmail($googleUser->getEmail());
            $user->setUsername($googleUser->getName());
            $user->setRoles(['ROLE_USER']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

        }

        return new SelfValidatingPassport(
            new UserBadge($googleUser->getEmail(), function () use ($user) {
                return $user;
            }),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}