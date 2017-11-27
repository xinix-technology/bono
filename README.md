# Bono

[![License](http://img.shields.io/packagist/l/xinix-technology/bono.svg?style=flat-square)](https://github.com/xinix-technology/bono/blob/master/LICENSE)
[![Download](http://img.shields.io/packagist/dm/xinix-technology/bono.svg?style=flat-square)](https://github.com/xinix-technology/bono)
[![Version](http://img.shields.io/packagist/v/xinix-technology/bono.svg?style=flat-square)](https://github.com/xinix-technology/bono)

Bono, Sebuah Framework PHP

![Bono PHP](https://raw.githubusercontent.com/xinix-technology/bono/master/img/bono-logo.png "Bono PHP")

## Apa itu Bono?

Bono V2 adalah framework aplikasi web berbasis PHP. Bono versi 2 ini telah di-reengineering dan tidak lagi berbasis Slim Framework dan telah comply dengan standar PSR-7. Komponen utama Bono adalah routing. Setiap halamannya memiliki representasi routing.

## Terminologi

Bono memiliki memiliki dua elemen utama yaitu Middleware dan Bundle.

### Middleware

Middleware dapat menambahkan kemampuan Request Handling. Contoh: session, autentikasi, dll.

Sejak versi 2.0, Bono menghilangkan Provider. Fungsionalitas yang biasanya diimplementasi di Bono 1.0 di Provider dialihkan ke implementasi menggunakan Middleware.

### Bundle

Sejak versi 2.0, Bono memperkenalkan konsep Bundle. Bundle berlaku seperti layaknya sub aplikasi. Bahkan aplikasi utama pada dasarnya di-compose dalam wujud Bundle.

Bundle juga menggantikan keberadaan Controller pada Bono 1.0.
Bundle dapat menambahkan kemampuan pada aplikasi berbasis Bono. Contoh: modul chat, mesin CMS dan blog, forum dan komunitas, dll.

### Routing

Routing adalah kemampuan pada Bono untuk mendefinisikan pengalihan Request untuk ditangani oleh spesifik fungsi berdasarkan pola URI.

## Mengapa menggunakan Bono?

- Sangat cepat dalam membangun aplikasi web.
- Operasi Search, Create, Read, Update dan Delete dapat dilakukan dengan scaffolding.

## Templating pada Bono

Bono V2 tidak menyediakan default templating. Tapi kamu bisa menggunakan Templating engine yang tersedia secara umum dengan mudah dengan sedikit glue-code atau menggunakan basic [T Template](https://github.com/reekoheek/t).

## Kebutuhan Sistem

Yang diperlukan untuk membangun aplikasi menggunakan Bono:

- PHP 5.4+
- Composer
- Web Server (Apache, Nginx, LigHttpd, dll) or PHP Standalone
