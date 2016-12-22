<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig', [
            'items' => $this->get('app.filesystem.article_repository')->getHomeItems(),
        ]);
    }

    /**
     * @Route("/article/{slug}", name="article_view")
     * @Method("GET")
     */
    public function articleAction($slug)
    {
        return $this->render('home/index.html.twig', [
            'article' => $this->get('app.filesystem.article_repository')->getArticle($slug),
        ]);
    }
}
