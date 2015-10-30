<?php

namespace DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/*
 * @Route("/users")
 */
class UserController extends Controller
{

    /**
     * @Route("/switch/{user}")
     * @Template()
     */
    public function switchAction(Request $request, $user)
    {

        $service = $this->container->get('user_service');
        $user = $service->setUser($user);

        $this->addFlash('notice',"Switched to user $user.");

        return $this->redirectToRoute('demo_task_index');
    }

}