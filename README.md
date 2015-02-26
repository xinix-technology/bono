Bono
====

[![License](http://img.shields.io/packagist/l/xinix-technology/bono.svg?style=flat-square)](https://github.com/xinix-technology/bono/blob/master/LICENSE)
[![Download](http://img.shields.io/packagist/dm/xinix-technology/bono.svg?style=flat-square)](https://github.com/xinix-technology/bono)
[![Version](http://img.shields.io/packagist/v/xinix-technology/bono.svg?style=flat-square)](https://github.com/xinix-technology/bono)

Bono is PHP framework

![Bono PHP](https://raw.githubusercontent.com/xinix-technology/bono/master/img/bono-logo.png "Bono PHP")

## Apa itu Bono?

Bono adalah framework aplikasi web berbasis PHP.  Pada dasarnya Bono dibangun di atas Slim Framework. Karena itu komponen utamanya adalah routing. Setiap halamannya memiliki representasi routing.


## Komponen

Bono memiliki memiliki dua elemen utama yaitu Provider dan Middleware.

### Provider

Provider berfungsi untuk menambahkan kemampuan Bono dalam menambahkan aplikasi ke dalam Bono. Sebagai contoh untuk menambahkan aplikasi chat, aplikasi CMS, aplikasi Forum dan lain sebagainya. Singkatnya setiap aplikasi di dalam Bono adalah provider.

### Middleware
Middleware untuk menambahkan fungsionalitas yang berhubungan dengan cara kerja sebuah aplikasi. Misalnya seperti penyimpanan data, penggunaan session, mengirim email, autentikasi dan autorisasi.

Ada satu middleware di dalam Bono yang berfungsi mengumpulkan routing-routing menjadi satu grup dari bisnis unit. Yang dalam konsep MVC dikenal sebagai kontroler.

Bono memiliki hook dan filter yang memudahkan penambahan fungsionalitas tiap-tiap middleware yang ada. Perbedaan antara hook dan filter adalah filter dapat mengembalikan nilai sementara hook tidak.

## Why Bono?
- Ridiculously fast in building application.
- CRUD can be done in a fart.
- Just type in your terminal, and let the system give what you need

## Templating pada Bono
Bono menyediakan hook dan filter pada template engine-nya. Sehingga memudahkan theme developer dalam mengembangkan desain yang diinginkan.

## Instalasi

Yang diperlukan untuk membangun aplikasi menggunakan Bono:
- Web Server (Apache, Nginx, LigHttpd, dll)
- PHP 5.4+ (dengan dukungan library MongoDB)
- MongoDB
- Xpax*

Xpax (Xinix Package) adalah package management system yang dikembangkan oleh Xinix. Xpax bisa diinstal dengan perintah berikut di Terminal Anda:

```
npm install -g xinix-pax.
```

> **Catatan** `-g` pada argumen npm install akan membuat modul npm diinstall secara global, ada kemungkinan perintah ini membutuhkan permission dari superuser, gunakan `sudo` jika perintah ini gagal dijalankan.

Untuk mendapatkan package npm bisa diinstall dari www.nodejs.org

### Mencicipi Bono secara kilat
- Melalui Terminal ketikkan perintah :
    + xpax init https://github.com/reekoheek/bono-arch
    + xpax serve
- Buka http://localhost:8000 melalui browser Anda

### Menjalankan aplikasi Bono di web server
- Buatlah sebuah direktori untuk aplikasi Anda di root folder web server Anda.
- Melalui Terminal masuk ke direktori tersebut, kemudian jalankan perintah:
    + xpax init https://github.com/reekoheek/bono-arch
- Aplikasi Bono sudah terinstal di folder yang dibuat sebelumnya.
- Buka http://localhost/{nama_folder}/www melalui web browser.
