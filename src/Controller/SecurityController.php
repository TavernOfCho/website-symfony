<?php

namespace App\Controller;

use App\Form\UserRegistrationForm;
use App\Security\TokenAuthenticator;
use App\Utils\WowCollectionSDK;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\LoginForm;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="security_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $form = $this->createForm(LoginForm::class, [
            '_username' => $authenticationUtils->getLastUsername(),
        ]);

        return $this->render('Security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');
    }

    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @param WowCollectionSDK $apiSDK
     * @param GuardAuthenticatorHandler $guardAuthenticatorHandler
     * @param TokenAuthenticator $authenticator
     * @return Response
     */
    public function registerAction(Request $request, WowCollectionSDK $apiSDK, GuardAuthenticatorHandler $guardAuthenticatorHandler,
                                   TokenAuthenticator $authenticator)
    {
        $form = $this->createForm(UserRegistrationForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $user = $apiSDK->createAccount($form->getData())) {
                $this->addFlash('danger', 'An error occured while creating your account.');

                return $this->redirectToRoute('security_login');
            }

            $this->addFlash('success', sprintf('Bienvenue %s', $user->getUsername()));

            return $guardAuthenticatorHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'main');
        }

        return $this->render('Security/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

}
