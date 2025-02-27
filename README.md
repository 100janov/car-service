# Car Service System

## Overview
The **Car Service System** is a web-based application built using **PHP** and **Bootstrap** to log and manage cars coming in for service. The system allows **service shop owner** to track service history, customer details, and vehicle information efficiently.

## Features
- **Vehicle Logging:** Register and track cars coming in for service.
- **Customer Management:** Store customer information and link it to vehicle records.
- **Service Records:** Maintain logs of each service performed on a vehicle.
- **Responsive UI:** Built with **Bootstrap** for a clean and modern user experience.
- **User Authentication:** Secure login system for **service owners**.
- **Search & Filter:** Easily find records using search and filter options.
- **Reports & Analytics:** Generate service reports for better insights.

## Technologies Used
- **PHP** (Backend processing)
- **Bootstrap** (Frontend framework)
- **MySQL** (Database management)
- **HTML, CSS, JavaScript** (Frontend components)

## Installation Guide
### Prerequisites
- **PHP**
- **MySQL Server**
- **Apache or any compatible web server**
- **Composer** (for dependency management, if applicable)

### Steps to Install
1. **Clone the repository:**
   ```bash
   git clone https://github.com/100janov/car-service.git
   ```
2. **Navigate to the project directory:**
   ```bash
   cd car-service
   ```
3. **Import the database:**
   - Locate the `database.sql` file in the project.
   - Import it into MySQL using **phpMyAdmin** or the MySQL CLI.
4. **Configure database connection:**
   - Open `config.php` (or equivalent configuration file).
   - Update the database credentials as per your setup.
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'car_service_db');
   ```
5. **Start the server:**
   ```bash
   php -S localhost:8000
   ```
6. **Access the application via:**
   ```
   http://localhost:8000
   ```

## Usage
1. **Login/Register** as a **service owner**.
2. **Add a new vehicle** by entering the necessary details.
3. **Log a service record** whenever a vehicle is serviced.
4. **View and manage records** using **search and filter** options.
5. **Generate reports** for **service tracking**.

## License
This project is licensed under the **MIT License**. See `LICENSE` for details.

## Contact
For any inquiries or support, reach out to **[your email/contact info]**.
