<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index()
    {
        $user = $this->getUser();
        if($user){
            $projects = $user->getProjects();
        }else {
            $projects = [];
        }
        return $this->render('dashboard/index.html.twig', [
            'projects' => $projects,
        ]);
    }
}
