<?php
// ============================================================
// Konfigurasi Rekening Bank Resmi - RentAMobil
// ============================================================
// Petunjuk untuk Pengembang / Admin:
// Isi nilai 'nomor_rekening' dan 'atas_nama' sesuai rekening resmi
// RentAMobil sebelum aplikasi digunakan di lingkungan produksi.
// Selama 'nomor_rekening' masih kosong (''), halaman pembayaran
// tidak akan menampilkan nomor rekening dan akan memberi tahu
// pengguna bahwa informasi rekening belum dikonfigurasi.
// ============================================================

return [
    'Transfer BCA' => [
        'kode'           => 'BCA',
        'nama_metode'    => 'Transfer BCA',
        'nama_bank'      => 'Bank Central Asia (BCA)',
        'nomor_rekening' => '', // Contoh pengisian oleh admin: isi nomor rekening resmi BCA
        'atas_nama'      => '', // Contoh pengisian oleh admin: isi nama pemilik rekening resmi
        'instruksi'      => [
            'Buka aplikasi m-BCA / myBCA / KlikBCA atau kunjungi ATM BCA terdekat.',
            'Pilih menu Transfer ke sesama rekening BCA dan masukkan nomor rekening tujuan.',
            'Masukkan nominal transfer sesuai persis dengan Total Tagihan pesanan Anda.',
            'Simpan bukti transfer (screenshot atau foto struk) lalu unggah pada formulir di bawah ini.',
        ],
    ],
    'Transfer Mandiri' => [
        'kode'           => 'Mandiri',
        'nama_metode'    => 'Transfer Mandiri',
        'nama_bank'      => 'Bank Mandiri',
        'nomor_rekening' => '', // Isi nomor rekening resmi Bank Mandiri
        'atas_nama'      => '', // Isi nama pemilik rekening resmi
        'instruksi'      => [
            'Buka aplikasi Livin\' by Mandiri atau kunjungi ATM Mandiri terdekat.',
            'Pilih menu Transfer ke sesama rekening Mandiri dan masukkan nomor rekening tujuan.',
            'Masukkan nominal transfer sesuai persis dengan Total Tagihan pesanan Anda.',
            'Simpan bukti transfer (screenshot atau foto struk) lalu unggah pada formulir di bawah ini.',
        ],
    ],
    'Transfer BRI' => [
        'kode'           => 'BRI',
        'nama_metode'    => 'Transfer BRI',
        'nama_bank'      => 'Bank Rakyat Indonesia (BRI)',
        'nomor_rekening' => '', // Isi nomor rekening resmi Bank BRI
        'atas_nama'      => '', // Isi nama pemilik rekening resmi
        'instruksi'      => [
            'Buka aplikasi BRImo atau kunjungi ATM BRI terdekat.',
            'Pilih menu Transfer ke sesama rekening BRI dan masukkan nomor rekening tujuan.',
            'Masukkan nominal transfer sesuai persis dengan Total Tagihan pesanan Anda.',
            'Simpan bukti transfer (screenshot atau foto struk) lalu unggah pada formulir di bawah ini.',
        ],
    ],
];
