<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    public function statusProses()
    {
        //backend 
        $order = Order::whereIn('status', ['Paid', 'Kirim'])->orderBy('id', 'desc')->get();
        return view('backend.v_pesanan.proses', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'index' => $order
        ]);
    }

    public function statusSelesai()
    {
        //backend 
        $order = Order::where('status', 'Selesai')->orderBy('id', 'desc')->get();
        return view('backend.v_pesanan.selesai', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'judul' => 'Data Transaksi',
            'index' => $order
        ]);
    }

    public function statusDetail($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.v_pesanan.detail', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'judul' => 'Data Transaksi',
            'order' => $order,
        ]);
    }

    public function statusUpdate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $rules = [
            'alamat' => 'required',
        ];
        if ($request->status != $order->status) {
            $rules['status'] = 'required';
        }
        if ($request->noresi != $order->noresi) {
            $rules['noresi'] = 'required';
        }
        if ($request->pos != $order->pos) {
            $rules['pos'] = 'required';
        }
        $validatedData = $request->validate($rules);
        Order::where('id', $id)->update($validatedData);
        return redirect()->route('pesanan.proses')->with('success', 'Data berhasil diperbaharui');
    }

    public function addToCart($id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $produk = Produk::findOrFail($id);

        $order = Order::firstOrCreate(
            ['customer_id' => $customer->id, 'status' => 'pending'],
            ['total_harga' => 0]
        );

        $orderItem = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'produk_id' => $produk->id],
            ['quantity' => 1, 'harga' => $produk->harga]
        );

        if (!$orderItem->wasRecentlyCreated) {
            $orderItem->quantity++;
            $orderItem->save();
        }

        $order->total_harga += $produk->harga;
        $order->save();

        return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function viewCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending', 'paid')->first();
        if ($order) {
            $order->load('orderItems.produk');
        }
        return view('v_order.cart', compact('order'));
    }

    public function updateCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();;;
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();
        if ($order) {
            $orderItem = $order->orderItems()->where('id', $id)->first();
            if ($orderItem) {
                $quantity = $request->input('quantity');
                if ($quantity > $orderItem->produk->stok) {
                    return redirect()->route('order.cart')->with('error', 'Jumlah produk melebihi stok yang tersedia');
                }
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->quantity = $quantity;
                $orderItem->save();
                $order->total_harga += $orderItem->harga * $orderItem->quantity;
                $order->save();
            }
        }
        return redirect()->route('order.cart')->with('success', 'Jumlah produk berhasil diperbarui');
    }

    public function removeFromCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            $orderItem = OrderItem::where('order_id', $order->id)->where('produk_id', $id)->first();

            if ($orderItem) {
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->delete();
                if ($order->total_harga <= 0) {
                    $order->delete();
                } else {
                    $order->save();
                }
            }
        }
        return redirect()->route('order.cart')->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function selectShipping(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        // Pastikan order dengan status 'pending' ada untuk customer ini  
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        // Cek apakah order ada 
        if (!$order || $order->orderItems->count() == 0) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }
        // Inisialisasi total 
        $totalHarga = 0;
        $totalBerat = 0;

        // Hitung total harga dan total berat 
        foreach ($order->orderItems as $item) {
            $totalHarga += $item->harga * $item->quantity;
            $totalBerat += $item->produk->berat * $item->quantity;
        }

        // Kirim total ke view 
        return view('v_order.select_shipping', compact(
            'order',
            'totalHarga',
            'totalBerat'
        ));
    }

    public function updateOngkir(Request $request)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        $kota_asal       = $request->input('kota_asal');
        $kota_tujuan     = $request->input('kota_tujuan');

        if ($order) {
            // Simpan data ongkir ke dalam order  
            $order->kurir           = $request->input('kurir');
            $order->layanan_ongkir  = $request->input('layanan_ongkir');
            $order->biaya_ongkir    = $request->input('biaya_ongkir');
            $order->estimasi_ongkir = $request->input('estimasi_ongkir');
            $order->total_berat     = $request->input('total_berat');
            $order->alamat          = $request->input('alamat') . ', <br>' . $request->input('city_name') . ', <br>' . $request->input('province_name');
            $order->pos = $request->input('pos');
            $order->save();

            return redirect()->route('order.selectpayment')->with('kota_asal', $kota_asal)->with('kota_tujuan', $kota_tujuan);
        }
        return back()->with('error', 'Gagal menyimpan data ongkir');
    }

    public function getDestination(Request $request)
    {
        $search = $request->get('search', ''); // default kosong, bisa diketik oleh user 
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search=' . urlencode($search),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'key: ' . env('RAJAONGKIR_API_KEY'),
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);
        return response()->json($data);
    }

    public function calculateOngkir(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $weight = $request->input('weight');
        $courier = $request->input('courier');

        $postData = http_build_query([
            'origin' => $origin,
            'destination' => $destination,
            'weight' => $weight,
            'courier' => $courier,
            'price' => 'lowest'
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'key: ' . env('RAJAONGKIR_API_KEY'),
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(['error' => $err], 500);
        }

        return response()->json(json_decode($response, true));
    }

    public function selectPayment()
    {
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return redirect()->route('order.cart')
                ->with('error', 'Data customer tidak ditemukan.');
        }

        $order = Order::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return redirect()->route('order.cart')
                ->with('error', 'Keranjang belanja kosong.');
        }

        $order->load('orderItems.produk');

        $totalHarga = 0;

        foreach ($order->orderItems as $item) {
            $totalHarga += $item->harga * $item->quantity;
        }

        $grossAmount = $totalHarga + $order->biaya_ongkir;

        return view('v_order.select_payment', compact(
            'order',
            'totalHarga',
            'grossAmount'
        ));
    }

    public function complete()
    {
        // Ambil data customer berdasarkan user yang sedang login 
        $customer = Customer::where('user_id', Auth::id())->first();

        if (!$customer) {
            return redirect()->route('order.cart')
                ->with('error', 'Data customer tidak ditemukan.');
        }

        // Cari order yang masih pending 
        $order = Order::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return redirect()->route('order.cart')
                ->with('error', 'Keranjang belanja kosong.');
        }

        // Format tanggal: tahun-bulan-tanggal 
        $tanggal = date('ymd');

        // Ambil noresi terakhir 
        $lastOrder = Order::whereDate('created_at', date('Y-m-d'))
            ->whereNotNull('noresi')
            ->orderBy('noresi', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->noresi, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format nomor urut 
        $urut = str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        // Gabungkan noresi 
        $noresi = $tanggal . $urut;

        // Kurangi stok produk 
        foreach ($order->orderItems as $item) {

            $produk = Produk::find($item->produk_id);

            if ($produk) {

                $produk->stok -= $item->quantity;

                $produk->save();
            }
        }

        // Update order 
        $order->status = 'Paid';
        $order->noresi = $noresi;
        $order->save();

        return redirect()->route('order.history')
            ->with('success', 'Checkout berhasil. Nomor resi Anda: ' . $noresi);
    }

    public function orderHistory()
    {
        $customer = Customer::where('user_id', Auth::id())->first();;;
        // $orders = Order::where('customer_id', $customer->id)->where('status', 'completed')->get(); 
        $statuses = ['Paid', 'Kirim', 'Selesai'];
        $orders = Order::where('customer_id', $customer->id)
            ->whereIn('status', $statuses)
            ->orderBy('id', 'desc')
            ->get();
        return view('v_order.history', compact('orders'));
    }
    
}