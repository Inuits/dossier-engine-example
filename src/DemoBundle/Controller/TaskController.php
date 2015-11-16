<?php

namespace DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Route("/tasks")
 */
class TaskController extends Controller
{

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $service = $this->container->get('acpaas_api_service');
        $tasks = $service->getTasks()['data'];

        return array('tasks' => $tasks);
    }

    /**
     * @Route("/detail/{id}")
     * @Template()
     */
    public function detailAction(Request $request, $id)
    {

        $service = $this->container->get('acpaas_api_service');
        $task = $service->getTask($id);

        $builder = $this->createFormBuilder($task);

        $builder->add('id', 'text', array(
            'read_only' => true));

        $builder->add('name', 'text', array(
            'read_only' => true));

        $builder->add('description', 'textarea');

        $builder->add('createTime', 'text', array(
            'read_only' => true));

        $builder->add('priority', 'text');

        $builder->add('assignee', 'text');

        $builder->add('update', 'submit', array('label' => 'Update task'));

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $params = array(
                'assignee' => $data['assignee'],
                'description' => $data['description'],
                'priority' => $data['priority'],
            );

            $service = $this->container->get('acpaas_api_service');
            $service->putTask($data['id'], $params);

            return $this->redirectToRoute('demo_task_detail', array('id' => $data['id']));
        }


        return array('form' => $form->createView(), 'task' => $task);
    }

    /**
     * @Route("/completeTask/{id}")
     * @Template()
     */
    public function completeTaskAction(Request $request, $id)
    {

        $service = $this->container->get('acpaas_api_service');

        $service->postCompleteTask($id);

        $this->addFlash('notice', 'task completed');

        return $this->redirectToRoute('demo_task_index');
    }


}
