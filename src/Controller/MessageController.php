<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/messages")
 */
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALIZE = ['id', 'content', 'createAt', 'mine'];

    public function __construct(EntityManagerInterface $em, MessageRepository $messageRepository, ConversationRepository $conversationRepository)
    {
        $this->em = $em;
        $this->messageRepository = $messageRepository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/{id}", name="app_get_messages", methods="GET")
     */
    public function index(Request $request, Conversation $conversation)
    {
        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->findMessageByConversationId(
            $conversation->getId()
        );

        if(!empty($messages))
        {
            /**
             * @var $message Message
             */
            array_map(function($message){
                return $message->setMine(
                    $message->getUser()->getId() === $this->getUser()->getId() 
                    ? true: false
                );
            }, $messages);
        }
        
        return $this->json($messages, Response::HTTP_OK,[], [
            'attributes' => self::ATTRIBUTES_TO_SERIALIZE
        ]);
    }

     /**
     * @Route("/{id<[0-9]+>}/messages", name="app_new_messages", methods="POST")
     */
    public function newMessage(Request $request, Conversation $conversation)
    {
        if(! $this->getUser())
        {
            $this->addFlash('error','Veuillez vous identifier');

            return $this->redirectToRoute('app_login');
        }
        
        $user = $this->getUser();

        $content = $request->get('message_to_add', null);

        $message = new Message();
        $message->setContent($content);
        $message->setUser($user);
        $message->setMine(true);

        $conversation->addMessage($message);
        $conversation->setLastMessage($message);

        try
        {
            $this->em->persist($message);
            $this->em->persist($conversation);
            $this->em->flush();

        } 
        catch(\Exception $e)
        {
            throw new \Exception("Error message"); 
        }

        $conversations = $this->conversationRepository->findConversationByUser($this->getUser()->getId());
        $messages = $this->messageRepository->findBy([
            'conversation' => $conversation->getId()
        ]);
        
        //return $this->render('conversation/index.html.twig',compact(['conversations', 'conversation', 'messages']));
        
        return $this->redirectToRoute('app_show_conversation', ['id' => $conversation->getId()]);

    }
}
