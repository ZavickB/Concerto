<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
