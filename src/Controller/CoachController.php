<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Form\CoachType;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coach')]
final class CoachController extends AbstractController
{
    #[Route(name: 'app_coach_index', methods: ['GET'])]
    public function index(CoachRepository $coachRepository): Response
    {
        return $this->render('coach/index.html.twig', [
            'coaches' => $coachRepository->findAll(),
        ]);
    }
    #[Route('/manualDelete', name: 'exist_error', methods: ['GET'])]
    public function errorMsg(CoachRepository $coachRepository): Response
    {
        return $this->render('coach/manualDelete.html.twig');
    }

    #[Route('/new', name: 'app_coach_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CoachRepository $coachRepository): Response
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request); 
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Check if a coach with the same cin already exists
            $existingCoach = $coachRepository->findOneBy(['cin' => $coach->getCin()]);
    
            if ($existingCoach) {
                // Add an error message if the coach already exists
                return $this->redirectToRoute('exist_error', [], Response::HTTP_SEE_OTHER);
            } else {
                // If no duplicate found, persist the new coach
                $entityManager->persist($coach);
                $entityManager->flush();
    
                return $this->redirectToRoute('app_coach_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('coach/new.html.twig', [
            'coach' => $coach,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coach_show', methods: ['GET'])]
    public function show(Coach $coach): Response
    {
        return $this->render('coach/show.html.twig', [
            'coach' => $coach,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_coach_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_coach_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('coach/edit.html.twig', [
            'coach' => $coach,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coach_delete', methods: ['POST'])]
    public function delete(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coach->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($coach);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_coach_index', [], Response::HTTP_SEE_OTHER);
    } 
}
