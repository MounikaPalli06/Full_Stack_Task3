# Full_Stack_Task3
Backend Development &amp; Database Integration
# User Management System - Complete Backend Documentation

A comprehensive **PHP & MySQL** user management system with authentication, CRUD operations, role-based access, and advanced security features.

## 📋 Project Overview

This system implements a full-featured user management application with:
- ✅ User Registration & Authentication
- ✅ CRUD Operations (Create, Read, Update, Delete)
- ✅ Role-Based Access Control (User/Admin)
- ✅ Password Hashing & Security
- ✅ SQL Injection Prevention (Prepared Statements)
- ✅ Profile Management & Picture Upload
- ✅ CSRF Protection
- ✅ Input Validation & Sanitization

---

## 🗄️ Database Design

### 1. ER Diagram
┌─────────────┐         ┌──────────────┐
│    ROLES    │         │    USERS     │
├─────────────┤         ├──────────────┤
│ role_id (PK)├────────→│ role_id (FK) │
│ role_name   │    1:N  │ user_id (PK) │
│ description │         │ username     │
└─────────────┘         │ email        │
                        │ password     │
                        │ first_name   │
                        │ last_name    │
                        │ profile_pic  │
                        │ bio          │
                        │ is_active    │
                        │ created_at   │
                        │ updated_at   │
                        └──────────────┘

### 2. Database Normalization

**First Normal Form (1NF):**
- ✅ All attributes are atomic (no repeating groups)
- ✅ Each column contains only a single value
- ✅ Example: username, email are individual columns (not combined)

**Second Normal Form (2NF):**
- ✅ All non-key attributes depend on the entire primary key
- ✅ No partial dependencies
- ✅ Example: user information depends on user_id, not on role_id alone

**Third Normal Form (3NF):**
- ✅ No transitive dependencies
- ✅ Role information is in a separate table
- ✅ Users table references roles table via foreign key
- ✅ Prevents data redundancy and maintains referential integrity

### 3. SQL Setup

## 🏗️ Project Structure

Task3/
├── auth/                          # Authentication system
│   ├── login.php                 # User login
│   ├── register.php              # User registration
│   └── logout.php                # Session termination
│
├── crud/                          # CRUD operations
│   ├── add_user.php              # Create user (Admin only)
│   ├── manage_users.php          # Read users (Admin only)
│   ├── edit_user.php             # Update user (Admin only)
│   ├── delete_user.php           # Delete user (Admin only)
│   └── admin_dashboard.php       # Admin dashboard
│
├── profile/                       # Profile management
│   └── edit_profile.php          # Edit user profile & upload picture
│
├── includes/                      # Core files
│   ├── config.php                # Database configuration
│   └── security.php              # Security functions
│
├── uploads/                       # Profile pictures storage
│
├── database/                      # Database schema
│   └── setup.sql                 # Database setup script
│
├── css/                          # Styling
│   └── style.css                # Bootstrap-like stylesheet
│
├── index.php                     # User dashboard
└── README.md                     # Documentation

---

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Step 1: Create Database
### Step 2: Configure Database Connection
### Step 3: Set Folder Permissions
### Step 4: Access Application
## 📝 Usage Guide

### 1. User Registration

**Form Fields:**
- Username (3+ characters, unique)
- Email (valid format, unique)
- First Name
- Last Name
- Password (8+ chars, uppercase, lowercase, number)
- Confirm Password

**Process:**
1. User fills registration form
2. Server validates all inputs
3. Password hashed with bcrypt
4. User created in database with 'user' role
5. Redirected to login page

### 2. User Login

**Form Fields:**
- Username
- Password

**Process:**
1. Fetch user from database
2. Verify password using password_verify()
3. Check if account is active
4. Create session
5. Redirect based on role (Admin → Admin Dashboard, User → Dashboard)

### 3. CRUD Operations (Admin Only)

### 4. Profile Management
**URL:** `/profile/edit_profile.php`

**Features:**
- ✅ Edit personal information
- ✅ Upload profile picture
- ✅ Write/edit bio
- ✅ Display current profile picture

**Image Upload:**
- Max size: 2MB
- Allowed formats: JPG, JPEG, PNG, GIF
- Unique filename: `profile_USER_ID_TIMESTAMP.ext`
- Stored in: `uploads/` directory

---

## 🎯 Role-Based Access Control

### User Roles
1. **Regular User**
   - View own profile
   - Edit own profile
   - Upload profile picture
   - View dashboard

2. **Admin**
   - All user features
   - Manage all users (Add, Edit, Delete)
   - View admin dashboard with statistics
   - Access control panel

### Default Roles
- Role ID 1: User
- Role ID 2: Admin

---

## 📚 Additional Features

### Optional Enhancements
1. **Email Verification** - Verify email on registration
2. **Password Reset** - Forgot password functionality
3. **Two-Factor Authentication** - 2FA support
4. **Activity Logging** - Track user actions
5. **Backup/Export** - Export user data as CSV
6. **Search & Filter** - Filter users in table
7. **Pagination** - Handle large user lists
8. **API Endpoints** - RESTful API for CRUD
