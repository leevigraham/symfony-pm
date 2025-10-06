<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Security\GoogleAuthenticator;
use League\OAuth2\Client\Provider\Google;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class GoogleConnectController extends AbstractController
{
    #[Route('/google/connect', name: 'app_google_connect')]
    public function googleConnect(Request $request, Google $googleOAuthProvider): Response
    {
        // redirect to Google
        $url = $googleOAuthProvider->getAuthorizationUrl([
            // these are actually the default scopes
            'scopes' => [
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ],
        ]);

        // Create an anti-forgery state token
        // @see: https://developers.google.com/identity/openid-connect/openid-connect#createxsrftoken
        $request->getSession()->set(
            GoogleAuthenticator::GOOGLE_OAUTH_STATE_SESSION_KEY,
            $googleOAuthProvider->getState()
        );

        return $this->redirect($url);
    }

    #[Route('/google/connect/check', name: 'security_connect_google_check')]
    public function googleConnectCheck(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the Security/GoogleAuthenticator on your firewall.');
    }
}
