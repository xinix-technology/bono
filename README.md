# Bono

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

## Kebutuhan Sistem

Yang diperlukan untuk membangun aplikasi menggunakan Bono:
- PHP 5.4+ (dengan dukungan library MongoDB)
- Composer
- Web Server (Apache, Nginx, LigHttpd, dll) or PHP Standalone
