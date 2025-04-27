<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;
use App\Models\UserModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authorizationHeader = $request->getHeaderLine('Authorization');

        if (!$authorizationHeader) {
            return Services::response()
                ->setJSON(['message' => 'Invalid token'])
                ->setStatusCode(401);
        }

        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return Services::response()
                ->setJSON(['message' => 'Invalid token'])
                ->setStatusCode(401);
        }

        try {
            $encryptionKey = getenv('encryption.key');
            $decoded = JWT::decode($token, new Key($encryptionKey, 'HS256'));

            // Misal token punya 'user_id'
            $session_id = $decoded->data->session_id ?? null;

            if (!$session_id) {
                return Services::response()
                    ->setJSON(['message' => 'Invalid token'])
                    ->setStatusCode(401);
            }

            $session = Services::cache();
            $sessionData = $session->get($session_id);

            if (!$sessionData) {
                return Services::response()
                    ->setJSON(['message' => 'Invalid token'])
                    ->setStatusCode(401);
            }

            $sessionData = $session->get($session_id);
            
            // Cari user di database
            $userModel = new UserModel();
            $user = $userModel->find($sessionData['user_id']);

            if (!$user) {
                return Services::response()
                    ->setJSON(['message' => 'Internal Server Error (1)'])
                    ->setStatusCode(500);
            }

            // Set data user di request agar bisa dipakai di Controller
            $request->user = $user;

        } catch (\Exception $e) {
            return Services::response()
                ->setJSON(['message' => 'Invalid token: ' . $e->getMessage()])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu apa-apa setelah response
    }
}