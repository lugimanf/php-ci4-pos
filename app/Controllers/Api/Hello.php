<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class Hello extends ResourceController
{
    public function index()
    {
        return $this->respond([
            'status' => 200,
            'message' => 'Hello World!'
        ]);
    }
}