<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaulController extends AbstractController
{
    #[Route('/defaul', name: 'defaul')]
    public function index(): Response
    {
        return $this->render('defaul/index.html.twig', [
            'controller_name' => 'DefaulController',
        ]);
    }

    /**
     * @Route("/autocomplete", name="autocomplete")
     */
    public function autocomplete(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('App\Entity\Provincia')->createQueryBuilder('c')
           ->where('c.descripcion LIKE :descripcion')
           ->setParameter('descripcion', '%'.$term.'%')
           ->getQuery()
           ->getResult();

        foreach ($entities as $entity)
        {
            $names[] = $entity->getDescripcion();
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }


    /**
       * @Route("/search", methods="GET", name="search")
       */
      public function search(Request $request)
      {
          $query = $request->query->get('q', '');
          //$limit = $request->query->get('l', 10);

          if (!$request->isXmlHttpRequest()) {
              return $this->render('ciudad/search.html.twig', ['query' => $query]);
          } 

          $query = trim(strip_tags($query));

          $em = $this->getDoctrine()->getManager();

          $entities = $em->getRepository('App\Entity\Ciudad')->createQueryBuilder('c')
             ->where('c.descripcion LIKE :descripcion')
             ->setParameter('descripcion', '%'.$query.'%')
             ->getQuery()
             ->getResult();




          //$foundCiudades = $posts->findBySearchQuery($query, $limit);

          $results = [];
          foreach ($entities as $entity) {
              $results[] = [
                  'title' => htmlspecialchars($entity->getDescripcion(), \ENT_COMPAT | \ENT_HTML5),
                  'url' => $this->generateUrl('ciudad_edit', ['id' => $entity->getId()]),
              ];
          }

          return $this->json($results);
      }
}
