<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Usecases\Api\Auth as AuthUsecase;
use App\Models\UserModel;

class Auth extends Controller
{
     // ganti dengan secret key kamu

    public function login()
    {
        // Ambil data input dari request
        $request = $this->request->getJSON(true);
        $AuthUsecase = new AuthUsecase();
        $result = $AuthUsecase->login($request);

        if (isset($result['message'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message'],
            ])->setStatusCode(401);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result['data'],
        ]);
    }

    public function login_otp()
    {
        // Ambil data input dari request
        $request = $this->request->getJSON(true);

        $AuthUsecase = new AuthUsecase();
        $result = $AuthUsecase->login_otp($request);

        if (isset($result['message'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message'],
            ])->setStatusCode(401);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result['data'],
        ]);
    }

    public function register()
    {
        // Ambil data input dari request
        $request = $this->request->getJSON(true);
        $email = $request['email'] ?? '';

        // Cek username dan password dengan model
        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if ($user) {
            return $this->response->setJSON([
                'message' => 'email has been used',
            ])->setStatusCode(409);
        }

        return $this->response->setJSON([
            'message' => 'register email success',
            'link' => ""
        ])->setStatusCode(200);
    }
}