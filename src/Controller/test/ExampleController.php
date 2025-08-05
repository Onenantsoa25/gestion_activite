<?php
namespace App\Controller\test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\test\Util;

class ExampleController extends AbstractController
{
    // Route pour afficher une vue avec des données (model-view)
    #[Route('/aaa', name: 'app_accueil1')]
    public function accueil(): Response
    {
        $nom = 'Manoa';
        $age = 22;

        $util = new Util($age, $nom);

        // Bufferisation de sortie
        ob_start();
        extract(['util' => $util]); 
        // Rendre les variables disponibles via include
        include __DIR__ . '/../../templates/accueil.php';

        $content = ob_get_clean();

        return new Response($content);
    }

    // Route qui redirige vers la précédente
    #[Route('/rediriger', name: 'app_redirection')]
    public function redirection(): Response
    {
        // Redirection vers la route "app_accueil"
        return $this->redirectToRoute('app_accueil');
    }
}
