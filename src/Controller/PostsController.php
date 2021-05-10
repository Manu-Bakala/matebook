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
     * @Security("is_granted('ROLE_USER') && user.isVerified()")
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
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
    public function show(Post $post): Response
    {
        if(! $this->getUser())
        {
            throw $this->createAccessDeniedException('Not connected to create post');
        }

        return $this->render('posts/show.html.twig', compact('post'));
    }

    /**
     * @Route("/posts/{id<[0-9]+>}", name="app_post_user_show", methods="GET")
     */
    public function show_posts_user(User $user, PostRepository $postRepository): Response
    {
        if(! $this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        $posts = $postRepository->findBy(['user' => $user->getId()], ['createdAt' => 'DESC']);

        return $this->render('posts/show_posts_user.html.twig', compact(['posts','user']));
    }

    /**
     * @Route("/post/{id<[0-9]+>}/edit", name="app_post_edit", methods="GET|PUT")
     * @IsGranted("POST_MANAGE", subject="post")
     */
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        /*if(! $this->getUser())
        {
            throw $this->createAccessDeniedException('Not connected to create post');
        }

        if(! $this->getUser()->isVerified())
        {
            $this->addFlash('error','You need to have a verified account!');
            return $this->redirectToRoute('app_home');
        }

        if($post->getUser() != $this->getUser())
        {
            $this->addFlash('error','Access Forbidden');
            return $this->redirectToRoute('app_home');
        }*/

        $form = $this->createForm(PostType::class, $post, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->flush();

            $this->addFlash('success','Post successfully updated');

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
        $this->denyAccessUnlessGranted('POST_MANAGE', $post);
        
        /*if(! $this->getUser())
        {
            throw $this->createAccessDeniedException('Not connected to create post');
        }

        if(! $this->getUser()->isVerified())
        {
            $this->addFlash('error','You need to have a verified account!');
            return $this->redirectToRoute('app_home');
        }

        if($post->getUser() != $this->getUser())
        {
            $this->addFlash('error','Access Forbidden');
            return $this->redirectToRoute('app_home');
        }*/

        if($this->isCsrfTokenValid('post_delete_'.$post->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($post);
            $em->flush();

            $this->addFlash('info','Publication supprimée avec succes');
        }

        return $this->redirectToRoute('app_home');
    }
}
