<!DOCTYPE html>
<html>
<body>

    <h2>Update Status Pesanan</h2>

    <p>
        Halo {{ $pesan->customer->user->name }},
    </p>

    <p>
        Status pesanan Anda telah diperbarui.
    </p>

    <p>
        <strong>ID Pesanan:</strong>
        {{ $pesan->id_pesan }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ strtoupper(str_replace('_', ' ', $status)) }}
    </p>

    <p>
        Terima kasih telah menggunakan layanan kami.
    </p>

</body>
</html>
