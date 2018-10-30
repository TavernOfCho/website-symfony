<?php
namespace App\Security;

use App\Security\Core\User\UserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class CustomAuthenticator
 * @package UserBundle\Security
 */
class TokenAuthenticator extends AbstractGuardAuthenticator implements AuthenticatorInterface
{
    /** @var UserProvider $provider */
    private $provider;

    /** @var RouterInterface $router */
    private $router;

    /** @var UserPasswordEncoderInterface $encoder */
    private $encoder;


    /**
     * CustomAuthenticator constructor.
     * @param UserProvider $provider
     * @param RouterInterface $router
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserProvider $provider, RouterInterface $router, UserPasswordEncoderInterface $encoder)
    {
        $this->provider = $provider;
        $this->router = $router;
        $this->encoder = $encoder;
    }

    /**
     * step 1
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login_check') {
            return null;
        }

        $username = trim($request->request->get('_username'));
        $password = $request->request->get('_password');

        return ['username' => $username, 'password' => $password];
    }

    /**
     * step 2
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        var_dump($credentials);
        $this->provider->getApiSDK()->generateBnetOauthUser($credentials);

        exit;
        $user = null;
//        $user = new BnetOAuthUser();
//        $user
//            ->setUsername($credentials['username'])
//            ->setFirstName($credentials['firstname'])
//            ->setLastName($credentials['lastname'])
//            ->setFullname($credentials['fullname'])
//            ->setEmail($credentials['email'])
//            ->setToken($credentials['token'])
//        ;

        return $user;
    }

    /**
     * step 3
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        var_dump($credentials);
        exit;
        return $this->encoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('index'));
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->router->generate('security_login'));
    }

    /**
     * Called when authentication is needed, but it's not sent
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('security_login'));
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->getPathInfo() === '/login_check';
    }
}
