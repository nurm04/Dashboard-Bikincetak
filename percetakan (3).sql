-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 06, 2026 at 06:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `percetakan`
--

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE `akun` (
  `id_akun` varchar(10) NOT NULL,
  `nama_akun` varchar(255) NOT NULL,
  `kategori` enum('harta','kewajiban','modal','pendapatan','beban') NOT NULL,
  `saldo_normal` enum('debit','kredit') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`id_akun`, `nama_akun`, `kategori`, `saldo_normal`) VALUES
('1101', 'Kas', 'harta', 'debit'),
('1102', 'Bank', 'harta', 'debit'),
('1103', 'Piutang Usaha', 'harta', 'debit'),
('1104', 'Persediaan Bahan Baku', 'harta', 'debit'),
('2101', 'Hutang Usaha', 'kewajiban', 'kredit'),
('3101', 'Modal Awal', 'modal', 'kredit'),
('4101', 'Pendapatan Penjualan', 'pendapatan', 'kredit'),
('5101', 'Harga Pokok Penjualan (HPP)', 'beban', 'debit'),
('5201', 'Beban Operasional', 'beban', 'debit'),
('5202', 'Beban Gaji', 'beban', 'debit');

-- --------------------------------------------------------

--
-- Table structure for table `alamat`
--

CREATE TABLE `alamat` (
  `id_alamat` varchar(255) NOT NULL,
  `id_cstomer` varchar(255) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bahan_baku`
--

CREATE TABLE `bahan_baku` (
  `id_bahan_baku` varchar(255) NOT NULL,
  `nama_bahan_baku` varchar(255) NOT NULL,
  `satuan` varchar(255) NOT NULL,
  `harga_beli` float NOT NULL,
  `stok_awal` float NOT NULL,
  `stok_sekarang` float NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buku_besar`
--

CREATE TABLE `buku_besar` (
  `id_buku_besar` varchar(255) NOT NULL,
  `tanggal_transaksi` datetime NOT NULL,
  `tipe_referensi` enum('pesanan','pembelian') NOT NULL,
  `id_referensi` varchar(255) NOT NULL,
  `id_akun` varchar(255) NOT NULL,
  `debit` float NOT NULL DEFAULT 0,
  `kredit` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id_customer` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_role_customer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id_detail_pembelian` varchar(255) NOT NULL,
  `id_pembelian` varchar(255) NOT NULL,
  `id_bahan_baku` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` float NOT NULL,
  `subtotal` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_sku` varchar(255) NOT NULL,
  `id_pesan` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diskon_customer`
--

CREATE TABLE `diskon_customer` (
  `id_sku` varchar(255) NOT NULL,
  `id_role_customer` varchar(255) NOT NULL,
  `tipe` enum('nominal','persen') NOT NULL,
  `nilai` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `harga_bertingkat`
--

CREATE TABLE `harga_bertingkat` (
  `id_sku` varchar(255) NOT NULL,
  `min` int(11) NOT NULL,
  `max` int(11) NOT NULL,
  `harga` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `harga_pengerjaan`
--

CREATE TABLE `harga_pengerjaan` (
  `id_sku` varchar(255) NOT NULL,
  `pengerjaan` varchar(255) NOT NULL,
  `harga` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` varchar(255) NOT NULL,
  `nama_kategori` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komposisi`
--

CREATE TABLE `komposisi` (
  `id_sku` varchar(255) NOT NULL,
  `id_bahan_baku` varchar(255) NOT NULL,
  `jumlah_pakai` varchar(255) NOT NULL,
  `hpp` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembelian_bahan`
--

CREATE TABLE `pembelian_bahan` (
  `id_pembelian` varchar(255) NOT NULL,
  `tanggal_beli` datetime NOT NULL,
  `nama_supplier` varchar(255) NOT NULL,
  `total_biaya` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id_pesan` varchar(255) NOT NULL,
  `id_customer` varchar(255) NOT NULL,
  `id_alamat` varchar(255) NOT NULL,
  `tanggal_pesan` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tanggal_selesai` datetime NOT NULL,
  `status_operasional` enum('keranjang','menunggu_diproses','proses_pengerjaan','selesai') NOT NULL,
  `status_pembayaran` enum('belum_lunas','lunas') NOT NULL,
  `estimasi_pengerjaan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pilihan_varian`
--

CREATE TABLE `pilihan_varian` (
  `id_pilihan` varchar(255) NOT NULL,
  `nama_pilihan` varchar(255) NOT NULL,
  `id_varian` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` varchar(255) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `gambar` text NOT NULL,
  `id_kategori` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk_sku`
--

CREATE TABLE `produk_sku` (
  `id_sku` varchar(255) NOT NULL,
  `id_produk` varchar(255) NOT NULL,
  `nama_sku` varchar(255) NOT NULL,
  `harga_jasa` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk_varian`
--

CREATE TABLE `produk_varian` (
  `id_produk_varian` varchar(255) NOT NULL,
  `id_produk` varchar(255) NOT NULL,
  `id_varian` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_customer`
--

CREATE TABLE `role_customer` (
  `id_role_customer` varchar(255) NOT NULL,
  `role` enum('user','member','reseller') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_staf`
--

CREATE TABLE `role_staf` (
  `id_role_staf` varchar(255) NOT NULL,
  `role` enum('admin','kasir','produksi') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sku_detail_pilihan`
--

CREATE TABLE `sku_detail_pilihan` (
  `id_sku` varchar(255) NOT NULL,
  `id_pilihan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staf`
--

CREATE TABLE `staf` (
  `id_staf` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_role_staf` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `varian`
--

CREATE TABLE `varian` (
  `id_varian` varchar(255) NOT NULL,
  `nama_varian` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id_akun`);

--
-- Indexes for table `alamat`
--
ALTER TABLE `alamat`
  ADD PRIMARY KEY (`id_alamat`),
  ADD KEY `id_cstomer` (`id_cstomer`);

--
-- Indexes for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  ADD PRIMARY KEY (`id_bahan_baku`);

--
-- Indexes for table `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD PRIMARY KEY (`id_buku_besar`),
  ADD KEY `id_referensi` (`id_referensi`,`id_akun`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`),
  ADD KEY `id_role_customer` (`id_role_customer`);

--
-- Indexes for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`id_detail_pembelian`),
  ADD KEY `id_pembelian` (`id_pembelian`,`id_bahan_baku`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD KEY `id_sku` (`id_sku`),
  ADD KEY `id_pesan` (`id_pesan`);

--
-- Indexes for table `diskon_customer`
--
ALTER TABLE `diskon_customer`
  ADD KEY `id_sku` (`id_sku`),
  ADD KEY `id_role_customer` (`id_role_customer`);

--
-- Indexes for table `harga_bertingkat`
--
ALTER TABLE `harga_bertingkat`
  ADD KEY `id_sku` (`id_sku`);

--
-- Indexes for table `harga_pengerjaan`
--
ALTER TABLE `harga_pengerjaan`
  ADD KEY `id_sku` (`id_sku`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `komposisi`
--
ALTER TABLE `komposisi`
  ADD KEY `id_sku` (`id_sku`,`id_bahan_baku`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`);

--
-- Indexes for table `pembelian_bahan`
--
ALTER TABLE `pembelian_bahan`
  ADD PRIMARY KEY (`id_pembelian`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id_pesan`),
  ADD KEY `id_customer` (`id_customer`,`id_alamat`),
  ADD KEY `id_alamat` (`id_alamat`);

--
-- Indexes for table `pilihan_varian`
--
ALTER TABLE `pilihan_varian`
  ADD PRIMARY KEY (`id_pilihan`),
  ADD KEY `id_varian` (`id_varian`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `produk_sku`
--
ALTER TABLE `produk_sku`
  ADD PRIMARY KEY (`id_sku`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `produk_varian`
--
ALTER TABLE `produk_varian`
  ADD PRIMARY KEY (`id_produk_varian`),
  ADD KEY `id_produk` (`id_produk`,`id_varian`),
  ADD KEY `id_varian` (`id_varian`);

--
-- Indexes for table `role_customer`
--
ALTER TABLE `role_customer`
  ADD PRIMARY KEY (`id_role_customer`);

--
-- Indexes for table `role_staf`
--
ALTER TABLE `role_staf`
  ADD PRIMARY KEY (`id_role_staf`);

--
-- Indexes for table `sku_detail_pilihan`
--
ALTER TABLE `sku_detail_pilihan`
  ADD KEY `id_sku` (`id_sku`,`id_pilihan`),
  ADD KEY `id_pilihan` (`id_pilihan`);

--
-- Indexes for table `staf`
--
ALTER TABLE `staf`
  ADD PRIMARY KEY (`id_staf`),
  ADD KEY `role_staf` (`id_role_staf`);

--
-- Indexes for table `varian`
--
ALTER TABLE `varian`
  ADD PRIMARY KEY (`id_varian`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD CONSTRAINT `buku_besar_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`id_role_customer`) REFERENCES `role_customer` (`id_role_customer`);

--
-- Constraints for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian_bahan` (`id_pembelian`),
  ADD CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`);

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesan`) REFERENCES `pesan` (`id_pesan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_sku`) REFERENCES `produk_sku` (`id_sku`);

--
-- Constraints for table `diskon_customer`
--
ALTER TABLE `diskon_customer`
  ADD CONSTRAINT `diskon_customer_ibfk_1` FOREIGN KEY (`id_sku`) REFERENCES `produk_sku` (`id_sku`),
  ADD CONSTRAINT `diskon_customer_ibfk_2` FOREIGN KEY (`id_role_customer`) REFERENCES `role_customer` (`id_role_customer`);

--
-- Constraints for table `harga_bertingkat`
--
ALTER TABLE `harga_bertingkat`
  ADD CONSTRAINT `harga_bertingkat_ibfk_1` FOREIGN KEY (`id_sku`) REFERENCES `produk_sku` (`id_sku`);

--
-- Constraints for table `harga_pengerjaan`
--
ALTER TABLE `harga_pengerjaan`
  ADD CONSTRAINT `harga_pengerjaan_ibfk_1` FOREIGN KEY (`id_sku`) REFERENCES `produk_sku` (`id_sku`);

--
-- Constraints for table `komposisi`
--
ALTER TABLE `komposisi`
  ADD CONSTRAINT `komposisi_ibfk_1` FOREIGN KEY (`id_sku`) REFERENCES `produk_sku` (`id_sku`),
  ADD CONSTRAINT `komposisi_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`);

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_ibfk_1` FOREIGN KEY (`id_alamat`) REFERENCES `alamat` (`id_alamat`),
  ADD CONSTRAINT `pesan_ibfk_2` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`);

--
-- Constraints for table `pilihan_varian`
--
ALTER TABLE `pilihan_varian`
  ADD CONSTRAINT `pilihan_varian_ibfk_1` FOREIGN KEY (`id_varian`) REFERENCES `varian` (`id_varian`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `produk_sku`
--
ALTER TABLE `produk_sku`
  ADD CONSTRAINT `produk_sku_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`);

--
-- Constraints for table `produk_varian`
--
ALTER TABLE `produk_varian`
  ADD CONSTRAINT `produk_varian_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `produk_varian_ibfk_2` FOREIGN KEY (`id_varian`) REFERENCES `varian` (`id_varian`);

--
-- Constraints for table `sku_detail_pilihan`
--
ALTER TABLE `sku_detail_pilihan`
  ADD CONSTRAINT `sku_detail_pilihan_ibfk_1` FOREIGN KEY (`id_sku`) REFERENCES `produk_sku` (`id_sku`),
  ADD CONSTRAINT `sku_detail_pilihan_ibfk_2` FOREIGN KEY (`id_pilihan`) REFERENCES `pilihan_varian` (`id_pilihan`);

--
-- Constraints for table `staf`
--
ALTER TABLE `staf`
  ADD CONSTRAINT `staf_ibfk_1` FOREIGN KEY (`id_role_staf`) REFERENCES `role_staf` (`id_role_staf`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
