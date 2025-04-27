<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Usecases\Api\Auth as AuthUsecase;
use App\Models\UserModel;

class Auth extends Controller
{
    protected $authUsecase;
     
    public function __construct()
    {
        $this->authUsecase = new AuthUsecase();
    }

    public function login()
    {
        // Ambil data input dari request
        $payload = $this->request->getJSON(true);

        // Cek apakah payload valid
        if (!is_array($payload) || empty($payload)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid payload',
            ])->setStatusCode(400); // Bad Request
        }
        
        $rules = [
            'email'    => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                ],
            ],
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => validation_first_error($this->validator->getErrors()),
            ])->setStatusCode(400); // Unprocessable Entity
        }

        $result = $this->authUsecase->login($payload);

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
        $payload = $this->request->getJSON(true);

        $rules = [
            'otp'    => [
                'rules' => 'required|max_length[4]|min_length[4]',
                'errors' => [
                    'required'    => 'OTP wajib diisi.',
                    'max_length' => 'OTP harus 4 angka',
                    'min_length' => 'OTP harus 4 angka',
                ],
            ],
            'token'    => [
                'rules' => 'required',
                'errors' => [
                    'required'    => 'Token tidak terkirim',
                ],
            ],
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => validation_first_error($this->validator->getErrors()),
            ])->setStatusCode(400); // Unprocessable Entity
        }

        $result = $this->authUsecase->login_otp($payload);

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