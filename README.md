# BarberHub - Luxury Grooming Platform

BarberHub is a premium booking platform designed for elite barber salons in Morocco. It connects high-end barbers with customers looking for a luxury grooming experience.

## 🚀 Features

### For Barbers
- **Professional Profile:** Showcase salon details, experience, and bio.
- **Service Management:** Add, edit, and delete grooming services with custom pricing and duration.
- **Booking Management:** Real-time dashboard to accept or reject appointments.
- **Auto-Accept System:** Pending bookings are automatically accepted if less than 1 hour remains until the appointment.
- **GPS Integration:** Set salon location using Google Maps/GPS coordinates.

### For Customers
- **Elite Search:** Filter barbers by city, service type, rating, and price.
- **Intelligent Booking:** Select multiple services; the system automatically calculates the total duration and price.
- **Review System:** Rate and comment on services with image upload support.
- **Anti-Spam Protection:** Automated account suspension and blacklisting after 5 "no-show" instances.

### For Administrators
- **Full Control:** Manage all users and barbers.
- **Verification System:** Approve or reject new barber registrations based on uploaded credentials.
- **Blacklist Management:** Monitor and manage banned accounts to maintain platform integrity.

---

## 🛠️ Tech Stack
- **Frontend:** HTML5, CSS3 (Luxury Theme), JavaScript (ES6+), Bootstrap 5.
- **Backend:** PHP 8 (OOP Architecture).
- **Database:** MySQL.
- **Tools:** PDO for secure database connections, Sessions for authentication.

---

## 📂 Project Structure

```text
/
├── admin/              # Admin dashboard and management pages
├── auth/               # Login, Register, and Logout logic
├── barber/             # Barber dashboard and service management
├── customer/           # Search, barber profile, and booking system
├── classes/            # Core OOP logic (User, Barber, Customer, etc.)
├── config/             # Database connection configuration
├── includes/           # Shared components (Header, Footer)
├── uploads/            # Media storage (Profiles, Salons, Diplomas)
├── assets/             # CSS, JS, and Images
└── database.sql        # MySQL schema
```

---

## 🧠 Key Logic Explanation

### 1. Authentication & Sessions
Managed by the `User` class. It uses `password_hash` and `password_verify` for security. Sessions store user role and ID to restrict access to specific dashboards.

### 2. Booking Time Calculation
In `barber_view.php`, JavaScript calculates the total duration of selected services. When booked, the system sets the `start_time` and calculates the `end_time` by adding the total duration to ensure no overlap for the next customer.

### 3. Anti-Spam System
The `Customer` class contains `banIfNecessary()`. When a barber marks an appointment as "No-Show", the customer's count increases. Upon reaching 5 counts, the account is deleted, and the email is added to the `blacklist` table.

### 4. Admin Approval
Barbers are registered with a `pending` status. They cannot log in until the Admin reviews their "Diploma/Video" and changes their status to `active` via the Admin Dashboard.

### 5. Google Maps Integration
Uses the Browser Geolocation API to capture `latitude` and `longitude` during barber registration, which is then used to generate direct Google Maps links on the barber's profile.

---

## 🔧 Installation
1. Clone the repository to your local server (e.g., XAMPP/WAMP).
2. Import `database.sql` into your MySQL server.
3. Update `config/db.php` with your database credentials.
4. Default Admin Login:
   - **Email:** `admin@barberhub.com`
   - **Password:** `Admin_00393690`

---

Developed by **Gemini CLI** for BarberHub.
