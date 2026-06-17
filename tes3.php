<?php
session_start();

class User
{
    public $nama;
    public $point = 0;

    public function __construct($nama)
    {
        $this->nama = $nama;
    }

    public function tambahPoint()
    {
        $this->point += 1;
    }
}

class Menu
{
    public $namaMenu;
    public $ukuran;

    public function __construct($namaMenu, $ukuran)
    {
        $this->namaMenu = $namaMenu;
        $this->ukuran = $ukuran;
    }

    public function hargaDasar()
    {
        if ($this->namaMenu == "americano") return 15000;
        if ($this->namaMenu == "latte") return 20000;
        if ($this->namaMenu == "cappuccino") return 18000;
        if ($this->namaMenu == "matcha") return 22000;
        if ($this->namaMenu == "chocolate") return 21000;
        if ($this->namaMenu == "red velvet") return 23000;
        return 10000;
    }

    public function hargaUkuran()
    {
        if ($this->ukuran == "regular") return 0;
        if ($this->ukuran == "medium") return 3000;
        if ($this->ukuran == "large") return 5000;
        return 0;
    }
}

class NonKopi extends Menu
{
    public $rasa;

    public function __construct($namaMenu, $ukuran, $rasa)
    {
        parent::__construct($namaMenu, $ukuran);
        $this->rasa = $rasa;
    }
}

class Voucher
{
    public $kodeVoucher;
    public $diskon = 0;

    public function __construct($kode)
    {
        $this->kodeVoucher = $kode;

        if ($kode == "KOPIHEMAT") {
            $this->diskon = 0.1;
        } else if ($kode == "DISKON5") {
            $this->diskon = 0.05;
        } else {
            $this->diskon = 0;
        }
    }
}

class Pesanan
{
    public $user;
    public $menu;
    public $voucher;
    public $totalHarga;
    public $totalDiskon; 

    public function __construct($user, $menu, $voucher)
    {
        $this->user = $user;
        $this->menu = $menu;
        $this->voucher = $voucher;
    }

    public function hitungTotal()
    {
        $hargaAwal = $this->menu->hargaDasar() + $this->menu->hargaUkuran();
        $hargaSetelahVoucher = $hargaAwal - ($hargaAwal * $this->voucher->diskon);
        $hargaAkhir = $hargaSetelahVoucher - ($hargaSetelahVoucher * 0.02);
        $this->totalHarga = $hargaAkhir;
        $this->totalDiskon = $hargaAwal - $hargaAkhir;
        return $hargaAkhir;
    }
}

if (!isset($_SESSION['riwayat'])) {
    $_SESSION['riwayat'] = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = new User($_POST['nama']);
    $menu = new NonKopi($_POST['menu'], $_POST['ukuran'], $_POST['rasa']);
    $voucher = new Voucher($_POST['voucher']);

    $pesanan = new Pesanan($user, $menu, $voucher);
    $total = $pesanan->hitungTotal();

    $user->tambahPoint();

    $waktu = date("H.i.s");

    $_SESSION['riwayat'][] = [
        "waktu" => $waktu,
        "nama" => $user->nama,
        "menu" => $menu->namaMenu,
        "total" => $total
    ];

    $_SESSION['last'] = [
        "nama" => $user->nama,
        "menu" => $menu->namaMenu,
        "harga" => $total,
        "diskon" => $pesanan->totalDiskon,
        "voucher" => $voucher->kodeVoucher,
        "point" => $user->point
    ];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>KopiNesia System</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: #f4f4f4;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .left {
            flex: 1;
            background: #222;
            color: white;
            padding: 30px;
        }

        .right {
            flex: 1;
            padding: 30px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }

        button {
            padding: 10px;
            width: 100%;
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }

        .box {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="left">
            <h2>KopiNesia Order</h2>

            <form method="POST">
                <input name="nama" placeholder="Nama pelanggan" required>

                
                <select name="menu" id="menu">
                    <option value="americano">Americano</option>
                    <option value="latte">Latte</option>
                    <option value="cappuccino">Cappuccino</option>
                </select>

                <select name="rasa" id="rasa" onchange="updateMenu()">
                    <option value="kopi">Kopi</option>
                    <option value="manis">Minuman Manis</option>
                </select>


                <select name="ukuran">
                    <option value="regular">Regular</option>
                    <option value="medium">Medium</option>
                    <option value="large">Large</option>
                </select>

                <input name="voucher" placeholder="Kode Voucher (opsional)">

                <button type="submit">Order</button>
            </form>
        </div>

        <div class="right">

            <h2>Order Summary</h2>

            <?php if (isset($_SESSION['last'])) { ?>
                <div class="box">
                    <p>Nama: <?= $_SESSION['last']['nama'] ?></p>
                    <p>Menu: <?= ucfirst($_SESSION['last']['menu']) ?></p>
                    <p> Total Diskon: Rp <?= number_format(isset($_SESSION['last']['diskon']) ? $_SESSION['last']['diskon'] : 0, 0, ',', '.') ?></p>
                    <p>Total Bayar: Rp <?= number_format($_SESSION['last']['harga'], 0, ',', '.') ?></strong></p>
                     <p>Voucher: <?= $_SESSION['last']['voucher'] ?></p>
                    <p>Point: <?= $_SESSION['last']['point'] ?></p>
                </div>
            <?php } ?>

            <h2>Riwayat Transaksi</h2>

            <?php foreach ($_SESSION['riwayat'] as $r) { ?>
                <div class="box">
                    <?= $r['waktu'] ?> - <?= $r['nama'] ?> - <?= ucfirst($r['menu']) ?> - Rp <?= number_format($r['total'], 0, ',', '.') ?>
                </div>
            <?php } ?>

        </div>
    </div>

    <script>
        function updateMenu() {
            const jenisRasa = document.getElementById('rasa').value;
            const menuSelect = document.getElementById('menu');
            menuSelect.innerHTML = "";
            if (jenisRasa === "kopi") {
                menuSelect.options[menuSelect.options.length] = new Option('Americano', 'americano');
                menuSelect.options[menuSelect.options.length] = new Option('Latte', 'latte');
                menuSelect.options[menuSelect.options.length] = new Option('Cappuccino', 'cappuccino');
            } else if (jenisRasa === "manis") {
                menuSelect.options[menuSelect.options.length] = new Option('Matcha', 'matcha');
                menuSelect.options[menuSelect.options.length] = new Option('Chocolate', 'chocolate');
                menuSelect.options[menuSelect.options.length] = new Option('Red Velvet', 'red velvet');
            }
        }
    </script>

</body>

</html>