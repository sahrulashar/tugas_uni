<?php

namespace App\Controllers;

/**
 * C_rbac — Controller Gerbang Masuk Aplikasi (Alias Home/Auth)
 *
 * Sesuai arahan praktikum: control home;c_rbac tugasnya hanya untuk masuk aplikasi.
 */
class C_rbac extends Auth
{
    public function index()
    {
        return $this->login();
    }
}
