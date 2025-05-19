<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Entity\Client;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/commandes')]
class CommandeController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    // LISTER TOUTES LES COMMANDES
    #[Route('/', name: 'commande_index', methods: ['GET'])]
    public function index(CommandeRepository $repo): JsonResponse
    {
        $commandes = $repo->findAll();
        return $this->json([
            'data' => array_map([$this, 'serializeCommande'], $commandes)
        ]);
    }

    // AFFICHER UNE COMMANDE
    #[Route('/{id}', name: 'commande_show', methods: ['GET'])]
    public function show(Commande $commande): JsonResponse
    {
        return $this->json([
            'data' => $this->serializeCommande($commande)
        ]);
    }

    // CRÉER UNE COMMANDE
    #[Route('/', name: 'commande_create', methods: ['POST'])]
    public function create(
        Request $request,
        ClientRepository $clientRepo,
        ProduitRepository $produitRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validation du client
        $client = $clientRepo->find($data['clientId'] ?? 0);
        if (!$client) {
            return $this->json(['error' => 'Client introuvable'], 404);
        }

        // Création de la commande
        $commande = new Commande();
        $commande->setClient($client)
            ->setDateCommande(new \DateTime())
            ->setReference($this->generateReference())
            ->setAdresse($client->getAdresse());

        // Gestion des lignes de commande
        $errors = $this->processLignesCommande($commande, $data['lignes'] ?? [], $produitRepo);
        if ($errors) {
            return $errors;
        }

        // Validation et persistance
        $errors = $this->validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($commande);
        $this->em->flush();

        return $this->json([
            'message' => 'Commande créée',
            'data' => $this->serializeCommande($commande)
        ], 201);
    }

    // METTRE À JOUR UNE COMMANDE
    #[Route('/{id}', name: 'commande_update', methods: ['PUT'])]
    public function update(
        Commande $commande,
        Request $request,
        ProduitRepository $produitRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Mise à jour des champs de base
        if (isset($data['codeLivraison'])) {
            $commande->setCodeLivraison($data['codeLivraison']);
        }

        // Mise à jour des lignes de commande
        if (isset($data['lignes'])) {
            $this->clearLignesCommande($commande);
            $errors = $this->processLignesCommande($commande, $data['lignes'], $produitRepo);
            if ($errors) {
                return $errors;
            }
        }

        // Validation et sauvegarde
        $errors = $this->validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();

        return $this->json([
            'message' => 'Commande mise à jour',
            'data' => $this->serializeCommande($commande)
        ]);
    }

    // SUPPRIMER UNE COMMANDE
    #[Route('/{id}', name: 'commande_delete', methods: ['DELETE'])]
    public function delete(Commande $commande): JsonResponse
    {
        $this->em->remove($commande);
        $this->em->flush();

        return $this->json(null, 204);
    }

    // Méthodes utilitaires

    private function processLignesCommande(Commande $commande, array $lignes, ProduitRepository $produitRepo): ?JsonResponse
    {
        foreach ($lignes as $ligneData) {
            // Validation des données
            $required = ['produitId', 'quantite', 'prixUnitaire'];
            foreach ($required as $field) {
                if (!isset($ligneData[$field])) {
                    return $this->json(['error' => "Champ $field manquant pour la ligne de commande"], 400);
                }
            }

            // Vérification du produit
            $produit = $produitRepo->find($ligneData['produitId']);
            if (!$produit) {
                return $this->json(['error' => 'Produit introuvable pour une ligne de commande'], 404);
            }

            // Création de la ligne
            $ligne = new LigneCommande();
            $ligne->setProduit($produit)
                ->setQuantite($ligneData['quantite'])
                ->setPrixUnitaire($ligneData['prixUnitaire']);

            $commande->addLigneCommande($ligne);

            // Validation
            $errors = $this->validator->validate($ligne);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], 400);
            }
        }
        return null;
    }

    private function clearLignesCommande(Commande $commande): void
    {
        foreach ($commande->getLigneCommandes() as $ligne) {
            $this->em->remove($ligne);
        }
        $commande->getLigneCommandes()->clear();
    }

    private function generateReference(): string
    {
        return 'CMD-' . date('Ymd-His') . '-' . substr(uniqid(), -6);
    }

    private function serializeCommande(Commande $commande): array
    {
        return [
            'id' => $commande->getId(),
            'reference' => $commande->getReference(),
            'dateCommande' => $commande->getDateCommande()?->format('Y-m-d H:i:s'),
            'client' => $commande->getClient()?->getId(),
            'adresse' => $commande->getAdresse()?->getId(),
            'lignes' => array_map(function(LigneCommande $ligne) {
                return [
                    'produit' => $ligne->getProduit()?->getId(),
                    'quantite' => $ligne->getQuantite(),
                    'prixUnitaire' => $ligne->getPrixUnitaire()
                ];
            }, $commande->getLigneCommandes()->toArray())
        ];
    }
}