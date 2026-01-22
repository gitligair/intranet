<?php

namespace App\Controller;

use App\Entity\BaseScript;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseScriptController extends AbstractController
{

    #[Route('/scripts/download/{id}', name: 'script_download')]
    public function download(BaseScript $script): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/scripts/' . $script->getFilename();

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Fichier non trouvé.');
        }

        return $this->file(
            $path,
            $script->getFilename(),
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
