<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\IdeaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IdeaController extends AbstractController
{
    /**
     * @Route("/idea/{id}/comments", name="idea_comments", methods={"GET"})
     */
    public function getIdeaComments($id, IdeaRepository $ideaRepository, Request $request): Response
    {
        $idea = $ideaRepository->find($id);

        // Create a new Comment entity and form
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        return $this->render('project/_idea_comments_modal.html.twig', [
            'idea' => $idea,
            'form' => $form->createView(), // Pass the form view to the template
        ]);
    }

   /**
     * @Route("/idea/{id}/comment/add", name="comment_add", methods={"POST"})
     */
    public function addComment($id, Request $request, IdeaRepository $ideaRepository, EntityManagerInterface $entityManager): Response
    {
        $idea = $ideaRepository->find($id);

        // Create a new Comment entity
        $comment = new Comment();
        $comment->setPostDate(new \DateTime());
        $comment->setIdea($idea);
        $comment->setOwner($this->getUser()); // Assuming you have user authentication

        // Create a form to handle the submission
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Fetch the idea or parent comment ID from the request
            $parentCommentId = $request->request->get('comment')['parentComment'];

            // Set the parent comment (if provided)
            if ($parentCommentId) {
                // Fetch parent comment from database or other source
                $parentComment = $this->getDoctrine()->getRepository(Comment::class)->find($parentCommentId);
                if ($parentComment) {
                    $comment->setParentComment($parentComment);
                } 
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirect back to the idea comments modal or handle as needed
            return $this->redirectToRoute('project_view', ['id' => $idea->getProject()->getId()]);
        }

        // Handle form errors or return appropriate response
        // For simplicity, return a JSON response or render a template
        return new Response('Error handling comment submission.', Response::HTTP_BAD_REQUEST);
    }
}