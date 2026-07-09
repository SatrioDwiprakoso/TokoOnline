@extends('backend.v_layouts.app') 

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body border-top">
                    <h5 class="card-title">Halaman Beranda</h5>
                    <div class="alert alert-success" role="alert">
                        <h4 class="alert-heading"> Selamat Datang, {{ Auth::user()->nama }}</h4> 
                        <p class="mb-0">Aplikasi Toko Online dengan hak akses yang anda miliki sebagai <b>
                            @if (Auth::user()->role == 1)
                                Super Admin
                            @elseif(Auth::user()->role == 0)
                                Admin
                            @endif
                        </b> ini adalah halaman utama dari aplikasi Web Programming. Studi Kasus Toko Online.</p>
                        <hr>
                        <p class="mb-0">Kuliah..? BSI Aja !!!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Site Analysis</h4>
                    <h5 class="card-subtitle">Overview of Latest Month</h5>
                    <div class="flot-chart" style="height: 350px; width: 100%;">
                        <div class="flot-chart-content" id="flot-line-chart" style="height: 100%; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="bg-dark p-10 text-white text-center">
                                <i class="fa fa-user m-b-5 font-20"></i>
                                <h5 class="m-b-0 m-t-5">{{ $totalUsers }}</h5>
                                <small class="font-light">Total Users</small>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="bg-dark p-10 text-white text-center">
                                <i class="fa fa-plus m-b-5 font-20"></i>
                                <h5 class="m-b-0 m-t-5">{{ $newUsers }}</h5>
                                <small class="font-light">New Users</small>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="bg-dark p-10 text-white text-center">
                                <i class="fa fa-shopping-cart m-b-5 font-20"></i>
                                <h5 class="m-b-0 m-t-5">{{ $totalProduk }}</h5>
                                <small class="font-light">Total Shop</small>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="bg-dark p-10 text-white text-center">
                                <i class="fa fa-tag m-b-5 font-20"></i>
                                <h5 class="m-b-0 m-t-5">{{ $totalOrders }}</h5>
                                <small class="font-light">Total Orders</small>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="bg-dark p-10 text-white text-center">
                                <i class="fa fa-table m-b-5 font-20"></i>
                                <h5 class="m-b-0 m-t-5">{{ $pendingOrders }}</h5>
                                <small class="font-light">Pending Orders</small>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <div class="bg-dark p-10 text-white text-center">
                                <i class="fa fa-globe m-b-5 font-20"></i>
                                <h5 class="m-b-0 m-t-5">{{ $onlineOrders }}</h5>
                                <small class="font-light">Online Orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
    
    <script>
        $(document).ready(function() {
            if ($("#flot-line-chart").length > 0) {
                // Men-generate data gelombang sinus dan kosinus
                var sin = [], cos = [];
                for (var i = 0; i < 14; i += 0.2) {
                    sin.push([i, Math.sin(i)]);
                    cos.push([i, Math.cos(i)]);
                }

                // Eksekusi pembuatan grafik
                $.plot($("#flot-line-chart"), [
                    { data: sin, label: "sin(x)", color: "#ee7951" }, 
                    { data: cos, label: "cos(x)", color: "#4fb9f0" }  
                ], {
                    series: {
                        lines: { show: true, lineWidth: 2 },
                        points: { show: true, radius: 3 }
                    },
                    grid: { hoverable: true, borderColor: "#eeeeee", borderWidth: 1 },
                    yaxis: { min: -1.2, max: 1.2 },
                    xaxis: { show: true }
                });

                // Tooltip saat data di-hover
                $("<div id='tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    border: "1px solid #fdd",
                    padding: "2px",
                    "background-color": "#fee",
                    opacity: 0.80
                }).appendTo("body");

                $("#flot-line-chart").bind("plothover", function (event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0].toFixed(2),
                            y = item.datapoint[1].toFixed(2);
                        $("#tooltip").html(item.series.label + " of " + x + " = " + y)
                            .css({top: item.pageY+5, left: item.pageX+5})
                            .fadeIn(200);
                    } else {
                        $("#tooltip").hide();
                    }
                });
            }
        });
    </script>
@endsection