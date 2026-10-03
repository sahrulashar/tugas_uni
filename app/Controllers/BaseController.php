<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\RbacChecker;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    // ─────────────────────────────────────────────────────────────
    //  RBAC HELPERS — tersedia di semua controller turunan
    // ─────────────────────────────────────────────────────────────

    /**
     * Cek akses dan redirect otomatis jika tidak punya izin.
     * Shortcut dari RbacChecker::gate()
     *
     * Cara pakai di controller anak:
     *   $this->cekAkses('ak1', 'daftar');
     */
    protected function cekAkses(string $kodeLaman, string $aksi): void
    {
        RbacChecker::gate($kodeLaman, $aksi);
    }

    /**
     * Cek apakah user punya akses (return bool, tidak redirect)
     * Dipakai di view untuk show/hide tombol
     */
    protected function bolehAkses(string $kodeLaman, string $aksi): bool
    {
        return RbacChecker::boleh($kodeLaman, $aksi);
    }

    /**
     * Ambil data user yang sedang login dari session
     *
     * @return array ['user_id'=>int, 'kode_user'=>string, 'nama_user'=>string]
     */
    protected function userLogin(): array
    {
        return [
            'user_id'   => (int)    session()->get('user_id'),
            'kode_user' => (string) session()->get('kode_user'),
            'nama_user' => (string) session()->get('nama_user'),
        ];
    }
}
