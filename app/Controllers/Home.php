<?php

namespace App\Controllers;

/**
 * Home — Controller Gerbang Masuk Aplikasi (Login & Logout)
 *
 * Sesuai arahan praktikum: control home / c_rbac tugasnya hanya untuk masuk aplikasi.
 */
class Home extends Auth
{
    public function index()
    {
        return $this->login();
    }
}
