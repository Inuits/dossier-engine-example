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
    private $clientAuth;
    private $oauthPath;
    private $apiPath;
    private $apiKey;
    private $apiKeyType;
    private $apiKeyName;

    public function __construct(array $config, Session $session, Client $client, UserService $userService)
    {
        $this->config = $config;
        $this->session = $session;
        $this->userService = $userService;
        $this->clientAuth = $this->config['client_auth'];
        $this->client = $client;
        $this->client->setBaseUrl($this->config['base_url']);
        $this->oauthPath = $this->config['oauth_path'];
        $this->apiPath = $this->config['api_path'];
        $this->apiKey = $this->config['api_key'];
        $this->apiKeyType = $this->config['api_key_type'];
        $this->apiKeyName = $this->config['api_key_name'];
    }

    private function get($url, $queryParams = array())
    {

        try {
            $this->addAuthParams($queryParams);
            $request = $this->client->get($url . '?' . http_build_query($queryParams));
            $this->addAuthHeaders();
            $response = $request->send();

            return $response->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function post($url, $queryParams = array(), $bodyParams = array())
    {

        try {
            $this->addAuthParams($queryParams);
            $request = $this->client->post($url . '?' . http_build_query($queryParams), array(), $bodyParams);
            $this->addAuthHeaders();
            return $request->send()->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function put($url, $queryParams = array(), $bodyParams = array())
    {

        try {
            $this->addAuthParams($queryParams);
            $request = $this->client->put($url . '?' . http_build_query($queryParams), array(), $bodyParams);
            $this->addAuthHeaders();
            return $request->send()->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function delete($url, $queryParams = array())
    {

        try {
            $this->addAuthParams($queryParams);
            $request = $this->client->delete($url . '?' . http_build_query($queryParams));
            $this->addAuthHeaders();
            return $request->send()->json();

        } catch (RequestException $ex) {
            throw new \Exception($ex->getResponse()->getBody());
        }

    }

    private function addAuthHeaders($request)
    {
        if ($this->clientAuth == 'oauth')
        {
            $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
        }
        else
        {
            if ($this->apiKeyType === 'header')
            {
                $request->addHeader($this->apiKeyName, $this->apiKey);
            }
        }
    }

    private function addAuthParams(&$queryParams)
    {
        if ($this->clientAuth == 'apikey' && $this->apiKeyType == 'param')
        {
            $queryParams[$this->apiKeyName] = $this->apiKey;
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

            $request = $this->client->get($this->oauthPath . '/token?' . http_build_query($params));
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

        $record = $this->post($this->apiPath . '/entities/Record', $queryParams, $bodyParams);

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

        return $this->get($this->apiPath . '/entities', $queryParams)['results'];
    }

    public function getEntity($id)
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general'
        );

        return $this->get($this->apiPath . '/entities/' . $id, $queryParams);
    }

    public function putEntity($id, $bodyParams)
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general',
        );

        return $this->put($this->apiPath . '/entities/' . $id, $queryParams, $bodyParams);
    }

    public function postEntityAcl($id, $bodyParams)
    {
        $this->post($this->apiPath . '/entities/' . $id . '/acl', array(), $bodyParams);
    }

    public function deleteEntity($id)
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general',
        );

        return $this->delete($this->apiPath . '/entities/' . $id, $queryParams);
    }

    public function getMetadataSchemas()
    {
        $user = $this->userService->getUser();

        $queryParams = array(
            'user' => $user,
            'group' => 'general'
        );

        return $this->get($this->apiPath . '/metadataSchema', $queryParams)['results'];
    }

    public function getEntityMetadata($id)
    {
        return $this->get($this->apiPath . '/entities/' . $id . '/metadata');
    }

    public function getTasks()
    {
        $queryParams['assignee'] = $this->userService->getUser();
        return $this->get($this->apiPath . '/activiti/runtime/tasks', $queryParams);

    }

    public function getTask($id)
    {
        return $this->get($this->apiPath . '/activiti/runtime/tasks/' . $id);
    }

    public function putTask($id, $params)
    {
        return $this->put($this->apiPath . '/activiti/runtime/tasks/' . $id, array(), $params);
    }

    public function postCompleteTask($id)
    {

        $params = array(
            'action' => 'complete'
        );

        return $this->post($this->apiPath . '/activiti/runtime/tasks/' . $id, array(), $params);
    }

    public function getDiagram($id)
    {

        return $this->get($this->apiPath . '/activiti/runtime/process-instances/' . $id . '/diagram');
    }
}
