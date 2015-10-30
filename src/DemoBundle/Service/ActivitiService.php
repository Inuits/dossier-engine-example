<?php

namespace DemoBundle\Service;

use ERMS\CoreBundle\Provider\EntityTypeProviderInterface;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Symfony\Component\HttpFoundation\Session\Session;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Service\Client;

class ActivitiService
{

    private $config;
    private $client;
    private $userService;

    public function __construct(array $config, Client $client, UserService $userService)
    {
        $this->config = $config;
        $this->client = $client;
        $this->client->setBaseUrl($this->config['activiti_url']);
        $this->userService = $userService;
    }

    private function get($url)
    {

        $params = array();

        $request = $this->client->get($url, $params);
        $request->setAuth($this->config['activiti_user'], $this->config['activiti_password']);
        $response = $request->send();

        return $response->json();
    }

    private function post($url, $params)
    {

        $request = $this->client->post($url, array(
            'content-type' => 'application/json'
        ), array());
        $request->setBody(json_encode($params));


        $request->setAuth($this->config['activiti_user'], $this->config['activiti_password']);
        $response = $request->send();

        return $response->json();
    }

    private function put($url, $params)
    {

        $request = $this->client->put($url, array(
            'content-type' => 'application/json'
        ), array());

        $request->setBody(json_encode($params));

        $request->setAuth($this->config['activiti_user'], $this->config['activiti_password']);
        $response = $request->send();

        return $response->json();
    }


    public function getTasks()
    {
        return $this->get('/activiti-rest/service/runtime/tasks?assignee=' . $this->userService->getUser())['data'];

    }

    public function getTask($id)
    {
        return $this->get('/activiti-rest/service/runtime/tasks/' . $id);
    }

    public function putTask($id, $params)
    {
        return $this->put('/activiti-rest/service/runtime/tasks/' . $id, $params);
    }

}
