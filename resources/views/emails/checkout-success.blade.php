<!DOCTYPE html>
<html>
<body>

    <h2>Pesanan Berhasil Dibuat</h2>

    <p>
        Halo {{ $pesan->customer->user->name }},
    </p>

    <p>
        Terima kasih telah melakukan pemesanan.
    </p>

    <hr>

    <p>
        <strong>ID Pesanan:</strong>
        {{ $pesan->id_pesan }}
    </p>

    <p>
        <strong>Status:</strong>
        Menunggu Pembayaran
    </p>

    <p>
        <strong>Kode Unik:</strong>
        {{ $kodeUnik }}
    </p>

    <p>
        <strong>Total Transfer:</strong>
        <b>
            Rp {{ number_format($totalBayar, 0, ',', '.') }}
        </b>
    </p>

    <hr>

    <h3>Transfer ke Rekening Berikut</h3>

    <p>
        Bank:
        <strong>{{ $rekening['bank'] }}</strong>
    </p>

    <p>
        No. Rekening:
        <strong>{{ $rekening['nomor'] }}</strong>
    </p>

    <p>
        Atas Nama:
        <strong>{{ $rekening['atas_nama'] }}</strong>
    </p>

    <hr>

    <p>
        Mohon transfer sesuai nominal hingga 3 digit terakhir agar pembayaran dapat diverifikasi lebih cepat.
    </p>

    <p>
        Setelah melakukan pembayaran, admin akan segera memproses pesanan Anda.
    </p>

</body>
</html>
