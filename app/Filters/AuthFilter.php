<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter — Filter yang memastikan user sudah login
 *
 * Dipasang di Routes.php untuk semua route yang butuh autentikasi.
 * Jika belum login → redirect ke halaman login.
 *
 * Cara pasang di Routes.php:
 *   $routes->group('46124026', ['filter' => 'auth'], function ($routes) { ... });
 *
 * @package App\Filters
 */
class AuthFilter implements FilterInterface
{
    /**
     * Jalankan sebelum request masuk ke controller.
     * Cek sesi — jika belum login, redirect ke login.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan login terlebih dahulu.');
        }
    }

    /**
     * Tidak perlu aksi setelah response.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak digunakan
    }
}
