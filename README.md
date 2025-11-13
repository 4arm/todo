PHP/MariaDB To-Do List Application

This is a simple, single-file To-Do list application built using the classic LAMP Stack (Linux, Apache, MariaDB, PHP) and styled with Tailwind CSS for a modern, responsive look.

The application is designed to be deployed directly on an Ubuntu/Debian Google Cloud VM running the Apache2 web server.

Prerequisites

A running Google Cloud VM instance (Ubuntu/Debian recommended).

SSH access to the VM.

The VM's firewall must allow incoming TCP traffic on port 80 (HTTP).

1. Server Setup (Installation)

Run the following commands via SSH to install the necessary components: Apache2, MariaDB, and PHP with the required database extensions.

# 1. Update the package index
sudo apt update

# 2. Install Apache Web Server
sudo apt install apache2 -y

# 3. Install MariaDB Server and Client
sudo apt install mariadb-server mariadb-client -y

# 4. Install PHP and necessary modules for Apache and MariaDB/MySQL
sudo apt install php libapache2-mod-php php-mysql -y

# 5. Restart Apache to load the new PHP module
sudo systemctl restart apache2


2. Database Creation and Security

After installation, secure MariaDB and create the database and user required by the application.

A. Secure MariaDB

Run the MariaDB security script (highly recommended):

sudo mysql_secure_installation


Follow the prompts to set a strong root password and remove insecure defaults.

B. Create Database and User

Log into the MariaDB shell and execute the SQL commands.

Log in:

sudo mariadb


Run the following SQL:

NOTE: The user password set here is Easeham123. You must use this same password in the index.php configuration in the next step.

-- 1. Create the database
CREATE DATABASE todo_app;

-- 2. Create a dedicated database user
CREATE USER 'todo_user'@'localhost' IDENTIFIED BY 'Easeham123';

-- 3. Grant the user privileges on the new database
GRANT ALL PRIVILEGES ON todo_app.* TO 'todo_user'@'localhost';

-- 4. Create the 'todos' table structure
USE todo_app;
CREATE TABLE todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_completed BOOLEAN DEFAULT FALSE
);

-- 5. Apply the changes and exit
FLUSH PRIVILEGES;
EXIT;


3. Application Configuration and Deployment

Configure Database Credentials:
Edit the index.php file and update the DB_PASS constant to match the password you set in the SQL step (Easeham123).

// In index.php:
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_app');
define('DB_USER', 'todo_user');
define('DB_PASS', 'Easeham123'); // <-- UPDATED PASSWORD


Deploy the File:
Copy the configured index.php file to your Apache web root. This is usually /var/www/html/.

For example, using SCP from your local machine (replace your-vm-ip):

scp /path/to/index.php username@your-vm-ip:/var/www/html/


Or, if you are logged in via SSH, create the file:

sudo nano /var/www/html/index.php
# ... paste contents of index.php and save (Ctrl+X, Y, Enter)


4. Usage

After deployment, open your web browser and navigate to your VM's public IP address:

http://[YOUR_VM_PUBLIC_IP]


The application will now allow you to add, mark as complete/pending, and delete tasks, with all data persisting in the MariaDB database.
