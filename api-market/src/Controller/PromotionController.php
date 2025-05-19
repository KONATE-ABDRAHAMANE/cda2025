<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/promotions')]
class PromotionController extends AbstractController
{
    // CREATE
    #[Route('/ajout', name: 'promotion_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, PromotionRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        // Vérifie si une promotion identique existe déjà
        $existing = $repo->findOneBy([
            'titre' => $data['titre'],
            'debut' => new \DateTime($data['debut']),
            'fin' => new \DateTime($data['fin']),
        ]);
    
        if ($existing) {
            return $this->json([
                'error' => 'Une promotion avec le même titre, début et fin existe déjà.',
                'id' => $existing->getId()
            ], 409); // 409 Conflict
        }
    
        $promotion = new Promotion();
        $promotion->setTitre($data['titre']);
        $promotion->setDebut(new \DateTime($data['debut']));
        $promotion->setFin(new \DateTime($data['fin']));
    
        $em->persist($promotion);
        $em->flush();
    
        return $this->json($this->serialize($promotion), 201);
    }

    // READ ALL
    #[Route('/', name: 'promotion_index', methods: ['GET'])]
    public function index(PromotionRepository $repo): JsonResponse
    {
        $promotions = $repo->findAll();
        return $this->json(array_map([$this, 'serialize'], $promotions));
    }

    // READ ONE
    #[Route('/{id}', name: 'promotion_show', methods: ['GET'])]
    public function show(Promotion $promotion): JsonResponse
    {
        return $this->json($this->serialize($promotion));
    }

    // UPDATE
    #[Route('/{id}', name: 'promotion_update', methods: ['PUT'])]
    public function update(Promotion $promotion, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['titre'])) $promotion->setTitre($data['titre']);
        if (isset($data['debut'])) $promotion->setDebut(new \DateTime($data['debut']));
        if (isset($data['fin'])) $promotion->setFin(new \DateTime($data['fin']));

        $em->flush();

        return $this->json($this->serialize($promotion));
    }

    // DELETE
    #[Route('/{id}', name: 'promotion_delete', methods: ['DELETE'])]
    public function delete(Promotion $promotion, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($promotion);
        $em->flush();

        return $this->json(null, 204);
    }

    private function serialize(Promotion $promotion): array
    {
        return [
            'id' => $promotion->getId(),
            'titre' => $promotion->getTitre(),
            'debut' => $promotion->getDebut()->format('Y-m-d'),
            'fin' => $promotion->getFin()->format('Y-m-d'),
            'produits' => array_map(fn($p) => $p->getId(), $promotion->getProduits()->toArray())
        ];
    }
}
