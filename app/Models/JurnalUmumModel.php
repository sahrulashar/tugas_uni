<?php

namespace App\Models;

use CodeIgniter\Model;

class JurnalUmumModel extends Model
{
    protected $DBGroup       = 'default';
    protected $table         = 'jurnal_umum';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'no_jurnal',
        'tgl_jurnal',
        'no_referensi',
        'sumber',
        'keterangan',
        'total_debit',
        'total_kredit',
        'created_at',
    ];
    protected $useTimestamps = false;

    /**
     * Ambil seluruh jurnal beserta detail ayat jurnalnya.
     */
    public function getJurnalLengkap_l1H(): array
    {
        $db = \Config\Database::connect();
        $jurnalList = $this->orderBy('tgl_jurnal', 'DESC')->orderBy('id', 'DESC')->findAll();

        foreach ($jurnalList as &$j) {
            $j['details'] = $db->table('jurnal_umum_d')
                ->where('id_jurnal', $j['id'])
                ->get()
                ->getResultArray();
        }

        return $jurnalList;
    }
}
