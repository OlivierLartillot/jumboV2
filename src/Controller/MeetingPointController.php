<?php

namespace App\Controller;

use App\Entity\MeetingPoint;
use App\Form\MeetingPointType;
use App\Repository\MeetingPointRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/meeting/point')]
class MeetingPointController extends AbstractController
{
    #[Route('/', name: 'app_meeting_point_index', methods: ['GET'])]
    public function index(MeetingPointRepository $meetingPointRepository): Response
    {
        return $this->render('meeting_point/index.html.twig', [
            'meeting_points' => $meetingPointRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_meeting_point_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MeetingPointRepository $meetingPointRepository): Response
    {
        $meetingPoint = new MeetingPoint();
        $form = $this->createForm(MeetingPointType::class, $meetingPoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meetingPointRepository->save($meetingPoint, true);

            return $this->redirectToRoute('app_meeting_point_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('meeting_point/new.html.twig', [
            'meeting_point' => $meetingPoint,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meeting_point_show', methods: ['GET'])]
    public function show(MeetingPoint $meetingPoint): Response
    {
        return $this->render('meeting_point/show.html.twig', [
            'meeting_point' => $meetingPoint,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_meeting_point_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MeetingPoint $meetingPoint, MeetingPointRepository $meetingPointRepository): Response
    {
        $form = $this->createForm(MeetingPointType::class, $meetingPoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meetingPointRepository->save($meetingPoint, true);

            return $this->redirectToRoute('app_meeting_point_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('meeting_point/edit.html.twig', [
            'meeting_point' => $meetingPoint,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_meeting_point_delete', methods: ['POST'])]
    public function delete(Request $request, MeetingPoint $meetingPoint, MeetingPointRepository $meetingPointRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$meetingPoint->getId(), $request->request->get('_token'))) {
            $meetingPointRepository->remove($meetingPoint, true);
        }

        return $this->redirectToRoute('app_meeting_point_index', [], Response::HTTP_SEE_OTHER);
    }
}
