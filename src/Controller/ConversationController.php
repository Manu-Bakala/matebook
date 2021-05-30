<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/conversations")
 */
class ConversationController extends AbstractController
{   
    public function __construct(UserRepository $userRepository, ConversationRepository $conversationRepository, MessageRepository $messageRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->entityManager = $em;
    }

    /**
     * @Route("/{id}", name="app_new_conversation", methods="POST|GET")
     * @throws \Exception
     */
    public function index(Request $request, int $id, EntityManagerInterface $em)
    {
        
        if(! $this->getUser())
        {
            $this->addFlash('error','Veuillez vous identifier');

            return $this->redirectToRoute('app_login');
        }

        $otherUser = $request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($id);

        if(is_null($otherUser))
        {
            $this->addFlash('error','L\'utilisateur n\'a pas été trouvé');

            return $this->redirectToRoute('app_get_conversations');
        }

        //Cannot create a conversation with myself
        if($otherUser->getId() === $this->getUser()->getId())
        {
            $this->addFlash('error','Impossible de créer une conversation avec vous même');

            return $this->redirectToRoute('app_get_conversations');
        }

        //Check if conversation already exists

        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );

        
        if(count($conversation))
        {
            $this->addFlash('error','La conversation existe déjà');
            
            return $this->redirectToRoute('app_get_conversations');
        }

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        try
        {
            $em->persist($conversation);
            $em->persist($participant);
            $em->persist($otherParticipant); 
            
            $em->flush();

        } catch(\Exception $e){
            throw new \Exception($e);
        }
        
        $this->addFlash('success','Conversation ajoutée avec succes');

        return $this->redirectToRoute('app_show_conversation', ['id' => $conversation->getId()]);
    }

    /**
     * @Route("", name="app_get_conversations", methods="GET")
     */
    public function getConversations(){

        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        $conversations = $this->conversationRepository->findConversationByUser($this->getUser()->getId());
        $messages = $this->messageRepository->findAll();

        return $this->render('conversation/index.html.twig',compact(['conversations', 'messages']));
    }

    /**
     * @Route("/{id<[0-9]+>}/messages", name="app_show_conversation", methods="GET")
     */
    public function showConversation(Conversation $conversation) 
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        $conversations = $this->conversationRepository->findConversationByUser($this->getUser()->getId());
        //dd($conversations[]);

        $messages = $this->messageRepository->findBy([
            'conversation' => $conversation->getId()
        ]);

        return $this->render('conversation/index.html.twig',compact(['conversations', 'conversation', 'messages']));
    }
}
