<?php

namespace App\Controller;

use App\Entity\Provincia;
use App\Form\ProvinciaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProvinciaRepository;
use App\Security\PostVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * @Route("/provincia")
 * @IsGranted("ROLE_ADMIN")
 */
class ProvinciaController extends AbstractController
{
    /**
     * @Route("/", name="provincia_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $provincias = $entityManager
            ->getRepository(Provincia::class)
            ->findAll();

        return $this->render('provincia/index.html.twig', [
            'provincias' => $provincias,
        ]);
    }

    /**
     * @Route("/new", name="provincia_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $provincium = new Provincia();
        $form = $this->createForm(ProvinciaType::class, $provincium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($provincium);
            $entityManager->flush();

            return $this->redirectToRoute('provincia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('provincia/new.html.twig', [
            'provincium' => $provincium,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="provincia_show", methods={"GET"})
     */
    public function show(Provincia $provincium): Response
    {
        return $this->render('provincia/show.html.twig', [
            'provincium' => $provincium,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="provincia_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Provincia $provincium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProvinciaType::class, $provincium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('provincia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('provincia/edit.html.twig', [
            'provincium' => $provincium,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="provincia_delete", methods={"POST"})
     */
    public function delete(Request $request, Provincia $provincium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$provincium->getId(), $request->request->get('_token'))) {
            $entityManager->remove($provincium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('provincia_index', [], Response::HTTP_SEE_OTHER);
    }


}
