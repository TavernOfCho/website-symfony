<?php

namespace App\Controller;

use App\Configuration\CharacterRequired;
use App\Form\RealmPlayerType;
use App\Form\UserEmailType;
use App\Manager\CharacterManager;
use App\Manager\RealmManager;
use App\Security\Core\User\BnetOAuthUser;
use App\Utils\CharacterHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @Security("is_granted('ROLE_USER')")
 * @method BnetOAuthUser getUser()
 */
class DashboardController extends AbstractController
{
    /**
     * @var SessionInterface $session
     */
    private $session;

    /**
     * @var CharacterHelper $characterHelper
     */
    private $characterHelper;

    /**
     * @param SessionInterface $session
     * @param CharacterHelper $characterHelper
     */
    public function __construct(SessionInterface $session, CharacterHelper $characterHelper)
    {
        $this->session = $session;
        $this->characterHelper = $characterHelper;
    }

    /**
     * @Route("/", name="dashboard_index")
     * @param Request $request
     * @param CharacterManager $characterManager
     * @return Response
     */
    public function index(Request $request, CharacterManager $characterManager): Response
    {
        $form = $this->createForm(RealmPlayerType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (null === $profile = $characterManager->findCharacter($data['character_name'], $data['realm'])) {
                $this->addFlash('error',
                    sprintf('The player %s for realm %s does not exists', $data['character_name'], $data['realm']));

                return $this->redirectToRoute('dashboard_index');
            }

            $this->session->set('character', ['realm' => $data['realm'], 'name' => $data['character_name']]);

            if ($request->query->get('redirect')) {
                return $this->redirect($request->query->get('redirect'));
            }

            return $this->redirectToRoute('dashboard_stats');
        }

        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/profile", name="dashboard_profile")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function profile(Request $request)
    {
        $form = $this->createForm(UserEmailType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()
                ->setEmail($form->get('email')->getData())
                ->setMailEnabled($form->get('mail_enabled')->getData());

            //TODO transfert to API
//            $this->getDoctrine()->getManager()->persist($this->getUser());
//            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Informations updated');

            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('dashboard/profile.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/stats", name="dashboard_stats")
     * @CharacterRequired()
     *
     * @param Request $request
     * @param CharacterManager $characterManager
     * @param RealmManager $realmManager
     * @return Response
     */
    public function stats(Request $request, CharacterManager $characterManager, RealmManager $realmManager): Response
    {
        list('realm' => $realm, 'name' => $username) = $request->attributes->get('_character');
        $profile = $characterManager->findCharacter($username, $realm);
        $items = $characterManager->findCharacterItems($username, $realm);
        $profile['stats'] = $characterManager->getCharacterStats($username, $realm);

        return $this->render('dashboard/stats.html.twig', [
            'realm' => $realmManager->getRealm($realm),
            'player' => $username,
            'profile' => $profile,
            'items' => $items,
        ]);
    }
}
