<?php

namespace App\Usecases\Api;

use App\Models\UserModel;
use Config\Services;
use Firebase\JWT\JWT;

class Auth
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = Services::cache();
    }

    public function login($params)
    {
        helper('otp');
        $email = $params['email'] ?? '';
        // Cek username dan password dengan model
        $userModel = new UserModel();
        $otp = generate_otp();
        $user = $userModel->findByEmail($email);

        if ($user) {
            // Simpan ke cache session
            if (ENVIRONMENT == "development"){
                log_message("error", "otp sekarang => ".$otp);
            }
            $sessionId = uniqid('login_otp_', true);
            $this->session->save($sessionId, [
                'user_id' => $user["id"],
                'otp' =>$otp,
            ], 120); // expired 2 menit

            // Buat token JWT
            $payload = [
                'iss' => 'your-domain.com', // Issuer
                'aud' => 'your-domain.com', // Audience
                'iat' => time(),             // Issued at
                'nbf' => time(),             // Not before
                'exp' => time() + 3600,       // Expired in 1 hour
                'data' => [
                    'session_id' => $sessionId,
                ]
            ];

            $jwt = JWT::encode($payload, getenv('encryption.key'), 'HS256');

            return [
                "data" => [
                        "token" => $jwt,
                    ]
                ];

            
        } else {
            return [
                "message" => "user tidak ditemukan",
            ];
        }
    }

    public function login_otp($params)
    {
        helper('otp');
        $otp = $params['otp'] ?? '';
        $token = $params['token'] ?? '';
        

        try {
            $encryptionKey = getenv('encryption.key');
            // Memverifikasi dan mendekodekan JWT
            $decoded = JWT::decode($token, $encryptionKey,['HS256']);
            
            // Jika valid, kita ambil session ID dari decoded token
            $session_id = $decoded->data->session_id; // Pastikan token menyimpan field 'userId'

            // Ambil data session berdasarkan ID
            $sessionData = $this->session->get($session_id);

            // Jika data session ada, return data
            if ($sessionData && ($otp == $sessionData['otp'])) {
                // Simpan ke cache session
                $sessionId = uniqid('login_', true);
                $this->session->save($sessionId, [
                    'user_id' => $sessionData['user_id'],
                    'otp' =>$otp,
                ], 3600 * 24 * 30); // expired 30 hari

                // Buat token JWT
                $payload = [
                    'iss' => 'your-domain.com', // Issuer
                    'aud' => 'your-domain.com', // Audience
                    'iat' => time(),             // Issued at
                    'nbf' => time(),             // Not before
                    'exp' => time() + (3600 * 24 * 30),       // Expired in 1 hour
                    'data' => [
                        'session_id' => $sessionId,
                    ]
                ];

                $jwt = JWT::encode($payload, getenv('encryption.key'), 'HS256');

                return [
                    'data' => [
                        "token" => $jwt,
                    ]
                    ];
            }

            // Jika tidak ada data session
            return [
                "message" => "Invalid or expired token"
            ];

        } catch (\Exception $e) {
            // Jika token tidak valid atau error dalam decode
            return [
                "message" => "Invalid or expired token" . $e,
            ];
        }
    }
}