<?php

namespace DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('demo_task_index');

    }
}
