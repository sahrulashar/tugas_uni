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
 *   // Setelah tambah (minimal):
 *   AuditLogger::catat('TAMBAH', 'coa', $id, ['after' => $dataBaru]);
 *
 *   // Setelah tambah (lengkap dengan modul & keterangan):
 *   AuditLogger::catat('TAMBAH', 'coa', $id, ['after' => $dataBaru], 'Master', 'Tambah COA: 1-1100');
 *
 *   // Setelah edit:
 *   AuditLogger::catat('EDIT', 'supplier', $id, ['before' => $dataLama, 'after' => $dataBaru], 'Master', 'Edit Supplier: PT Maju');
 *
 *   // Setelah hapus:
 *   AuditLogger::catat('HAPUS', 'karyawan', $id, ['before' => $dataSebelum], 'Master', 'Hapus Karyawan: Budi');
 */
class AuditLogger
{
    /**
     * Catat satu entri audit log.
     *
     * @param string $aksi        'TAMBAH'|'EDIT'|'HAPUS'|'NONAKTIF'|'AKTIF'|'SOFT_DELETE'
     * @param string $tabel       Nama tabel yang terdampak, e.g. 'coa', 'supplier'
     * @param int    $recordId    Primary key record yang terdampak
     * @param array  $detail      ['before' => [...], 'after' => [...]] — opsional
     * @param string $modul       Kategori modul: 'Master'|'Transaksi'|'Laporan' — opsional
     * @param string $keterangan  Deskripsi singkat yang bisa dibaca manusia — opsional
     * @return bool
     */
    public static function catat(
        string $aksi,
        string $tabel,
        int $recordId,
        array $detail = [],
        string $modul = '',
        string $keterangan = ''
    ): bool {
        $userId   = (int) (session()->get('user_id') ?? 0);

        // Nama user: ambil dari session jika ada, fallback ke 'System'
        // Ganti 'nama_user' dengan key session yang sesuai setelah login dibuat
        $namaUser = session()->get('nama_user')
                 ?? session()->get('nama')
                 ?? session()->get('username')
                 ?? 'System';

        // Ambil info request secara otomatis
        $url = (string) current_url();

        $auditModel = new AuditLogModel();

        return (bool) $auditModel->insert([
            'user_id'           => $userId,
            'nama_user'         => $namaUser,
            'aksi'              => strtoupper($aksi),
            'modul'             => $modul ?: null,
            'tabel_terdampak'   => $tabel,
            'record_id'         => $recordId,
            'keterangan'        => $keterangan ?: null,
            'url'               => $url ?: null,
            'detail_perubahan'  => empty($detail) ? null : json_encode($detail, JSON_UNESCAPED_UNICODE),
            'waktu'             => date('Y-m-d H:i:s'),
        ]);
    }
}
