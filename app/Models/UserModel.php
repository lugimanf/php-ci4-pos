<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user'; // Nama tabel
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password_hash'];
    protected $returnType = 'array';
    protected $useTimestamps = true;

    // Fungsi untuk mencari user berdasarkan username
    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }
}