<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Penawaran {{ $penawaran->id_penawaran }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: sans-serif; font-size: 11px; color: #000; line-height: 1.4; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .italic { font-style: italic; }

        .header-kop { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .table-item { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-item th { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px; font-weight: bold; }
        .table-item td { padding: 6px; vertical-align: top; }
        .border-box { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 6px 0; margin-bottom: 15px; }
    </style>
</head>
<body>

    <!-- NOMOR PENAWARAN -->
    <div style="float: right; width: 200px;" class="font-bold text-right">
        Nomor : {{ $penawaran->id_penawaran }}
    </div>
    <div style="clear: both;"></div>

    <!-- KOP SURAT -->
    <div class="header-kop">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 style="font-size: 24px; margin: 0; font-weight: 900;" class="uppercase">BIKIN CETAK</h1>
                    <div class="italic" style="font-size: 9px;">cetak online semakin mudah dan cepat</div>
                    <div class="italic font-bold" style="font-size: 10px;">Digital Printing, Offset, Merchandise</div>
                </td>
                <td class="text-right" style="font-size: 9px;">
                    Jl Barata Jaya XVII No 3 Kec. Gubeng, Kota SBY, Jawa Timur 60284<br>
                    Telpon : 0830831862770 | Email : bikinkancetak@gmail.com
                </td>
            </tr>
        </table>
    </div>

    <!-- KEPADA -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 60%;">
                Kepada Yth,<br>
                <strong>{{ $penawaran->customer->user->name ?? '-' }}</strong><br>
                <div style="font-size: 10px;">{{ $penawaran->alamat->alamat_lengkap ?? '' }}</div>
            </td>
            <td class="italic text-right" style="vertical-align: top;">
                Surabaya, {{ \Carbon\Carbon::parse($penawaran->tanggal_penawaran)->translatedFormat('d/m/Y H:i:s') }}
            </td>
        </tr>
    </table>

    <p>Dengan Hormat,</p>
    <p>Bersama surat ini kami mengajukan Penawaran harga jasa percetakan, sesuai dengan permintaan spesifikasi yang kami terima untuk mengerjakan pencetakan sebagai berikut :</p>

    <!-- TABEL ITEM -->
    <table class="table-item">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th class="text-left">Nama Barang</th>
                <th class="text-center" style="width: 50px;">Qty</th>
                <th class="text-right" style="width: 90px;">Satuan Rp.</th>
                <th class="text-right" style="width: 110px;">Jumlah Harga Rp.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penawaran->penawaranItem as $index => $item)
                @php
                    $namaBarang = $item->nama_produk_snapshot;

                    // Parse Atribut
                    $attrs = is_string($item->atribut_custom_snapshot) ? json_decode($item->atribut_custom_snapshot, true) : $item->atribut_custom_snapshot;
                    if ($attrs && is_array($attrs)) {
                        foreach ($attrs as $v) { $namaBarang .= '_' . $v; }
                    }

                    // Parse Finishing & Hitung Harga
                    $totalFinishing = 0;
                    if ($item->penawaranItemFinishing) {
                        foreach ($item->penawaranItemFinishing as $fin) {
                            $finName = last(explode(':', $fin->nama_finishing_snapshot));
                            $namaBarang .= '_' . trim($finName);
                            $totalFinishing += (float) ($fin->harga_finishing_snapshot ?? 0);
                        }
                    }

                    $qty = (int) ($item->jumlah ?? 1);
                    $hargaSatuanBersih = (float) ($item->harga_satuan_snapshot ?? 0) + $totalFinishing;
                    $totalItem = ($hargaSatuanBersih * $qty) + (float) ($item->harga_pengerjaan_snapshot ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $namaBarang }}</td>
                    <td class="text-center">{{ $qty }}</td>
                    <td class="text-right">{{ number_format($hargaSatuanBersih, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totalItem, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- DELIVERY -->
    <div class="border-box">
        <table style="width: 100%;">
            <tr>
                <td>
                    <strong>Delivery :</strong>
                    {{ $penawaran->ekspedisi_nama && $penawaran->ekspedisi_nama !== 'Belum Termasuk Biaya Kirim' ? $penawaran->ekspedisi_nama : 'Belum Termasuk Biaya Kirim' }}
                    @if($penawaran->ekspedisi_layanan && $penawaran->ekspedisi_nama !== 'Ambil di Toko')
                        - {{ $penawaran->ekspedisi_layanan }}
                    @endif
                </td>
                @if($penawaran->harga_ongkir > 0)
                    <td class="font-bold text-right">
                        {{ number_format($penawaran->harga_ongkir, 0, ',', '.') }}
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <p>Apabila terdapat pertanyaan dan membutuhkan info lebih lanjut, Anda dapat menghubungi kami. Demikian surat penawaran ini saya sampaikan atas perhatiannya kami ucapkan terima kasih.</p>

    <!-- CATATAN -->
    <div style="margin-top: 15px;">
        <div>Catatan :</div>
        <div style="white-space: pre-wrap; font-size: 10px;">{{ $penawaran->catatan_penawaran }}</div>
        <div class="italic font-bold" style="margin-top: 10px; font-size: 10px;">
            Rekening Pembayaran BCA 1930566086 | Mandiri 9000043545889 a/n MOH CHAIRUL ANAM
        </div>
    </div>

    <!-- TTD -->
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 60%;"></td>
            <td class="text-center">
                <h2 style="font-size: 16px; margin: 0; font-weight: 900;" class="uppercase">BIKIN CETAK</h2>
                <div class="italic" style="font-size: 9px; margin-bottom: 15px;">cetak online semakin mudah dan cepat</div>

                <div>Hormat Kami,</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="font-size: 12px;">MOHAMMAD CHAIRUL ANAM</div>
                <div class="italic" style="font-size: 10px;">Marketing Manager</div>
            </td>
        </tr>
    </table>

</body>
</html>
