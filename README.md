# Camotes Island Barber Booking System

A full-stack web application designed for a barbershop located in Camotes Island. It provides seamless appointment booking, user authentication, and a dashboard for barbers and administrators.

## Features
- **User Authentication:** Secure login and registration system with role-based access.
- **Roles:** Customer, Barber, and Admin.
- **Booking System:** Customers can easily book appointments for haircuts, beard trims, and styling.
- **Service & Barber View:** View available services and skilled barbers.
- **Dashboard:** Specialized views based on user roles (Admin vs. Barber).

## Technologies Used
- **Frontend:** HTML5, CSS3 (Glassmorphism design), Vanilla JavaScript
- **Backend:** PHP (using PDO for secure database interactions)
- **Database:** MySQL

## Installation & Setup
1. Clone this repository: 
   ```bash
   git clone https://github.com/SmileyLouie/barber-booking-system.git
   ```
2. Move the project folder to your local server directory (e.g., `htdocs` for XAMPP).
3. Start **Apache** and **MySQL** from your XAMPP control panel.
4. Open your browser and go to phpMyAdmin (`http://localhost/phpmyadmin`), then create a database named `barbershop_db`.
5. Import the `database.sql` file (found in the root directory) into the `barbershop_db` database.
6. Check your `api/db.php` file and ensure the database credentials match your local setup.
7. Open your browser and navigate to the project: `http://localhost/barber/`.

## Default Test Accounts
After importing the database, the following accounts are available for testing purposes:

- **Admin Account:**
  - Email: `admin@camotes.com`
  - Password: `admin`

- **Barber Account:**
  - Email: `marcus@camotes.com`
  - Password: `admin`
