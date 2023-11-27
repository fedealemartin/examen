<?php

namespace App\Controller;

use App\Entity\Ciudad;
use App\Form\CiudadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\CiudadRepository;
use App\Security\PostVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * @Route("/ciudad")
 * @IsGranted("ROLE_ADMIN")
 */
class CiudadController extends AbstractController
{
    /**
     * @Route("/", name="ciudad_index", methods={"GET"})
     * @Route("/", methods="GET", name="admin_index")
     * @Route("/", methods="GET", name="admin_post_index")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $ciudads = $entityManager
            ->getRepository(Ciudad::class)
            ->findAll();

        return $this->render('ciudad/index.html.twig', [
            'ciudads' => $ciudads,
        ]);
    }

    /**
     * @Route("/new", name="ciudad_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ciudad = new Ciudad();
        $form = $this->createForm(CiudadType::class, $ciudad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ciudad);
            $entityManager->flush();

            return $this->redirectToRoute('ciudad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ciudad/new.html.twig', [
            'ciudad' => $ciudad,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="ciudad_show", methods={"GET"})
     */
    public function show(Ciudad $ciudad): Response
    {
        return $this->render('ciudad/show.html.twig', [
            'ciudad' => $ciudad,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ciudad_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Ciudad $ciudad, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CiudadType::class, $ciudad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('ciudad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('ciudad/edit.html.twig', [
            'ciudad' => $ciudad,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="ciudad_delete", methods={"POST"})
     */
    public function delete(Request $request, Ciudad $ciudad, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ciudad->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ciudad);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ciudad_index', [], Response::HTTP_SEE_OTHER);
    }

    

}
