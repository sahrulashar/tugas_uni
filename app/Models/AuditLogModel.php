<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'aksi',
        'tabel_terdampak',
        'record_id',
        'detail_perubahan',
        'waktu',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil semua log audit, urut dari yang terbaru
     */
    public function getAll_l1H(): array
    {
        return $this->orderBy('waktu', 'DESC')->findAll();
    }

    /**
     * Ambil log audit berdasarkan tabel dan record tertentu
     */
    public function getByRecord_l1H(string $tabel, int $recordId): array
    {
        return $this->where('tabel_terdampak', $tabel)
                    ->where('record_id', $recordId)
                    ->orderBy('waktu', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log audit berdasarkan user_id
     */
    public function getByUser_l1H(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('waktu', 'DESC')
                    ->findAll();
    }
}
