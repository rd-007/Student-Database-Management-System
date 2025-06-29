# Student CRUD App

A simple PHP + MySQL web application to create, read, update, and delete student records in real-time via fetch API.

## Setup

1. Import `schema.sql` into your MySQL server:

```bash
mysql -u root -p < schema.sql
```

2. Edit `db.php` with your MySQL credentials if needed.
3. Place the `student-crud-app` folder in your web server root (e.g., `htdocs` for XAMPP) or serve via built-in PHP server:

```bash
php -S localhost:8000 -t C:\Users\Rohit\CascadeProjects\student-crud-app
```

4. Open http://localhost:8000 in your browser and manage student records.

---
Built with Bootstrap 5, vanilla JS fetch, and PDO.
