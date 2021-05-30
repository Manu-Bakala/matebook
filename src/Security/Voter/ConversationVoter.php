<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ConversationVoter extends Voter
{
    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }
    
    const VIEW = 'view';
    
    protected function supports($attribute, $subject)
    {
        return $attribute == self::VIEW && $subject instanceof Conversation;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $result = $this->conversationRepository->checkIfUserIsParticipant(
            $subject->getId(),
            $token->getUser()->getId()
        );
        return !!$result;
    }
}
