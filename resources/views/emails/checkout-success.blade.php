<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Berhasil</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #0056b3;">Halo, Pelanggan!</h2>

        <p>Terima kasih telah mempercayakan kebutuhan cetak Anda di <strong>Bikin Cetak</strong>. Pesanan Anda telah berhasil kami rekam ke dalam sistem dengan rincian sebagai berikut:</p>

        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p><strong>Nomor Pesanan:</strong> {{ $pesan->id_pesan }}</p>
            <p><strong>Total Pesanan:</strong> Rp {{ number_format($totalTransfer - $kodeUnik, 0, ',', '.') }}</p>
            <p><strong>Kode Unik:</strong> Rp {{ $kodeUnik }}</p>
            <hr style="border: top 1px solid #ddd;">
            <p style="font-size: 18px; color: #d9534f;"><strong>Total Bayar: Rp {{ number_format($totalTransfer, 0, ',', '.') }}</strong></p>
        </div>

        <h3>Informasi Pembayaran</h3>
        <p>Silakan lakukan pembayaran sesuai dengan <strong>Total Bayar</strong> (termasuk kode unik) ke rekening berikut agar sistem dapat melakukan verifikasi otomatis:</p>

        <ul>
            <li><strong>{{ $rekening['bank'] }}</strong> - {{ $rekening['nomor'] }} a/n {{ $rekening['atas_nama'] }}</li>
        </ul>

        <p>Setelah pembayaran terverifikasi, pesanan Anda akan segera masuk ke antrean produksi.</p>

        <p>Salam hangat,<br>
        <strong>Tim Bikin Cetak</strong></p>
    </div>
</body>
</html>
