<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("", name="app_admin_index")
     */
    public function index(): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Ooops Nope!');

        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/posts", name="app_admin_post_index")
     */
    public function postsIndex(): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/posts_index.html.twig');
    }
}
