<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PostsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods="GET")
     */
    public function index(PostRepository $postRepository): Response
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        $posts_user_online = $postRepository->findBy(
            ['user' => $this->getUser()->getId()],
            ['createdAt' => 'DESC'],
            $limit = null,
            $offset = null);

        return $this->render('posts/index.html.twig', compact(['posts','posts_user_online']));
    }
    
    /**
     * @Route("/post/create", name="app_post_create", methods="GET|POST")
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {

        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        if(! $this->getUser()->isVerified())
        {
            $this->addFlash('error','Pour créer une publication, votre compte doit être vérifié via le mail que nous vous avons envoyé!');
            return $this->redirectToRoute('app_home');
        }

        $post = new Post;
        
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $post->setUser($this->getUser());
            $em->persist($post);
            $em->flush();

            $this->addFlash('success','Publication créée avec succes');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('posts/create.html.twig', [
            'form_post' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id<[0-9]+>}", name="app_post_show", methods="GET")
     */
    public function show(Post $post, PostRepository $postRepository): Response
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        $user = $post->getUser();

        $posts_user = $postRepository->findBy(['user' => $user->getId()]);
        $user_friends = $this->getUser()->getMyFriends();

        return $this->render('posts/show.html.twig', compact('post','posts_user', 'user_friends'));
    }

    /**
     * @Route("/posts/{id<[0-9]+>}", name="app_post_user_show", methods="GET|POST")
     */
    public function show_posts_user(Request $request, User $user, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        if($request->request->has('follow_user'))
        {
            $this->getUser()->addFriend($user);
            $em->persist($this->getUser());
            $em->flush();

            $this->addFlash('success','Vous êtes maintenant abonné a '.$user->getFullName());
        }

        if($request->request->has('unfollow_user'))
        {
            $this->getUser()->removeFriend($user);
            $em->persist($this->getUser());
            $em->flush();

            $this->addFlash('success','Vous êtes maintenant désabonné de '.$user->getFullName());
        }

        $posts = $postRepository->findBy(['user' => $user->getId()], ['createdAt' => 'DESC']);

        return $this->render('posts/show_posts_user.html.twig', compact(['posts','user']));
    }

    /**
     * @Route("/post/{id<[0-9]+>}/edit", name="app_post_edit", methods="GET|PUT")
     */
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        if(! $this->getUser()->isVerified())
        {
            $this->addFlash('error','Pour modifier votre publication, votre compte doit être vérifié via le mail que nous vous avons envoyé!');
            return $this->redirectToRoute('app_home');
        }

        if($post->getUser() != $this->getUser())
        {
            $this->addFlash('error','Impossible de modifier une publication qui ne vous appartient pas');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(PostType::class, $post, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->flush();

            $this->addFlash('success','publication modifiée avec succes');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('posts/edit.html.twig',[
            'post' => $post,
            'form_post' => $form->createView()
        ]);
    }

     /**
     * @Route("/post/{id<[0-9]+>}/delete", name="app_post_delete", methods="DELETE")
     */
    public function delete(Request $request, Post $post, EntityManagerInterface $em): Response
    {        
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        if(! $this->getUser()->isVerified())
        {
            $this->addFlash('error','Votre compte n\'est pas vérifié! Consultez vos email');
            return $this->redirectToRoute('app_home');
        }

        if($post->getUser() != $this->getUser())
        {
            $this->addFlash('error','Vous ne pouvez pas supprimer la publication d\'un autre utilisateur');
            return $this->redirectToRoute('app_home');
        }

        if($this->isCsrfTokenValid('post_delete_'.$post->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($post);
            $em->flush();

            $this->addFlash('info','Publication supprimée avec succes');
        }

        return $this->redirectToRoute('app_home');
    }
}
