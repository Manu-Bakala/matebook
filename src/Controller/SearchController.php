<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="app_user_search", methods="GET|POST")
     */
    public function SearchUser(UserRepository $userRepository, Request $request): Response
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }
        
        $form_search = $this->createFormBuilder()
            ->add('search', TextType::class,[
                'attr' => array(
                    'placeholder' => 'Cherchez un ami'
                )
            ])
            ->getForm()
        ;
        
        $form_search->handleRequest($request);

        $user_friends = $this->getUser()->getMyFriends();

        if($form_search->isSubmitted() && $form_search->isValid())
        {
            $value_search = $form_search->getData()['search'];
            $all_users = $userRepository->createQueryBuilder('q')
                ->where('q.firstName LIKE :value_search')
                ->orWhere('q.lastName LIKE :value_search')
                ->setParameter('value_search', '%'.$value_search.'%')
                ->getQuery()
                ->getResult()
            ;

            return $this->render('search/search_user.html.twig', [
                'form_search' => $form_search->createView(),
                'all_users' => $all_users,
                'user_friends' => $user_friends
            ]);

        }

        $all_users = $userRepository->findAll();

        return $this->render('search/search_user.html.twig', [
            'form_search' => $form_search->createView(),
            'all_users' => $all_users,
            'user_friends' => $user_friends
        ]);
    }
}
