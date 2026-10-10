<!DOCTYPE html>
<html>
<head>
    <title>Surat Penawaran</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #0056b3;">Halo, {{ $penawaran->customer->user->name ?? 'Bapak/Ibu' }}!</h2>

        <p>Terima kasih atas ketertarikan Anda pada layanan <strong>Bikin Cetak</strong>. Bersama email ini, kami melampirkan <strong>Surat Penawaran (Quotation)</strong> resmi sesuai dengan permintaan Anda.</p>

        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p><strong>Nomor Penawaran:</strong> {{ $penawaran->id_penawaran }}</p>
            <p><strong>Berlaku Sampai:</strong> {{ \Carbon\Carbon::parse($penawaran->berlaku_sampai)->translatedFormat('d F Y') }}</p>
        </div>

        <p>Silakan periksa dokumen PDF yang terlampir pada email ini untuk melihat rincian item, spesifikasi, dan estimasi harga.</p>

        <p>Jika Anda memiliki pertanyaan lebih lanjut, ingin melakukan penyesuaian, atau ingin melanjutkan ke tahap pemesanan, silakan membalas email ini atau menghubungi Admin kami melalui WhatsApp.</p>

        <p>Salam hangat,<br>
        <strong>Tim Bikin Cetak</strong></p>
    </div>
</body>
</html>
