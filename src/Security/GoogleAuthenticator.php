<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends AbstractAuthenticator
{
    public const GOOGLE_OAUTH_STATE_SESSION_KEY = 'google_oauth_state';

    public function __construct(
        private readonly Google                 $googleProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface        $router
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'security_connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        // Confirm anti-forgery state token
        // @see: https://developers.google.com/identity/openid-connect/openid-connect#confirmxsrftoken
        $state = $request->query->get('state');
        $sessionState = $request->getSession()->get(self::GOOGLE_OAUTH_STATE_SESSION_KEY);
        if (!$state || $sessionState !== $state) {
            throw new CustomUserMessageAuthenticationException(
                'There was an error getting access from Google. Please try again. Possible CSRF attack.'
            );
        }
        $request->getSession()->remove(self::GOOGLE_OAUTH_STATE_SESSION_KEY);

        // Exchange code for access token and ID token
        // @see: https://developers.google.com/identity/openid-connect/openid-connect#exchangecode
        $code = $request->query->get('code');
        if (!$code) {
            throw new CustomUserMessageAuthenticationException(
                'There was an error getting access from Google. Please try again.'
            );
        }

        try {
            $accessToken = $this->googleProvider->getAccessToken(
                'authorization_code',
                ['code' => $code]
            );
        } catch (IdentityProviderException $e) {
            // probably the authorization code has been used already
            $response = $e->getResponseBody();
            $errorCode = $response['error'];
            $errorMessage = $response['error_description'];

            throw new CustomUserMessageAuthenticationException(
                sprintf('There was an error logging you into Google: %s [%s]',
                    $errorMessage,
                    $errorCode
                )
            );
        }

        // Return a Self Validating Passport
        // @see: https://symfony.com/doc/current/security/custom_authenticator.html#self-validating-passport
        // @see: https://github.com/knpuniversity/oauth2-client-bundle?tab=readme-ov-file#step-1-using-the-new-oauth2authenticator-class
        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken) {
                // Obtain user information from the ID token
                // @see: https://developers.google.com/identity/openid-connect/openid-connect#obtainuserinfo
                /** @var GoogleUser $googleUser */
                $googleUser = $this->googleProvider->getResourceOwner($accessToken);
                $googleEmail = $googleUser->getEmail();

                // Check for an existing user
                $existingUser = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['googleAccountId' => $googleUser->getId()]);

                // If one exists return them
                // Nothing more to do
                if ($existingUser) {
                    return $existingUser;
                }

                // Is there a user that matches the email?
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $googleEmail]);

                /**
                 * @TODO: Determine what should happen when there's no user.
                 * We could create a User as we do below
                 * We could redirect to a registration form and prompt them to add more details / set password etc
                 */
                // No user… create one and set some values
                if (!$user) {
                    $user = new User();
                    $user->email = $googleUser->getEmail();
                    $user->emailVerified = $googleUser->toArray()['email_verified'] ?? false;
                    $user->password = '';
                    $user->displayName = trim("{$googleUser->getFirstName()} {$googleUser->getLastName()}");
                }

                // Add the googleId
                $user->googleAccountId = $googleUser->getId();
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Return the user
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('app_dashboard');

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }


    //    public function start(Request $request, ?AuthenticationException $authException = null): Response
    //    {
    //        /*
    //         * If you would like this class to control what happens when an anonymous user accesses a
    //         * protected page (e.g. redirect to /login), uncomment this method and make this class
    //         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //         *
    //         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //         */
    //    }
}
