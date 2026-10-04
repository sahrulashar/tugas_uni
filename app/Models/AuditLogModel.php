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
        'nama_user',
        'aksi',
        'modul',
        'tabel_terdampak',
        'record_id',
        'keterangan',
        'url',
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

    /**
     * Ambil log audit berdasarkan modul
     *
     * Contoh: getByModul_l1H('Master') → semua aksi di modul Master
     */
    public function getByModul_l1H(string $modul): array
    {
        return $this->where('modul', $modul)
                    ->orderBy('waktu', 'DESC')
                    ->findAll();
    }

    /**
     * Cari log audit dengan filter fleksibel
     *
     * @param array $filter  Key yang didukung:
     *                       - 'aksi'           : 'TAMBAH'|'EDIT'|'HAPUS'|...
     *                       - 'modul'          : 'Master'|'Transaksi'|...
     *                       - 'tabel_terdampak': nama tabel
     *                       - 'user_id'        : ID user
     *                       - 'tanggal_mulai'  : string 'Y-m-d'
     *                       - 'tanggal_akhir'  : string 'Y-m-d'
     * @param int   $limit   Jumlah maksimal baris (0 = semua)
     * @return array
     */
    public function search_l1H(array $filter = [], int $limit = 0): array
    {
        $builder = $this->orderBy('waktu', 'DESC');

        if (!empty($filter['aksi'])) {
            $builder->where('aksi', strtoupper($filter['aksi']));
        }

        if (!empty($filter['modul'])) {
            $builder->where('modul', $filter['modul']);
        }

        if (!empty($filter['tabel_terdampak'])) {
            $builder->where('tabel_terdampak', $filter['tabel_terdampak']);
        }

        if (!empty($filter['user_id'])) {
            $builder->where('user_id', (int) $filter['user_id']);
        }

        if (!empty($filter['tanggal_mulai'])) {
            $builder->where('DATE(waktu) >=', $filter['tanggal_mulai']);
        }

        if (!empty($filter['tanggal_akhir'])) {
            $builder->where('DATE(waktu) <=', $filter['tanggal_akhir']);
        }

        return $limit > 0 ? $builder->findAll($limit) : $builder->findAll();
    }

    /**
     * Ambil statistik ringkasan audit log
     */
    public function getStats_l1H(): array
    {
        $today = date('Y-m-d');
        $total = $this->countAllResults();
        $hariIni = $this->where('DATE(waktu)', $today)->countAllResults();
        $modifikasi = $this->whereIn('aksi', ['EDIT', 'HAPUS', 'SOFT_DELETE', 'NONAKTIF'])->countAllResults();
        $userCount = $this->select('COUNT(DISTINCT user_id) as total_user', false)->first()['total_user'] ?? 0;

        return [
            'total'      => $total,
            'hari_ini'   => $hariIni,
            'modifikasi' => $modifikasi,
            'total_user' => (int) $userCount,
        ];
    }

    /**
     * Ambil opsi filter (daftar tabel, modul, aksi yang ada di data)
     */
    public function getFilterOptions_l1H(): array
    {
        $tabel = $this->db->table($this->table)
            ->distinct()
            ->select('tabel_terdampak')
            ->where('tabel_terdampak IS NOT NULL')
            ->where('tabel_terdampak !=', '')
            ->orderBy('tabel_terdampak', 'ASC')
            ->get()
            ->getResultArray();

        $modul = $this->db->table($this->table)
            ->distinct()
            ->select('modul')
            ->where('modul IS NOT NULL')
            ->where('modul !=', '')
            ->orderBy('modul', 'ASC')
            ->get()
            ->getResultArray();

        return [
            'tabel' => array_column($tabel, 'tabel_terdampak'),
            'modul' => array_column($modul, 'modul'),
        ];
    }
}

