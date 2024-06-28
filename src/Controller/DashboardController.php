<?php 
namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if($user){
            $projects = $user->getProjects();
            $collabs  = $entityManager->getRepository(Project::class)->findUserCollabs($user->getId());
        }else {
            $projects = [];
            $collabs  = [];
        }
        return $this->render('dashboard/index.html.twig', [
            'projects' => $projects,
            'collabs' => $collabs
        ]);
    }
}
