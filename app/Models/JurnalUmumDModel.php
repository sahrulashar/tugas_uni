<?php

namespace App\Models;

use CodeIgniter\Model;

class JurnalUmumDModel extends Model
{
    protected $DBGroup       = 'default';
    protected $table         = 'jurnal_umum_d';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_jurnal',
        'kode_coa',
        'nama_coa',
        'debit',
        'kredit',
        'keterangan',
    ];
    protected $useTimestamps = false;
}
