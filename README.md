DukaSmart RMS – Retail Management System
A complete web‑based solution for small Kenyan shops to manage inventory, sales, customers, and reports.

 Table of Contents
- About the Project
- Features
- Tech Stack
- Requirements
- Installation
- Default Users
- Screenshots
- Project Structure
- Contributing
- License
- Contact

 About the Project
DukaSmart RMS is a final year diploma project developed to digitize daily operations of small retail shops in Kenya. It replaces manual paper‑based record keeping with an easy‑to‑use web application that works entirely offline. The system helps shop owners track inventory, process sales, manage customers, and generate insightful reports.

Developed by Wambui Flavian as part of the Diploma in Information Technology.

 Features

- User Authentication– Secure login for Admin and Cashier roles.
- Product Management– Add, edit, delete, and search products. Low‑stock alerts and colour‑coded stock status.
- Customer Management – Maintain customer profiles and purchase history.
- Point of Sale (POS)– Interactive cart, quantity adjustments, multiple payment methods (Cash, M‑Pesa, Card). Automatically updates stock on checkout.
- Sales History– View all past sales with customer and cashier details.
- Receipt Printing – Printable receipt page for each sale.
- Dashboard – Real‑time statistics: today’s sales, total products, low‑stock count, out‑of‑stock items, and recent sales.
- Reports– Daily sales summary, low‑stock report, best‑selling products with date filtering.
- Offline First– Runs on a local server; no internet required.

 Tech Stack
Component 	| Technology    
Frontend  	HTML5, CSS3, JavaScript
Backend	PHP 8.x 
Database  	MySQL 8.x 
Server 	Apache (XAMPP) 
 Tools  	phpMyAdmin, Git, VS Code

Requirements
- XAMPP (or any Apache + PHP + MySQL stack)  
- Web browser(Chrome, Edge, etc.)  
- Git (optional, for cloning)






Installation
Follow these steps to set up DukaSmart RMS on your local machine.
1. Clone or download the project
bash
git clone https://github.com/wambuiflavy/dukasmart_rms.git
Or download the ZIP and extract it into your XAMPP `htdocs` folder.
 2. Move project to htdocs
Make sure the folder `dukasmart_rms` is inside `C:\xampp\htdocs\` (or your XAMPP installation path). 3. Start Apache and MySQL
Open XAMPP Control Panel and start  Apache and MySQL.
4. Create the database
- Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)  
- Create a new database named `dukasmart_db`.  
- Import the SQL file located at `/database/dukasmart_db.sql` (if included).  
  Alternatively, run the SQL statements from the documentation to create tables and insert sample data.
 5. Configure database connection
Copy `config.example.php` to `config.php` (if provided) and update the database credentials if needed. The default settings are:

php
$host = 'localhost';
$dbname = 'dukasmart_db';
$username = 'root';
$password = '';

 6. Access the application
Open your browser and go to:

http://localhost/dukasmart_rms/login.php


 Default Users
After installation, you can log in with the following test credentials:

Role	Username	Password 
Admin	admin	admin123
Cashier 	cashier	cashier123
Manager	manager 	manager123


*Passwords are currently stored in plain text for development; will be updated with hashing later.*


















Project Structure

dukasmart_rms/
├── config.php
├── login.php
├── logout.php
├── dashboard.php
├── products.php
├── add_product.php
├── edit_product.php
├── delete_product.php
├── customers.php
├── add_customer.php
├── edit_customer.php
├── delete_customer.php
├── pos.php
├── sales.php
├── receipt.php
├── reports.php
├── style.css
├── database/
│   └── dukasmart_db.sql
└── README.md


 Contributing
This is a student project and is not open for external contributions, but feedback and suggestions are welcome.

 License
This project is for educational purposes only. 
All rights reserved © 2026 Wambui Flavian.

 Contact
- Developer: Wambui Flavian  
- Email:wambuiflavy@gmail.com
- GitHub: github.com/ wambuiflavy-jpg  https://github.com/wambuiflavy-jpg

Thank you for checking out DukaSmart RMS DukaSmart RMS – Retail Management System
