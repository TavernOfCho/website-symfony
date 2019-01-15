<?php

namespace App\Controller;

use App\Configuration\CharacterRequired;
use App\Form\RealmPlayerType;
use App\Security\Core\User\BnetOAuthUser;
use App\Utils\CharacterHelper;
use App\Utils\WowCollectionSDKExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @var WowCollectionSDKExtension
     */
    private $SDKExtension;

    /**
     * @param SessionInterface $session
     * @param CharacterHelper $characterHelper
     * @param WowCollectionSDKExtension $SDKExtension
     */
    public function __construct(SessionInterface $session, CharacterHelper $characterHelper, WowCollectionSDKExtension $SDKExtension)
    {
        $this->session = $session;
        $this->characterHelper = $characterHelper;
        $this->SDKExtension = $SDKExtension;
    }

    /**
     * @Route("/", name="dashboard_index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(RealmPlayerType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (null === $profile = $this->SDKExtension->findCharacter($data['character_name'], $data['realm'])) {
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


    //TODO profile action

    /**
     * @Route("/stats", name="dashboard_stats")
     * @CharacterRequired()
     * @param Request $request
     * @return Response
     */
    public function stats(Request $request): Response
    {
        list('realm' => $realm, 'name' => $username) = $request->attributes->get('_character');
        $profile = $this->SDKExtension->findCharacter($username, $realm);
        $items = $this->SDKExtension->findCharacterItems($username, $realm);
        $profile['stats'] = $this->SDKExtension->getCharacterStats($username, $realm);

        return $this->render('dashboard/stats.html.twig', [
            'realm' => $this->SDKExtension->getRealm($realm),
            'player' => $username,
            'profile' => $profile,
            'items' => $items,
        ]);
    }
}
