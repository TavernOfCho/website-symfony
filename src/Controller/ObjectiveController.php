<?php

namespace App\Controller;

use App\Configuration\CharacterRequired;
use App\Form\ObjectiveType;
use App\Manager\ObjectiveManager;
use App\Utils\CharacterHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/objectives")
 * @Security("is_granted('ROLE_USER')")
 */
class ObjectiveController extends AbstractController
{

    /**
     * @var ObjectiveManager $objectiveManager
     */
    private $objectiveManager;
    /**
     * @var CharacterHelper $characterHelper
     */
    private $characterHelper;

    /**
     * @param ObjectiveManager $objectiveManager
     * @param CharacterHelper $characterHelper
     */
    public function __construct(ObjectiveManager $objectiveManager, CharacterHelper $characterHelper)
    {
        $this->objectiveManager = $objectiveManager;
        $this->characterHelper = $characterHelper;
    }

    /**
     * @Route("/", name="objectives_index")
     * @CharacterRequired()
     *
     * @return Response
     */
    public function index(): Response
    {
        $objectives = $this->objectiveManager->findAllForCurrentUser();

        return $this->render('objective/index.html.twig', [
            'objectives' => $objectives,
        ]);
    }

    /**
     * @Route("/create", name="objectives_create")
     * @CharacterRequired()
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        list('realm' => $realm, 'name' => $username) = $request->attributes->get('_character');

        $form = $this->createForm(ObjectiveType::class, null, ['username' => $username, 'realm' => $realm]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objective = $form->getData();
            $objective['character'] = ['username' => $username, 'realm' => $realm];
            $this->objectiveManager->push($objective);

            $this->addFlash('success', 'The objective is now listed !');

            return $this->redirectToRoute('objectives_index');
        }

        return $this->render('objective/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
