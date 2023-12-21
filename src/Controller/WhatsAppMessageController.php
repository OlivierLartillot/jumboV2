<?php

namespace App\Controller;

use App\Entity\WhatsAppMessage;
use App\Repository\WhatsAppMessageRepository;
use App\Services\DaysConversions;
use App\Services\WhatsApp\TextManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/whats/app/message')]
class WhatsAppMessageController extends AbstractController
{
    #[Route('/', name: 'app_whats_app_message_index', methods: ['GET'])]
    public function index(WhatsAppMessageRepository $whatsAppMessageRepository): Response
    {
        $countWhatsAppMessages = count($whatsAppMessageRepository->findby(['user' => $this->getUser()]));
        $arrivalMessages = $whatsAppMessageRepository->findby(['user' => $this->getUser(), 'typeTransfer' => 1]);
        $interHotelMessages = $whatsAppMessageRepository->findby(['user' => $this->getUser(), 'typeTransfer' => 2]);
        $departureMessages = $whatsAppMessageRepository->findby(['user' => $this->getUser(), 'typeTransfer' => 3]);



        return $this->render('whats_app//index.html.twig', [
            'arrivalMessages' => $arrivalMessages,
            'interHotelMessages' => $interHotelMessages,
            'departureMessages' => $departureMessages,
            'countWhatsAppMessages' => $countWhatsAppMessages
        ]);
    }

    #[Route('/{transfer}/new', name: 'app_whats_app_message_new', methods: ['GET', 'POST'])]
    public function new($transfer, Request $request, WhatsAppMessageRepository $whatsAppMessageRepository, EntityManagerInterface $entityManager): Response
    {

        $whatsAppMessage = new WhatsAppMessage();

        if ($request->get('submit') !== null  ) {
            $language = $request->get('language');
           
            $text = $request->get('textArea');
            // si tu essaies d'envoyer la meme langue pour ce whatsapp qui existe deja, tu envoie une erreur
            $isLangueExiste = $whatsAppMessageRepository->findOneBy([
                'user' => $this->getUser(),
                'typeTransfer' => $transfer,
                'language' => $language
            ]);
            if ($isLangueExiste) {
                $this->addFlash(
                    'danger',
                    'Tu ne peux pas enregistrer deux messages dans la même langue'
                );
                //dd('on renvoie flash error + le texte');
                return $this->render('whats_app/create_arrival_messages.html.twig', [
                          
                            'text' => $text, 
                            'language' => $language
                        ]);

            }
            
            $whatsAppMessage->setUser($this->getUser());
            $whatsAppMessage->setTypeTransfer($transfer);
            $whatsAppMessage->setLanguage($language);

            $text = str_replace("\r\n","<br>", $text);
            $text = str_replace("<script>","forbidden tag", $text);
            $text = str_replace("</script>","forbidden tag", $text);
            $text = str_replace("<input","forbidden tag", $text);

            $whatsAppMessage->setMessage($text);
   
            $entityManager->persist($whatsAppMessage);
            $entityManager->flush();

            //renvoyer vers Edit
            return $this->redirectToRoute('app_whats_app_message_edit', [
                'id' => $whatsAppMessage->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('whats_app/create_arrival_messages.html.twig', []);
    }

    #[Route('/{id}', name: 'app_whats_app_message_show', methods: ['GET'])]
    public function show(WhatsAppMessage $whatsAppMessage): Response
    {
        return $this->render('whats_app_message/show.html.twig', [
            'whats_app_message' => $whatsAppMessage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_whats_app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WhatsAppMessage $whatsAppMessage, TextManager $textManager, EntityManagerInterface $entityManager): Response
    {

        // texte a  afficher dans le texteArea
        $textForArea = str_replace("<br>","\r\n", $whatsAppMessage->getMessage());
        // texte a afficher dans la div, Attention dans le deuxieme il faut utiliser la nouvelle valeur => $textForDiv !!! 
        $textForDiv = $textManager->replaceVariables($whatsAppMessage->getMessage(), $textManager->getExampleVariables());
        $textForDiv = $textManager->replaceTags($textForDiv, $textManager->getConvertSmileys());

        if ($request->get('submit') !== null  ) {
            // set uniquement le texte
            $text = $request->get('textArea');
            $text = $textManager->replaceTags($text, $textManager->getConvertToPhp());
            $whatsAppMessage->setMessage($text);
            $entityManager->persist($whatsAppMessage);
            $entityManager->flush();
            return $this->redirectToRoute('app_whats_app_message_edit', [
                'id' => $whatsAppMessage->getId(),
                'whatsAppMessage' => $whatsAppMessage,
                'textForArea' => $textForArea,
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('whats_app/edit_arrival_messages.html.twig', [
            'whatsAppMessage' => $whatsAppMessage,
            'textForDiv' => $textForDiv,
            'textForArea' => $textForArea,
        ]);
    }

    #[Route('/{id}', name: 'app_whats_app_message_delete', methods: ['POST'])]
    public function delete(Request $request, WhatsAppMessage $whatsAppMessage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$whatsAppMessage->getId(), $request->request->get('_token'))) {
            $entityManager->remove($whatsAppMessage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_whats_app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}
