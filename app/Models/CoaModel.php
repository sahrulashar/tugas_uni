<?php

namespace App\Models;

use CodeIgniter\Model;

class CoaModel extends Model
{
    protected $table            = 'coa';
    protected $primaryKey       = 'id_coa';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kode_coa',
        'nama_coa',
        'tipe_akun',
        'saldo_normal',
        'status',
    ];

    protected $useTimestamps = false;

    private array $urutanTipe = [
        'Aset',
        'Liabilitas',
        'Ekuitas',
        'Pendapatan',
        'Beban',
    ];

    public function getAll(): array
    {
        $builder = $this->db->table($this->table);

        $builder->select(
            'id_coa, kode_coa, nama_coa, tipe_akun, saldo_normal, status'
        );

        // FIELD() adalah fungsi khusus MySQL, tetap dipakai lewat orderBy raw
        $fieldList = "'" . implode("','", $this->urutanTipe) . "'";
        $builder->orderBy("FIELD(tipe_akun, {$fieldList})", '', false);
        $builder->orderBy('kode_coa', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getByTipe(string $tipe): array
    {
        return $this->select(
                'id_coa, kode_coa, nama_coa, tipe_akun, saldo_normal, status'
            )
            ->where('tipe_akun', $tipe)
            ->where('status', 'Aktif')
            ->orderBy('kode_coa', 'ASC')
            ->findAll();
    }

    public function getTipeAkun(): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('DISTINCT tipe_akun');

        $fieldList = "'" . implode("','", $this->urutanTipe) . "'";
        $builder->orderBy("FIELD(tipe_akun, {$fieldList})", '', false);

        return $builder->get()->getResultArray();
    }

    public function cekKode(string $kodeCoa, ?int $id = null): bool
    {
        $builder = $this->where('kode_coa', $kodeCoa);

        if ($id !== null) {
            $builder->where('id_coa !=', $id);
        }

        return $builder->countAllResults() > 0;
    }

    public function nonaktifkan(int $id): bool
    {
        return $this->update($id, ['status' => 'Tidak Aktif']);
    }

    public function aktifkan(int $id): bool
    {
        return $this->update($id, ['status' => 'Aktif']);
    }

    public function getTotalPerTipe(): array
    {
        $builder = $this->db->table($this->table);

        $builder->select(
            "tipe_akun,
             COUNT(*) AS total,
             SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) AS aktif,
             SUM(CASE WHEN status = 'Tidak Aktif' THEN 1 ELSE 0 END) AS tidak_aktif",
            false
        );

        $builder->groupBy('tipe_akun');

        $fieldList = "'" . implode("','", $this->urutanTipe) . "'";
        $builder->orderBy("FIELD(tipe_akun, {$fieldList})", '', false);

        return $builder->get()->getResultArray();
    }
}
