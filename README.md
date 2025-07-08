# SentrySMP Server Documentation

[![Server Status](https://img.shields.io/badge/Server-Online-brightgreen)](https://mc.sentrysmp.eu)
[![Minecraft Version](https://img.shields.io/badge/Minecraft-1.20+-blue)](https://minecraft.net)
[![Website](https://img.shields.io/badge/Website-sentrysmp.eu-orange)](https://sentrysmp.eu)

## 📋 Table of Contents

- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Installation & Setup](#installation--setup)
- [Configuration](#configuration)
- [Features](#features)
- [File Structure](#file-structure)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [Payment Integration](#payment-integration)
- [RCON Integration](#rcon-integration)
- [Admin Panel](#admin-panel)
- [Security](#security)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

## 🎯 Overview

SentrySMP is a comprehensive Minecraft server management system featuring:
- **E-commerce Integration**: Complete shop system with Stripe and PayPal
- **Player Management**: User authentication and VIP system
- **Real-time Server Monitoring**: Live player count and server status
- **Admin Dashboard**: Complete administrative interface
- **RCON Integration**: Remote server command execution
- **Automated Systems**: VIP expiration management and cleanup

### Key Technologies
- **Backend**: PHP 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: SQLite
- **Payment**: Stripe API, PayPal
- **Server Communication**: RCON Protocol
- **Dependencies**: Composer (PHP package manager)

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    SentrySMP Architecture                   │
├─────────────────────────────────────────────────────────────┤
│  Frontend (Web Interface)                                  │
│  ├── User Pages (spawners.php, keys.php, ranks.php)       │
│  ├── Shopping Cart (cart.html, checkout.php)              │
│  ├── Authentication (login-players.php)                   │
│  └── Admin Panel (admin.php, vip_manager.php)             │
├─────────────────────────────────────────────────────────────┤
│  Backend Services                                          │
│  ├── Payment Processing (Stripe, PayPal)                  │
│  ├── RCON Communication (Minecraft Server)                │
│  ├── Database Management (SQLite)                         │
│  ├── Session Management                                    │
│  └── Automated Cleanup (VIP expiration)                   │
├─────────────────────────────────────────────────────────────┤
│  External Integrations                                     │
│  ├── Minecraft Server (RCON)                              │
│  ├── Stripe API                                           │
│  ├── PayPal API                                           │
│  ├── Discord API                                          │
│  └── MCStatus API                                         │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- Composer
- SQLite support
- cURL extension
- Web server (Apache/Nginx)

### 1. Clone Repository
```bash
git clone https://github.com/toby-gamez/sentrysmp.git
cd sentrysmp
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
Create `.env` file with your credentials:
```env
# Stripe Configuration
STRIPE_SECRET_KEY=sk_live_your_stripe_secret_key
STRIPE_PUBLISHABLE_KEY=pk_live_your_stripe_publishable_key

# RCON Configuration (Minecraft Server)
RCON_HOST=your_minecraft_server_ip
RCON_PORT=25575
RCON_PASSWORD=your_rcon_password

# Discord Configuration
DISCORD_BOT_TOKEN=your_discord_bot_token
DISCORD_GUILD_ID=your_discord_guild_id

# PayPal Configuration
PAYPAL_CLIENT_ID=your_paypal_client_id

# Admin Credentials
ADMIN_USERNAME_1=webdev
ADMIN_PASSWORD_1=secure_password_1
ADMIN_USERNAME_2=owner
ADMIN_PASSWORD_2=secure_password_2
ADMIN_USERNAME_3=pepeno01
ADMIN_PASSWORD_3=secure_password_3
```

### 4. Database Setup
The system automatically creates SQLite databases on first run:
```bash
php create_db.php
php create_spawners_table.php
php create_table.php
```

### 5. Web Server Configuration
#### Apache (.htaccess included)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

## ⚙️ Configuration

### Environment Variables

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `STRIPE_SECRET_KEY` | Stripe API secret key | Yes | - |
| `STRIPE_PUBLISHABLE_KEY` | Stripe API publishable key | Yes | - |
| `RCON_HOST` | Minecraft server IP | Yes | - |
| `RCON_PORT` | RCON port | Yes | 25575 |
| `RCON_PASSWORD` | RCON password | Yes | - |
| `DISCORD_BOT_TOKEN` | Discord bot token | No | - |
| `DISCORD_GUILD_ID` | Discord server ID | No | - |
| `PAYPAL_CLIENT_ID` | PayPal client ID | Yes | - |
| `ADMIN_USERNAME_1` | First admin username | Yes | - |
| `ADMIN_PASSWORD_1` | First admin password | Yes | - |
| `ADMIN_USERNAME_2` | Second admin username | Yes | - |
| `ADMIN_PASSWORD_2` | Second admin password | Yes | - |
| `ADMIN_USERNAME_3` | Third admin username | Yes | - |
| `ADMIN_PASSWORD_3` | Third admin password | Yes | - |

### Server Settings
Edit these files for server-specific configuration:
- `player_count.php` - Server status API endpoint
- `discord.php` - Discord guild configuration
- Database connection strings in PHP files

## 🎮 Features

### 🛒 E-commerce System
- **Multi-item Shopping Cart**: Add spawners, keys, and ranks
- **Dynamic Pricing**: Quantity-based discounts
- **Payment Processing**: Stripe and PayPal integration
- **Order Management**: Complete transaction tracking

### 👤 User Management
- **Player Authentication**: Minecraft username-based login
- **Session Management**: Secure PHP sessions
- **VIP System**: Automated 30-day VIP memberships
- **Permission Management**: RCON-based rank assignment

### 🎯 Shop Categories

#### Spawners (`spawners.php`)
- Mob spawners with custom prices
- Quantity-based purchasing
- Automatic RCON delivery

#### Keys (`keys.php`)
- Treasure chest keys
- Special reward keys
- Custom commands execution

#### Ranks (`ranks.php`)
- VIP memberships
- Special permissions
- Temporary rank assignments

### 📊 Admin Features
- **VIP Manager**: View and manage VIP users
- **Database Editor**: Direct database manipulation
- **Command Executor**: RCON command interface
- **Analytics**: Payment and user tracking

### 🔧 Automation
- **VIP Cleanup**: Automatic expiration handling
- **Permission Sync**: RCON permission updates
- **Logging**: Comprehensive system logging

## 📁 File Structure

```
sentrysmp/
├── 📂 css/
│   └── style.css                 # Main stylesheet
├── 📂 js/
│   ├── script.js                 # Main JavaScript
│   └── cart-handler.js           # Shopping cart logic
├── 📂 images/                    # Static assets
├── 📂 vendor/                    # Composer dependencies
├── 📂 backup/                    # Backup files
│
├── 🏠 Frontend Pages
│   ├── index.php                 # Homepage with VIP cleanup
│   ├── spawners.php              # Spawner shop
│   ├── keys.php                  # Keys shop
│   ├── ranks.php                 # Ranks shop
│   ├── cart.html                 # Shopping cart
│   ├── checkout.php              # Checkout page
│   ├── about.html                # About page
│   ├── join.html                 # How to join
│   ├── rules.html                # Server rules
│   ├── support.html              # Support page
│   └── vote.html                 # Voting page
│
├── 🔐 Authentication
│   ├── login-players.php         # Player login
│   ├── login-status-players.php  # Login status check
│   ├── logout-players.php        # Player logout
│   └── save_username.php         # Username validation
│
├── 💳 Payment System
│   ├── create-checkout-session.php  # Stripe session creation
│   ├── success.php               # Payment success handler
│   └── paypal-checkout.php       # PayPal integration
│
├── 🎮 RCON Integration
│   ├── cart-rcon.php             # Cart RCON commands
│   ├── vip-rcon.php              # VIP RCON functions
│   └── vip-send_rcon.php         # VIP command sender
│
├── 👑 Admin Panel
│   ├── admin.php                 # Main admin dashboard
│   ├── login.php                 # Admin login
│   ├── vip_manager.php           # VIP user management
│   ├── vip-list.php              # VIP user listing
│   ├── paid-list.php             # Payment history
│   ├── edit_spawners.php         # Spawner editor
│   ├── edit_keys.php             # Key editor
│   └── edit_ranks.php            # Rank editor
│
├── 📊 APIs & Services
│   ├── player_count.php          # Server status API
│   ├── discord.php               # Discord integration
│   ├── get_announcements.php     # Announcements API
│   └── get_commands.php          # Command execution API
│
├── 🗄️ Database Files
│   ├── blog.sqlite               # Spawners database
│   ├── keys.sqlite               # Keys database
│   ├── ranks.sqlite              # Ranks database
│   ├── vip.sqlite                # VIP users database
│   ├── paid_users.sqlite         # Payment records
│   └── team_content.sqlite       # Team information
│
├── ⚙️ Configuration
│   ├── .env                      # Environment variables
│   ├── .env.example              # Environment template
│   ├── composer.json             # PHP dependencies
│   ├── .gitignore                # Git ignore rules
│   └── .htaccess                 # Apache configuration
│
└── 📚 Documentation
    └── README.md                 # This file
```

## 🔌 API Documentation

### Player Count API
**Endpoint**: `GET /player_count.php`

**Response**:
```json
{
  "status": "success",
  "players": 12
}
```

### Discord Integration
**Endpoint**: `GET /discord.php`

**Response**:
```json
{
  "member_count": 1234,
  "online_count": 56
}
```

### Announcements API
**Endpoint**: `GET /get_announcements.php`

**Response**:
```json
[
  {
    "id": 1,
    "title": "Server Update",
    "content": "New features added!",
    "created_at": "2024-01-15 10:30:00"
  }
]
```

### Payment Success Webhook
**Endpoint**: `POST /success.php`

**Parameters**:
- `session_id` - Stripe session ID
- `transaction_id` - PayPal transaction ID
- `username` - Player username
- `cart` - JSON cart data

## 🗄️ Database Schema

### Spawners Table (`blog.sqlite`)
```sql
CREATE TABLE spawners (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    image TEXT,
    command TEXT
);
```

### Keys Table (`keys.sqlite`)
```sql
CREATE TABLE Keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    value TEXT NOT NULL,
    image TEXT,
    prikaz TEXT,
    price INTEGER DEFAULT 3
);
```

### Ranks Table (`ranks.sqlite`)
```sql
CREATE TABLE ranks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    duration INTEGER DEFAULT 30,
    permissions TEXT
);
```

### VIP Users Table (`vip.sqlite`)
```sql
CREATE TABLE vip_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    payment_id TEXT
);
```

### Paid Users Table (`paid_users.sqlite`)
```sql
CREATE TABLE paid_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    transaction_id TEXT UNIQUE,
    amount REAL NOT NULL,
    payment_method TEXT,
    cart_data TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 💳 Payment Integration

### Stripe Integration
The system uses Stripe Checkout for secure payment processing:

1. **Session Creation** (`create-checkout-session.php`):
   - Validates cart data
   - Creates Stripe checkout session
   - Returns checkout URL

2. **Payment Success** (`success.php`):
   - Verifies payment completion
   - Processes RCON commands
   - Updates user records

3. **Security Features**:
   - Environment variable configuration
   - Webhook signature verification
   - SQL injection prevention

### PayPal Integration
PayPal payments are handled through their JavaScript SDK:

1. **Client-side Integration** (`paypal-checkout.php`):
   - PayPal buttons initialization
   - Order creation and capture
   - Success/error handling
   - Environment variable configuration

2. **Server-side Processing**:
   - Order verification
   - Payment confirmation
   - User account updates

## 🎮 RCON Integration

### Connection Management
```php
// RCON credentials from environment
$host = $_ENV["RCON_HOST"];
$port = (int) $_ENV["RCON_PORT"];
$password = $_ENV["RCON_PASSWORD"];

// Create connection
$rcon = new Rcon($host, $port, $password, $timeout);
```

### Command Execution
The system supports various RCON commands:

#### VIP Permission Management
```php
// Grant VIP permissions
$command = "lp user {$username} parent set vip";
$rcon->sendCommand($command);

// Remove VIP permissions
$command = "lp user {$username} clear";
$rcon->sendCommand($command);
```

#### Item Delivery
```php
// Give spawner to player
$command = "give {$username} spawner 1";
$rcon->sendCommand($command);
```

### Error Handling
- Connection timeout management
- Command execution logging
- Automatic retry mechanisms

## 👑 Admin Panel

### Authentication
Admin access requires secure login:
```php
session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php?error=auth");
    exit();
}
```

### Admin Features

#### VIP Manager (`vip_manager.php`)
- View all VIP users
- Check expiration dates
- Manual user removal
- RCON permission sync

#### Database Editors
- **Spawners**: Add/edit/delete spawners
- **Keys**: Manage treasure keys
- **Ranks**: Configure rank packages

#### Command Executor
- Direct RCON command execution
- Command history logging
- Response monitoring

## 🔒 Security

### Environment Variables
All sensitive data is stored in environment variables:
```env
# Never commit these to version control
STRIPE_SECRET_KEY=sk_live_...
PAYPAL_CLIENT_ID=your_paypal_client_id
RCON_PASSWORD=secure_password
ADMIN_PASSWORD_1=secure_admin_password
ADMIN_PASSWORD_2=secure_admin_password
ADMIN_PASSWORD_3=secure_admin_password
```

### Input Validation
```php
// Username validation
if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $username)) {
    throw new Exception("Invalid username format");
}

// SQL injection prevention
$stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindValue(":username", $username, SQLITE3_TEXT);
```

### Session Security
```php
// Secure session configuration
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict'
]);
```

### CSRF Protection
All forms include CSRF tokens for protection against cross-site request forgery.

## 🚀 Deployment

### Production Setup

1. **Server Requirements**:
   - PHP 8.0+ with extensions: PDO, SQLite, cURL, JSON
   - Web server (Apache/Nginx)
   - SSL certificate (required for payments)

2. **Environment Configuration**:
   ```bash
   # Set production environment variables
   export STRIPE_SECRET_KEY="sk_live_..."
   export RCON_HOST="your_minecraft_server"
   export RCON_PASSWORD="secure_password"
   ```

3. **Database Optimization**:
   ```bash
   # Optimize SQLite databases
   sqlite3 blog.sqlite "VACUUM;"
   sqlite3 vip.sqlite "VACUUM;"
   ```

4. **Cron Jobs**:
   ```bash
   # Add to crontab for automated cleanup
   0 */6 * * * /usr/bin/php /path/to/sentrysmp/auto_cleanup.php
   ```

### SSL Configuration
```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## 🔧 Troubleshooting

### Common Issues

#### Payment Failures
```bash
# Check Stripe logs
tail -f /var/log/apache2/error.log | grep "stripe"

# Verify environment variables
php -r "require 'vendor/autoload.php'; \$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__); \$dotenv->load(); echo \$_ENV['STRIPE_SECRET_KEY'];"
```

#### RCON Connection Issues
```bash
# Test RCON connectivity
telnet your_minecraft_server 25575

# Check RCON logs
tail -f vip_rcon_log.txt
```

#### Database Corruption
```bash
# Check database integrity
sqlite3 blog.sqlite "PRAGMA integrity_check;"

# Repair if needed
sqlite3 blog.sqlite ".backup backup.db"
```

### Debug Mode
Enable debug mode by adding to `.env`:
```env
APP_DEBUG=true
```

### Log Files
- `vip_rcon_log.txt` - RCON operation logs
- `vip_cleanup_log.txt` - VIP cleanup logs
- `debug.txt` - General debug information

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Install development dependencies:
   ```bash
   composer install --dev
   ```
4. Make your changes
5. Test thoroughly
6. Submit a pull request

### Code Style
- Follow PSR-12 coding standards
- Use meaningful variable names
- Add comments for complex logic
- Include error handling

### Testing
```bash
# Run syntax checks
find . -name "*.php" -exec php -l {} \;

# Test database connections
php test_database.php

# Verify RCON connectivity
php test_rcon.php
```

## 📄 License

This project is proprietary software. All rights reserved.

## 📞 Support

For support and questions:
- **Website**: [sentrysmp.eu](https://sentrysmp.eu)
- **Discord**: [Join our server](https://discord.gg/sentrysmp)
- **Email**: support@sentrysmp.eu

---

**Last Updated**: January 2024
**Version**: 2.0.0
**Minecraft Compatibility**: 1.20+