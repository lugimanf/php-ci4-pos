<?php

namespace App\Controllers\Api\Utilities;

use CodeIgniter\Controller;
use Config\Services;
class Cache extends Controller
{
    protected $session;
     
    public function __construct()
    {
        $this->session = Services::cache();
    }

    public function clear_cache()
    {        
        $this->session->clean();
        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }    
}