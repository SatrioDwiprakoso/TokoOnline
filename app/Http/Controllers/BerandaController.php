<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BerandaController extends Controller
{
    public function berandaBackend()
    {
        // 1. Total Users (Menghitung semua user)
        $totalUsers = User::count();

        // 2. New Users (Menghitung user yang mendaftar di bulan dan tahun ini)
        $newUsers = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 3. Total Shop/Produk (Menghitung total produk)
        $totalProduk = Produk::count();

        // 4. Total Orders (Menghitung semua transaksi)
        $totalOrders = Order::count();

        // 5. Pending Orders (Asumsi status bernilai 0 atau 'pending')
        // Silakan sesuaikan nilai '0' ini dengan status yang Anda gunakan di database
        $pendingOrders = Order::where('status', 0)->count();

        // 6. Online Orders / Sukses (Asumsi status bernilai 1 atau 'lunas')
        // Silakan sesuaikan nilai '1' ini dengan status yang Anda gunakan di database
        $onlineOrders = Order::where('status', 1)->count();
        return view('backend.v_beranda.index', [
            'judul'         => 'Halaman Beranda',
            'totalUsers'    => $totalUsers,
            'newUsers'      => $newUsers,
            'totalProduk'   => $totalProduk,
            'totalOrders'   => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'onlineOrders'  => $onlineOrders
        ]);
    }

    public function index()
    {
        $produk = Produk::where('status', 1)->orderBy('updated_at', 'desc')->paginate(6);
        return view('v_beranda.index', [
            'judul' => 'Halaman Beranda',
            'produk' => $produk,
        ]);
    }
}