<?php

namespace DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Route("/records")
 */
class RecordController extends Controller
{

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction(Request $request)
    {

        $service = $this->container->get('acpaas_api_service');
        $records = $service->getRecords();

        return array('records' => $records);
    }

    /**
     * @Route("/detail/{id}")
     * @Template()
     */
    public function detailAction(Request $request, $id)
    {

        $service = $this->container->get('acpaas_api_service');
        $config = $this->container->getParameter('demo.config');

        $record = $service->getEntity($id);

        $record['creator'] = $record['creator']['user'];
        $record['modifier'] = $record['modifier']['user'];

        $builder = $this->createFormBuilder($record);

        $builder->add('id', 'text', array(
            'read_only' => true));

        $builder->add('current_version', 'text', array(
            'read_only' => true));

        $builder->add('created', 'text', array(
            'read_only' => true));

        $builder->add('modified', 'text', array(
            'read_only' => true));

        $builder->add('creator', 'text', array(
            'read_only' => true));

        $builder->add('modifier', 'text', array(
            'read_only' => true));

        $builder->add('number', 'text', array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 3)),
            )));

        $builder->add('update', 'submit', array('label' => 'Update record'));

        $metadata = $service->getEntityMetadata($record['id']);
        $processInstanceId = $metadata[0]['value'];

        $diagram = $config['activiti_url'].'activiti-rest/service/runtime/process-instances/'.  $processInstanceId . '/diagram';

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $number = $form->get('number')->getData();

            $params = array(
                'number' => $number
            );

            $service->putEntity($id, $params);

            return $this->redirectToRoute('demo_record_detail', array('id' => $id));
        }


        return array('form' => $form->createView(), 'record' => $record,'diagram' => $diagram);

    }

    /**
     * @Route("/create")
     * @Template()
     */
    public function createAction(Request $request)
    {

        $service = $this->container->get('acpaas_api_service');

        $builder = $this->createFormBuilder();

        $builder->add('number', 'text', array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 3)),
            )));

        $builder->add('update', 'submit', array('label' => 'Create record'));


        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $number = $form->get('number')->getData();

            $params = array(
                'number' => $number,
            );

            $record = $service->postRecord($params);

            return $this->redirectToRoute('demo_record_detail', array('id' => $record['id']));
        }


        return array('form' => $form->createView());

    }

    /**
     * @Route("/delete/{id}")
     * @Template()
     */
    public function deleteAction($id)
    {

        $service = $this->container->get('acpaas_api_service');
        $service->deleteEntity($id);

        return $this->redirectToRoute('demo_record_index');

    }

}
