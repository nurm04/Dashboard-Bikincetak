<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Berhasil</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #0056b3;">Halo, {{ $pesan->customer->user->name ?? 'Pelanggan' }}!</h2>

        <p>Terima kasih telah mempercayakan kebutuhan cetak Anda di <strong>Bikin Cetak</strong>. Pesanan Anda telah berhasil kami rekam ke dalam sistem dengan rincian sebagai berikut:</p>

        <!-- RINCIAN ITEM PESANAN -->
        <div style="margin-bottom: 20px;">
            <p style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 8px;">Rincian Item Pesanan:</p>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background-color: #f1f1f1; text-align: left;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Produk & Spesifikasi</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Qty</th>
                        <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesan->pesananItem as $item)
                        @php
                            $hargaDasar = (float) ($item->harga_satuan_snapshot ?? 0);
                            $attr = is_string($item->atribut_custom_snapshot) ? json_decode($item->atribut_custom_snapshot, true) : ($item->atribut_custom_snapshot ?? []);

                            $sisi = 1;
                            foreach ($item->pesananItemFinishing as $f) {
                                $namaFin = strtolower($f->nama_finishing_snapshot ?? '');
                                if (str_contains($namaFin, 'dua sisi') || str_contains($namaFin, '2 sisi') || str_contains($namaFin, 'bolak')) {
                                    $sisi = 2; break;
                                }
                            }

                            if (isset($attr['Jumlah Halaman'])) {
                                $hal = max(1, (int) $attr['Jumlah Halaman']);
                                $hargaDasar += (max(0, $hal - 1) * $sisi * 1500);
                            } elseif (isset($attr['Luas Dihargai (m2)'])) {
                                $luas = max(1, (float) $attr['Luas Dihargai (m2)']);
                                $hargaDasar = $hargaDasar * $luas;
                            }

                            $finishingPerItem = 0;
                            $finishingFlat = 0;
                            foreach ($item->pesananItemFinishing as $f) {
                                $isKaliQty = (bool) ($f->kali_jumlah_pesan ?? optional($f->skuFinishing)->kali_jumlah_pesan);
                                $val = ($f->tipe ?? 'nominal') === 'persen'
                                    ? ($hargaDasar * ((float) $f->harga_finishing_snapshot / 100))
                                    : (float) $f->harga_finishing_snapshot;

                                if ($isKaliQty) {
                                    $finishingPerItem += $val;
                                } else {
                                    $finishingFlat += $val;
                                }
                            }

                            $qty = (int) ($item->jumlah ?? 1);
                            $sla = (float) ($item->harga_pengerjaan_snapshot ?? 0);

                            $hargaSatuan = $hargaDasar + $finishingPerItem;
                            $subtotalItem = ($hargaSatuan * $qty) + $finishingFlat + $sla;
                        @endphp
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">
                                <strong>{{ $item->nama_produk_snapshot }}</strong>
                                @if(!empty($attr) && is_array($attr))
                                    <div style="font-size: 11px; color: #666; margin-top: 2px;">
                                        @foreach($attr as $k => $v)
                                            <span>▪ {{ strtoupper($k) }}: {{ $v }}</span><br>
                                        @endforeach
                                    </div>
                                @endif

                                @if($item->pesananItemFinishing->count() > 0)
                                    <div style="font-size: 11px; color: #0056b3; margin-top: 2px;">
                                        @foreach($item->pesananItemFinishing as $f)
                                            <span>▪ {{ $f->kategori_finishing ? strtoupper($f->kategori_finishing) . ': ' : '' }}{{ $f->nama_finishing_snapshot }}
                                                <span style="font-style: italic; opacity: 0.8;">
                                                    ({{ ($f->tipe ?? 'nominal') === 'persen' ? $f->harga_finishing_snapshot.'%' : 'Rp '.number_format($f->harga_finishing_snapshot,0,',','.') }})
                                                </span>
                                            </span><br>
                                        @endforeach
                                    </div>
                                @endif

                                @if($sla > 0)
                                    <div style="font-size: 10px; font-weight: bold; margin-top: 6px;">
                                        + SLA ({{ $item->estimasi_pengerjaan_snapshot ?? 'Reguler' }}): Rp {{ number_format($sla, 0, ',', '.') }}
                                    </div>
                                @endif

                                @if($finishingFlat > 0)
                                    <div style="font-size: 10px; font-weight: bold; margin-top: 2px;">
                                        + Jasa Tambahan (Flat): Rp {{ number_format($finishingFlat, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: center; vertical-align: top;">{{ $qty }}</td>
                            <td style="padding: 8px; border: 1px solid #ddd; text-align: right; vertical-align: top;">Rp {{ number_format($subtotalItem, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p><strong>Nomor Pesanan / Transaksi:</strong> {{ $pesan->kode_transaksi }}</p>
            <p><strong>Total Pesanan:</strong> Rp {{ number_format($totalTransfer - $kodeUnik, 0, ',', '.') }}</p>
            <p><strong>Kode Unik:</strong> Rp {{ $kodeUnik }}</p>
            <hr style="border: 0; border-top: 1px solid #ddd; margin: 10px 0;">
            <p style="font-size: 18px; color: #d9534f;"><strong>Total Bayar: Rp {{ number_format($totalTransfer, 0, ',', '.') }}</strong></p>
        </div>

        <h3>Informasi Pembayaran</h3>
        <p>Silakan lakukan pembayaran sesuai dengan <strong>Total Bayar</strong> (termasuk kode unik) ke rekening berikut:</p>

        <ul>
            <li><strong>{{ $rekening['bank'] }}</strong> - {{ $rekening['nomor'] }} a/n {{ $rekening['atas_nama'] }}</li>
        </ul>

        <p>Setelah pembayaran terverifikasi, pesanan Anda akan segera masuk ke antrean produksi.</p>

        <p>Salam hangat,<br>
        <strong>Tim Bikin Cetak</strong></p>
    </div>
</body>
</html>
