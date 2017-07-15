<?php

namespace Command\Controllers;

class ArtisanController extends Controller
{
    protected $account;

    public function __construct()
    {
        $this->account = app('Command\Model\Account');
    }

    public function queue($array)
    {
        $email = $array['email'] ? : null;

        $user = $this->account
            ->where('email', $email)
            ->first();

       return $user['password'];
    }


}