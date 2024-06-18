<?php 

namespace App\Controller;

use App\Entity\Idea;
use App\Form\IdeaType;
use App\Entity\Project;
use App\Repository\IdeaRepository;
use App\Repository\StatusRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
   
    /**
     * @Route("/projects/{id}", name="project_view", methods={"GET"})
     */
    public function view($id, Request $request, ProjectRepository $projectRepository): Response
    {
        $project = $projectRepository->find($id);

        return $this->render('project/view.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * @Route("/projects/{id}/addIdea", name="project_add_idea", methods={"GET", "POST"})
     */
    public function new($id, Request $request, ProjectRepository $projectRepository, StatusRepository $statusRepository): Response
    {
        $status_name = $request->get('status');
        $status = $statusRepository->findOneBy(['name' => $status_name]);
        // just set up a fresh $task object (remove the example data)
        $idea = new Idea();
        $project = $projectRepository->find($id);
        $idea->setProject($project);
        $idea->setStartDate(new \DateTime());
        $idea->setEndDate(null);
        $idea->setStatus($status);
        $form = $this->createForm(IdeaType::class, $idea);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $idea = $form->getData();

            // Get the currently authenticated user
            $user = $this->getUser();
            $idea->setOwner($user); // Set the user as the owner of the idea

            // Persist the idea
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($idea);
            $entityManager->flush();

            return $this->redirectToRoute(
                'project_view',
                [
                    "id" => $project->getId() 
                ]
             );
        }

        return $this->render('idea/new.html.twig', [
            'form' => $form->createView(), // Passer l'objet FormView à la vue
            'project' => $project
        ]);
    }

    private function initializeIdeas($ideas)
    {
        foreach ($ideas as $idea) {
            $idea->getComments()->initialize(); // Force initialization of comments
        }
    }

}
