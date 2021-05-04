<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods="GET")
     */
    public function index(PostRepository $postRepository): Response
    {
        if(! $this->getUser())
        {
            //$this->addFlash('error','You need to log in first');
            return $this->redirectToRoute('app_login');
        }

        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('posts/index.html.twig', compact('posts'));
    }
    
    /**
     * @Route("/post/create", name="app_post_create", methods="GET|POST")
     */
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        if(! $this->getUser())
        {
            $this->addFlash('error','You need to log in first');
            return $this->redirectToRoute('app_login');
        }

        if(! $this->getUser()->isVerified())
        {
            $this->addFlash('error','You need to have a verified account!');
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

            $this->addFlash('success','Post successfully created');

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
            $this->addFlash('error','You need to log in first');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('posts/show.html.twig', compact('post'));
    }

    /**
     * @Route("/post/{id<[0-9]+>}/edit", name="app_post_edit", methods="GET|PUT")
     */
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if(! $this->getUser())
        {
            $this->addFlash('error','You need to log in first');
            return $this->redirectToRoute('app_login');
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
        }

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
        if(! $this->getUser())
        {
            $this->addFlash('error','You need to log in first');
            return $this->redirectToRoute('app_login');
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
        }

        if($this->isCsrfTokenValid('post_delete_'.$post->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($post);
            $em->flush();

            $this->addFlash('info','Post successfully deleted');
        }

        return $this->redirectToRoute('app_home');
    }
}
