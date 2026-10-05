# RAY Cars (Dealer Auto)

Aplicație web pentru vânzarea de mașini, realizată în PHP și MySQL: înregistrare/autentificare, listă de mașini, coș de cumpărături și finalizare comandă.

Imagine cu pagina de pornire
<img width="1421" height="765" alt="Screenshot 2026-10-06 at 02 01 58" src="https://github.com/user-attachments/assets/066cb460-638e-461a-82c5-8d98a0aba500" />

## Tehnologii
- PHP 
- MySQL 
- Bootstrap 4, Font Awesome
- Apache (XAMPP)
- HTML / CSS

## Structura bazei de date
Tabele: `utilizatori`, `clienti`, `masini`, `furnizor`, `dotari`, `masina_dotari`, `tranzactii`.

## Instalare locală

1. Instalează [XAMPP](https://www.apachefriends.org/) și pornește **Apache** și **MySQL**.
2. Clonează proiectul în `htdocs`:
```bash
   cd /Applications/XAMPP/xamppfiles/htdocs
   git clone https://github.com/USERNAME/dealer-auto.git demo
```
   (pe Windows: `C:\xampp\htdocs`)
3. Importă baza de date: deschide `http://localhost/phpmyadmin` → **Import** → alege `database/dealer_auto.sql`.
   *Atenție: scriptul șterge și recreează tabelele existente.*
4. Creează fișierul de configurare:
```bash
   cd demo
   cp config.example.php config.php
```
   Editează `config.php` și completează utilizatorul și parola MySQL (în XAMPP implicit: `root` și parolă goală).
5. Deschide `http://localhost/demo/home.php`.

## Cont de test
- Utilizator: `demo`
- Parolă: `demo1234`

Exemplu pagina pentru login a site-ului:

<img width="571" height="688" alt="Screenshot 2026-10-06 at 02 01 20" src="https://github.com/user-attachments/assets/9662dcc8-5d0e-4a33-a528-3a53457237d1" />
