<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;

class DiscordController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/discord", name="connect_discord_start")
     */
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        // will redirect to Discord!
        return $clientRegistry
            ->getClient('discord') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'identify', // the scopes you want to access
                'email',    // adjust scopes as per your requirements
            ]);
    }

    /**
     * After going to Discord, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/discord/check", name="connect_discord_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry): Response
    {
        // Handle the authentication and user retrieval logic
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\DiscordClient $client */
        $client = $clientRegistry->getClient('discord');

        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\DiscordResourceOwner $discordUser */
            $discordUser = $client->fetchUser();

            // do something with all this new power!
            // e.g. $username = $discordUser->getUsername();
            // $email = $discordUser->getEmail();
            // $avatarUrl = $discordUser->getAvatarUrl();

            // Handle user creation/update logic here

            return $this->redirectToRoute('dashboard'); // Redirect to dashboard or any other route
        } catch (\Exception $e) {
            // something went wrong!
            // probably you should return the reason to the user
            return new Response('Authentication failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
