<?php

namespace DemoBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class UserService
{

    private $config;
    private $session;

    public function __construct(array $config, Session $session)
    {
        $this->config = $config;
        $this->session = $session;
    }

    public function setUser($user)
    {

        if (!in_array($user, $this->config['users'])) {
            $user = $this->config['users'][0];
        }

        $this->session->set('user', $user);

        return $user;
    }

    public function getUser()
    {

        $user = $this->session->get('user', null);

        if (is_null($user)) {
            $user = $this->config['users'][0];
            $this->setUser($user);
        }

        return $user;
    }

    public function getUsers()
    {
        return $this->config['users'];
    }


}