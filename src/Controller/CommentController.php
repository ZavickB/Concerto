<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @Route("comments/{id}", name="fetch_comments")
     */
    public function fetchComments(Request $request, $id)
    {
        $parentId = $request->query->get('parentId');
        $comments = $this->getDoctrine()
                         ->getRepository(Comment::class)
                         ->findBy(['parentComment' => $parentId]);

        return $this->render('project/_comment_list.html.twig', [
            'comments' => $comments,
            'level' => 1, // or calculate based on parent
        ]);
    }
}
