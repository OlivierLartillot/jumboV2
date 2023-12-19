<?php

namespace App\Controller;

use App\Entity\WhatsAppMessage;
use App\Repository\WhatsAppMessageRepository;
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
        return $this->render('whats_app//index.html.twig', [
            'whats_app_messages' => $whatsAppMessageRepository->findAll(),
        ]);
    }

    #[Route('/{transfer}/new', name: 'app_whats_app_message_new', methods: ['GET', 'POST'])]
    public function new($transfer, Request $request, WhatsAppMessageRepository $whatsAppMessageRepository, EntityManagerInterface $entityManager): Response
    {

        $whatsAppMessage = new WhatsAppMessage();
        $zoneDefault = true;
        // est ce qu il existe deja un message pour ce $treansfer et user
        $messageExist = count($whatsAppMessageRepository->findBy([
            'user' => $this->getUser(),
            'typeTransfer' => $transfer
        ]));


        // s il n y a pas de message , on va virer la zone default pour obliger la nouvelle a etre true
        if ($messageExist == false) {
            $zoneDefault = false;
        }

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
                            'zoneDefault' => $zoneDefault,
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
            // si il n y a pas de message il sera a default
            if ($messageExist == false) {
                $whatsAppMessage->setIsDefaultMessage(true);
            } else {
               
                $isDefault = $request->get('default') == 'yes' ? true : false ;
                //si on le met en tant que default il faut virer l'ancien
                if ($isDefault) {
                     // on recupere le message par default
                    $messageWithDefault = $whatsAppMessageRepository->findOneBy([
                        'user' => $this->getUser(),
                        'typeTransfer' => $transfer,
                        'isDefaultMessage' => true
                    ]); 
                    // et si on le trouve il n est plus par defaut !
                    ($messageWithDefault) ? $messageWithDefault->setIsDefaultMessage(false) : "";
                    // et on met le nouveau en default !
                    $whatsAppMessage->setIsDefaultMessage(true);
                } else {
                    $whatsAppMessage->setIsDefaultMessage(false);
                }
            }
            
            $entityManager->persist($whatsAppMessage);
            $entityManager->flush();

            //renvoyer vers Edit
            return $this->redirectToRoute('app_whats_app_message_edit', [
                'id' => $whatsAppMessage->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('whats_app/create_arrival_messages.html.twig', [
            'zoneDefault' => $zoneDefault
        ]);
    }

    #[Route('/{id}', name: 'app_whats_app_message_show', methods: ['GET'])]
    public function show(WhatsAppMessage $whatsAppMessage): Response
    {
        return $this->render('whats_app_message/show.html.twig', [
            'whats_app_message' => $whatsAppMessage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_whats_app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WhatsAppMessage $whatsAppMessage, WhatsAppMessageRepository $whatsAppMessageRepository, EntityManagerInterface $entityManager): Response
    {

        $textForArea = str_replace("<br>","\r\n", $whatsAppMessage->getMessage());
        
        // est ce qu on affiche la zone pour choisir isDefault ?
        $zoneDefault = ($whatsAppMessage->isIsDefaultMessage()) ? false : true;



        if ($request->get('submit') !== null  ) {


            // set uniquement le texte
            $text = $request->get('textArea');
            $text = str_replace("\r\n","<br>", $text);
            $text = str_replace("<script>","forbidden tag", $text);
            $text = str_replace("</script>","forbidden tag", $text);
            $text = str_replace("<input","forbidden tag", $text);
            $whatsAppMessage->setMessage($text);

            // si tu ajoutes default, tu regardes si un autre été a default
            // si oui tu vires defaut pour l autre et tu mets le courant a default
            if ($request->get('default') != null) {

                $isDefault = $request->get('default') == 'yes' ? true : false ;
                    //si on le met en tant que default il faut virer l'ancien
                    if ($isDefault) {
                         // on recupere le message par default
                        $messageWithDefault = $whatsAppMessageRepository->findOneBy([
                            'user' => $this->getUser(),
                            'typeTransfer' => $whatsAppMessage->getTypeTransfer(),
                            'isDefaultMessage' => true
                        ]); 
                        // et si on le trouve il n est plus par defaut !
                        ($messageWithDefault) ? $messageWithDefault->setIsDefaultMessage(false) : "";
                        // et on met le nouveau en default !
                        $whatsAppMessage->setIsDefaultMessage(true);
                    } else {
                        $whatsAppMessage->setIsDefaultMessage(false);
                    }
            }
            $entityManager->persist($whatsAppMessage);
            $entityManager->flush();
            //dd($whatsAppMessage);
            return $this->redirectToRoute('app_whats_app_message_edit', [
                'id' => $whatsAppMessage->getId(),
                'whatsAppMessage' => $whatsAppMessage,
                'textForArea' => $textForArea,
                'zoneDefault' => $zoneDefault
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('whats_app/edit_arrival_messages.html.twig', [
            'whatsAppMessage' => $whatsAppMessage,
            'textForArea' => $textForArea,
            'zoneDefault' => $zoneDefault
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
