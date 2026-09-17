<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

/**
 * AuditLogger — Helper terpusat untuk mencatat jejak audit.
 *
 * Dipanggil dari Controller setelah setiap operasi CRUD berhasil.
 *
 * Aksi yang didukung:
 *   'TAMBAH'      → setelah insert berhasil
 *   'EDIT'        → setelah update berhasil
 *   'HAPUS'       → setelah delete permanen
 *   'NONAKTIF'    → setelah soft-delete / nonaktifkan
 *   'AKTIF'       → setelah mengaktifkan kembali
 *   'SOFT_DELETE' → setelah soft-delete via is_deleted=1
 *
 * Contoh pemakaian:
 *
 *   // Setelah tambah:
 *   AuditLogger::catat('TAMBAH', 'coa', $id, ['after' => $dataBaru]);
 *
 *   // Setelah edit:
 *   AuditLogger::catat('EDIT', 'supplier', $id, ['before' => $dataLama, 'after' => $dataBaru]);
 *
 *   // Setelah hapus:
 *   AuditLogger::catat('HAPUS', 'karyawan', $id, ['before' => $dataSebelum]);
 */
class AuditLogger
{
    /**
     * Catat satu entri audit log.
     *
     * @param string $aksi      'TAMBAH'|'EDIT'|'HAPUS'|'NONAKTIF'|'AKTIF'|'SOFT_DELETE'
     * @param string $tabel     Nama tabel yang terdampak, e.g. 'coa', 'supplier'
     * @param int    $recordId  Primary key record yang terdampak
     * @param array  $detail    ['before' => [...], 'after' => [...]] — opsional
     * @return bool
     */
    public static function catat(
        string $aksi,
        string $tabel,
        int $recordId,
        array $detail = []
    ): bool {
        $userId = session()->get('user_id') ?? 0;

        $auditModel = new AuditLogModel();

        return (bool) $auditModel->insert([
            'user_id'           => (int) $userId,
            'aksi'              => strtoupper($aksi),
            'tabel_terdampak'   => $tabel,
            'record_id'         => $recordId,
            'detail_perubahan'  => empty($detail) ? null : json_encode($detail, JSON_UNESCAPED_UNICODE),
            'waktu'             => date('Y-m-d H:i:s'),
        ]);
    }
}
