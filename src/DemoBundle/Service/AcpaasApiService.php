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

    private function get($url, $params)
    {

        $request = $this->client->get($url . '?' . http_build_query($params));
        $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
        $response = $request->send();

        return $response->json();
    }

    private function post($url, $params = array())
    {

        try {
            $request = $this->client->post($url, array(), $params);
            $request->addHeader('Authorization', 'Bearer ' . $this->getAccessToken());
            return $request->send()->json();

        } catch (RequestException $ex) {
            die($ex->getResponse()->getBody());
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

    public function postRecord($number)
    {

        $params = array(
            'number' => $number,
        );

        $user = $this->userService->getUser();

        return $this->post('/api/v1/entities/Record?user=' . $user, $params);

    }

}