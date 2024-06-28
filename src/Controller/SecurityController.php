<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connect/google", name="connect_google")
     */
    public function connectGoogle(): Response
    {
        // Will redirect to Google for authentication
        return $this->get('oauth2.registry')
            ->getClient('google_oauth2')
            ->redirect(['profile', 'email']);
    }

    /**
     * @Route("/connect/github", name="connect_github")
     */
    public function connectGithub(): Response
    {
        // Will redirect to GitHub for authentication
        return $this->get('oauth2.registry')
            ->getClient('github')
            ->redirect(['user:email']);
    }

    /**
     * @Route("/connect/discord", name="connect_discord")
     */
    public function connectDiscord(): Response
    {
        // Will redirect to GitHub for authentication
        return $this->get('oauth2.registry')
            ->getClient('discord')
            ->redirect(['identify', 'email']);
    }

}
