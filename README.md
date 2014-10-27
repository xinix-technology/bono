Bono
====

Bono is PHP framework

![Bono PHP](https://raw.githubusercontent.com/xinix-technology/bono/master/img/bono-logo.png "Bono PHP")

## Apa itu Bono?

Bono adalah framework aplikasi web berbasis PHP.  Pada dasarnya Bono dibangun di atas Slim Framework. Karena itu komponen utamanya adalah routing. Setiap halamannya memiliki representasi routing. 

Bono memiliki memiliki dua elemen utama yaitu Provider dan Middleware.

Provider berfungsi untuk menambahkan kemampuan Bono dalam menambahkan aplikasi ke dalam Bono. Sebagai contoh untuk menambahkan aplikasi chat, aplikasi CMS, aplikasi Forum dan lain sebagainya. Singkatnya setiap aplikasi di dalam Bono adalah provider.

Middleware untuk menambahkan fungsionalitas yang berhubungan dengan cara kerja sebuah aplikasi. Misalnya seperti penyimpanan data, penggunaan session, mengirim email, autentikasi dan autorisasi.

Ada satu middleware di dalam Bono yang berfungsi mengumpulkan routing-routing menjadi satu grup dari bisnis unit. Yang dalam konsep MVC dikenal sebagai kontroler.

Bono memiliki hook dan filter yang memudahkan penambahan fungsionalitas tiap-tiap middleware yang ada. Perbedaan antara hook dan filter adalah filter dapat mengembalikan nilai sementara hook tidak.

## Why Bono?
- Ridiculously fast in building application.
- CRUD can be done in a fart.
- Just type in your terminal, and let the system give what you need

## Templating pada Bono
Bono menyediakan hook dan filter pada template engine-nya. Sehingga memudahkan theme developer dalam mengembangkan desain yang diinginkan.

## Apa yang diperlukan untuk membangun aplikasi menggunakan Bono
- Web Server (Apache, Nginx, dll)
- PHP 5.3+ (with MongoDB lib)
- MongoDB
- Xpax*

**Xpax (Xinix Package) adalah package management system yang dikembangkan oleh Xinix. Xpax bisa diinstal dengan perintah npm install -g xinix-pax di Terminal Anda.
** Untuk mendapatkan package npm bisa diinstall dari www.nodejs.org

## Mulai membangun bersama Bono
Mencicipi Bono secara kilat
- Melalui Terminal ketikkan perintah :
    + xpax init bono-arch
    + xpax serve
- Buka http://localhost:8000 melalui browser Anda

Menjalankan aplikasi Bono di web server
- Buatlah sebuah direktori untuk aplikasi Anda di root folder web server Anda.
- Melalui Terminal masuk ke direktori tersebut, kemudian jalankan perintah:
    + xpax init bono-arch
- Aplikasi Bono sudah terinstal di folder yang dibuat sebelumnya.
- Buka http://localhost/nama_folder melalui web browser.
