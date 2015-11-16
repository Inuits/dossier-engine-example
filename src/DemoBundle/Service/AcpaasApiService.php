<?php

namespace DemoBundle\Service;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Service\Client;
use Symfony\Component\HttpFoundation\Session\Session;

class AcpaasApiService
{

    private $config;
    private $session;
    private $client;
    private $userService;

    public function __construct(array $config, Session $session, Client $client, UserService $userService)
    {
        $this->config = $config;
        $this->session = $session;
        $this->userService = $userService;
        $this->client = $client;
        $this->client->setBaseUrl($this->config['client_url']);
    }

    private function get($url, $queryParams = array())
    {

        try {
            $request = $this->client->get($url . '?' . http_build_query($queryParams));
            $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
            $response = $request->send();

            return $response->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }


    }

    private function post($url, $queryParams = array(), $bodyParams = array())
    {


        try {
            $request = $this->client->post($url . '?' . http_build_query($queryParams), array(), $bodyParams);
            $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
            return $request->send()->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function put($url, $queryParams = array(), $bodyParams = array())
    {

        try {
            $request = $this->client->put($url . '?' . http_build_query($queryParams), array(), $bodyParams);
            $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
            return $request->send()->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function delete($url, $queryParams = array())
    {

        try {
            $request = $this->client->delete($url . '?' . http_build_query($queryParams));
            $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
            return $request->send()->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function getAccessToken()
    {

        $now = new \DateTime();

        $acces_token_expires_in = $this->session->get('access_token_expires_in', $now->getTimestamp());


        if ($now->getTimestamp() >= $acces_token_expires_in) {

            $params = array(
                'client_id' => $this->config['public_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type' => 'client_credentials'
            );

            $request = $this->client->get('/oauth/v2/token?' . http_build_query($params));
            $result = $request->send()->json();

            $expires_in = $now->add(new \DateInterval('PT' . $result['expires_in'] . 'S'));
            $this->session->set('access_token_expires_in', $expires_in->getTimestamp());
            $this->session->set('access_token', $result['access_token']);

        }

        return $this->session->get('access_token');

    }

    public function postRecord($bodyParams)
    {

        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general',
        );

        $record = $this->post('/api/v1/entities/Record', $queryParams, $bodyParams);

        $bodyParams = array(
            'resource' => 'group',
            'operation' => 'view',
            'name' => 'general',
        );

        $this->postEntityAcl($record['id'], $bodyParams);

        $bodyParams = array(
            'resource' => 'group',
            'operation' => 'delete',
            'name' => 'general',
        );

        $this->postEntityAcl($record['id'], $bodyParams);

        $bodyParams = array(
            'resource' => 'group',
            'operation' => 'update',
            'name' => 'general',
        );

        $this->postEntityAcl($record['id'], $bodyParams);

        return $record;

    }

    public function getRecords()
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general',
            'type' => 'Record',
            'limit' => 99,
        );

        return $this->get('/api/v1/entities', $queryParams)['results'];
    }

    public function getEntity($id)
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general'
        );

        return $this->get('/api/v1/entities/' . $id, $queryParams);
    }

    public function putEntity($id, $bodyParams)
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general',
        );

        return $this->put('/api/v1/entities/' . $id, $queryParams, $bodyParams);
    }

    public function postEntityAcl($id, $bodyParams)
    {
        $this->post('/api/v1/entities/' . $id . '/acl', array(), $bodyParams);
    }

    public function deleteEntity($id)
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general',
        );

        return $this->delete('/api/v1/entities/' . $id, $queryParams);
    }

    public function getMetadataSchemas()
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general'
        );

        return $this->get('/api/v1/metadataSchema', $queryParams)['results'];
    }

    public function getEntityMetadata($id)
    {
        return $this->get('/api/v1/entities/' . $id . '/metadata');
    }

    public function getTasks()
    {
        $queryParams['assignee'] = $this->userService->getUser();
        return $this->get('/api/v1/activiti/runtime/tasks', $queryParams);

    }

    public function getTask($id)
    {
        return $this->get('/api/v1/activiti/runtime/tasks/' . $id);
    }

    public function putTask($id, $params)
    {
        return $this->put('/api/v1/activiti/runtime/tasks/' . $id, array(), $params);
    }

    public function postCompleteTask($id)
    {

        $params = array(
            'action' => 'complete'
        );

        return $this->post('/api/v1/activiti/runtime/tasks/' . $id, array(), $params);
    }

    public function getDiagram($id)
    {

        return $this->get('/api/v1/activiti/runtime/process-instances/' . $id . '/diagram');
    }


}