# SentrySMP Server Documentation

[![Server Status](https://img.shields.io/badge/Server-Online-brightgreen)](https://mc.sentrysmp.eu)
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
│  ├── User Pages (shards.php, keys.php, ranks.php)       │
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
php create_shards_table.php
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
- **Multi-item Shopping Cart**: Add shards, keys, and ranks
- **Dynamic Pricing**: Quantity-based discounts
- **Payment Processing**: Stripe and PayPal integration
- **Order Management**: Complete transaction tracking

### 👤 User Management
- **Player Authentication**: Minecraft username-based login (Bedrock players have a dot before their name, e.g., `.BedrockPlayer`)
- **Session Management**: Secure PHP sessions
- **VIP System**: Automated 30-day VIP and Eternal memberships
- **Permission Management**: RCON-based rank assignment

### 🎯 Shop Categories

#### Shards (`shards.php`)
- Points do buy spawners
- Quantity-based purchasing
- Automatic RCON delivery

#### Keys (`keys.php`)
- Treasure chest keys
- Special reward keys
- Custom commands execution

#### Battlepasses (`battlepasses.php`)
- Seasonal battle passes
- Progressive reward systems
- Premium content access

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
│   ├── shards.php                # Shards shop
│   ├── keys.php                  # Keys shop
│   ├── battlepasses.php          # Battlepasses shop
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
│   └── vip-rcon.php              # VIP RCON functions
│
├── 👑 Admin Panel
│   ├── admin.php                 # Main admin dashboard
│   ├── login.php                 # Admin login
│   ├── vip_manager.php           # VIP user management
│   ├── vip-list.php              # VIP user listing
│   ├── paid-list.php             # Payment history
│   ├── edit_shards.php           # Shard editor
│   ├── edit_keys.php             # Key editor
│   ├── edit_passes.php           # Battlepass editor
│   └── edit_ranks.php            # Rank editor
│
├── 📊 APIs & Services
│   ├── player_count.php          # Server status API
│   ├── discord.php               # Discord integration
│   ├── get_announcements.php     # Announcements API
│   └── get_commands.php          # Command execution API
│
├── 🗄️ Database Files
│   ├── blog.sqlite               # Shards database
│   ├── battlepasses.sqlite       # Battlepasses database
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

### Shards Table (`blog.sqlite`)
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
    price INTEGER DEFAULT 3,
    sales INTEGER
);
```

### Battlepasses Table (`battlepasses.sqlite`)
```sql
CREATE TABLE Battlepasses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    value TEXT NOT NULL,
    image TEXT,
    prikaz TEXT,
    price INTEGER DEFAULT 5,
    sales INTEGER
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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**VIP System Overview:**
- **Duration**: VIP access lasts 30 days from purchase date
- **Auto-cleanup**: Expired VIP users are automatically removed
- **RCON Integration**: Automatic permission management via RCON commands
- **Database Tracking**: All VIP purchases tracked in `vip_users` table

**VIP Detection Logic:**
- Automatically detects VIP purchases based on rank name or command containing:
  - "vip" (case insensitive)
  - "premium" (case insensitive)
  - "membership" (case insensitive)
- When VIP rank is purchased, user is automatically added to `vip_users` table
- Enhanced logging for VIP detection debugging

**VIP Management Files:**
- `vip_manager.php` - Admin panel for VIP user management
- `vip-list.php` - Public VIP user list
- `vip-rcon.php` - RCON permission management
- `auto_cleanup.php` - Automated cleanup script
- `index.php` - Automatic cleanup on page loads

### Eternal Users Table (`eternal.sqlite`)
```sql
CREATE TABLE eternal_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Eternal System Overview:**
- **Duration**: Eternal access lasts 30 days from purchase date
- **Auto-cleanup**: Expired Eternal users are automatically removed
- **RCON Integration**: Automatic permission management via RCON commands
- **Database Tracking**: All Eternal purchases tracked in `eternal_users` table

**Eternal Detection Logic:**
- Automatically detects Eternal purchases based on rank name or command containing:
  - "eternal" (case insensitive)
  - "eternity" (case insensitive)
  - "forever" (case insensitive)
- When Eternal rank is purchased, user is automatically added to `eternal_users` table
- Enhanced logging for Eternal detection debugging

**Eternal Management Files:**
- `eternal_manager.php` - Admin panel for Eternal user management
- `eternal-rcon.php` - RCON permission management
- `index.php` - Automatic cleanup on page loads

### Paid Users Table (`paid_users.sqlite`)
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    transaction_id TEXT UNIQUE,
    cart_data TEXT,
    amount REAL DEFAULT 0.0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**Enhanced Transaction Tracking:**
- **All purchases** are automatically saved to `paid_users.sqlite`
- **VIP purchases** are additionally saved to `vip.sqlite`
- **Cart data** is stored as JSON for detailed transaction history
- **Amount calculation** includes quantity × price for all items
- **Transaction ID** uniquely identifies each purchase

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

#### Eternal Permission Management
```php
// Grant Eternal permissions
$command = "lp user {$username} parent set eternal";
$rcon->sendCommand($command);

// Remove Eternal permissions
$command = "lp user {$username} clear";
$rcon->sendCommand($command);
```

#### Item Delivery
```php
// Give shard to player
$command = "points $usernamemc {amount};
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

#### Eternal Manager (`eternal_manager.php`)
- View all Eternal users
- Check expiration dates
- Manual user removal
- RCON permission sync

#### Database Editors
- **Shards**: Add/edit/delete shard
- **Keys**: Manage treasure keys
- **Battlepasses**: Manage battle passes
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

#### VIP System Issues

**Common VIP-related problems:**

1. **Auto-Cleanup System**: VIP users are automatically removed after 30 days
2. **Detection Issues**: VIP ranks not properly detected during purchase
3. **Database Errors**: SQLite permission or corruption issues
4. **Transaction Flow**: Issues in `execute_db_command.php` process

**Debug Steps:**

2. **Verify Transaction and VIP Status:**
   ```sql
   -- Recent transactions with amounts
   SELECT username, transaction_id, amount, cart_data, created_at
   FROM users ORDER BY created_at DESC LIMIT 10;

   -- Current VIP users with expiry
   SELECT username, created_at,
          julianday('now') - julianday(created_at) as days_old
   FROM vip_users;
   ```

2. **Check Cleanup Logs:**
   ```bash
   tail -f vip_cleanup_log.txt
   ```

5. **Expected Log Entries:**
   ```
   SUCCESS: Transaction saved to paid_users database: username (Amount: 10.00, Transaction: tx_123)
   VIP Detection - Rank: VIP Membership, IsVIP: YES
   SUCCESS: VIP user saved to database: username (Reason: VIP found in name)
   ```

#### Eternal System Issues

**Common Eternal-related problems:**

1. **Auto-Cleanup System**: Eternal users are automatically removed after 30 days
2. **Detection Issues**: Eternal ranks not properly detected during purchase
3. **Database Errors**: SQLite permission or corruption issues
4. **Transaction Flow**: Issues in `execute_db_command.php` process

**Debug Steps:**

1. **Verify Transaction and Eternal Status:**
   ```sql
   -- Recent transactions with amounts
   SELECT username, transaction_id, amount, cart_data, created_at
   FROM users ORDER BY created_at DESC LIMIT 10;

   -- Current Eternal users with expiry
   SELECT username, created_at,
          julianday('now') - julianday(created_at) as days_old
   FROM eternal_users;
   ```

2. **Check Cleanup Logs:**
   ```bash
   tail -f eternal_cleanup_log.txt
   tail -f eternal_rcon_log.txt
   ```

3. **Expected Log Entries:**
   ```
   SUCCESS: Transaction saved to paid_users database: username (Amount: 15.00, Transaction: tx_456)
   Eternal Detection - Rank: Eternal Membership, IsEternal: YES
   SUCCESS: Eternal user saved to database: username (Reason: Eternal found in name)
   ```

4. **Manual VIP Management:**
   ```php
   // Add VIP user manually
   $db = new SQLite3("vip.sqlite");
   $stmt = $db->prepare("INSERT INTO vip_users (username) VALUES (:username)");
   $stmt->bindValue(":username", $username, SQLITE3_TEXT);
   $stmt->execute();
   ```

5. **Manual Eternal Management:**
   ```php
   // Add Eternal user manually
   $db = new SQLite3("eternal.sqlite");
   $stmt = $db->prepare("INSERT INTO eternal_users (username) VALUES (:username)");
   $stmt->bindValue(":username", $username, SQLITE3_TEXT);
   $stmt->execute();
   ```
**Admin Panel Management:**
- **VIP**: Use admin panel `/vip_manager.php`
- **Eternal**: Use admin panel `/eternal_manager.php`
- **Check expiry**: Both VIP and Eternal expire 30 days after `created_at`
- **Manual cleanup**: Direct SQL commands (see above)

**Common Issues:**
- **Auto-cleanup too aggressive**: Check `index.php` cleanup throttling
- **VIP rank name mismatch**: Ensure rank contains "vip", "premium", or "membership"
- **Eternal rank name mismatch**: Ensure rank contains "eternal", "eternity", or "forever"
- **Database permissions**: `chmod 664 vip.sqlite eternal.sqlite`
- **RCON failures**: Check RCON connectivity in cleanup logs

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
- `vip_rcon_log.txt` - VIP RCON operation logs
- `vip_cleanup_log.txt` - VIP cleanup logs
- `eternal_rcon_log.txt` - Eternal RCON operation logs
- `eternal_cleanup_log.txt` - Eternal cleanup logs
- `debug.txt` - General debug information

## 🔧 Debug Tools

### VIP System Debug Tools

**VIP System Management**
- Use admin panel at `/vip_manager.php` for VIP user management
- Check VIP cleanup logs in `vip_cleanup_log.txt`
- Monitor RCON operations in `vip_rcon_log.txt`
- Review enhanced VIP detection logic in `execute_db_command.php`

### Eternal System Debug Tools

**Eternal System Management**
- Use admin panel at `/eternal_manager.php` for Eternal user management
- Check Eternal cleanup logs in `eternal_cleanup_log.txt`
- Monitor RCON operations in `eternal_rcon_log.txt`
- Review enhanced Eternal detection logic in `execute_db_command.php`

**Database Direct Access**
```sql
-- Check all transactions with details
SELECT username, transaction_id, amount, cart_data, created_at
FROM users ORDER BY created_at DESC;

-- Check VIP users with expiry info
SELECT username, created_at,
       datetime(created_at, '+30 days') as expires_at,
       julianday('now') - julianday(created_at) as days_old
FROM vip_users;

-- Check Eternal users with expiry info
SELECT username, created_at,
       datetime(created_at, '+30 days') as expires_at,
       julianday('now') - julianday(created_at) as days_old
FROM eternal_users;

-- Find transactions without VIP records (potential issues)
SELECT u.username, u.amount, u.cart_data, u.created_at
FROM users u
LEFT JOIN vip_users v ON u.username = v.username
WHERE u.cart_data LIKE '%rank_%' AND v.username IS NULL;

-- Find transactions without Eternal records (potential issues)
SELECT u.username, u.amount, u.cart_data, u.created_at
FROM users u
LEFT JOIN eternal_users e ON u.username = e.username
WHERE u.cart_data LIKE '%rank_%' AND e.username IS NULL;

-- Manual transaction addition
INSERT INTO users (username, transaction_id, amount, cart_data)
VALUES ('username_here', 'tx_12345', 10.00, '[{"id":"rank_2","quantity":1,"price":10.00}]');

-- Manual VIP addition
INSERT INTO vip_users (username) VALUES ('username_here');

-- Manual Eternal addition
INSERT INTO eternal_users (username) VALUES ('username_here');
```

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

## 🔧 System Updates & Enhancements

### Shopping Cart System
The cart system has been enhanced for better reliability and user experience:

- **Unified Price Format**: All prices display in `€X` format consistently across the platform
- **Improved Error Handling**: RCON connection failures now gracefully degrade instead of causing system errors
- **Enhanced Item Display**: Cart properly displays all item types including shards, keys, and ranks

### Current Implementation Status
- **Shards System**: Fully integrated with `shards.php` endpoint for proper cart functionality
- **Keys System**: Complete integration with cart, checkout, and RCON command execution
- **Battlepasses System**: Full implementation with `battlepasses.php`, admin editor, and payment processing
- **Ranks System**: Integrated with VIP/Eternal detection and automatic permission management
- **Price Display**: Standardized format with proper discount visualization across all shop categories
- **Cart Integration**: All shop categories (shards, keys, battlepasses, ranks) properly integrated
- **Error Recovery**: Robust error handling prevents payment processing interruptions
- **Cross-browser Compatibility**: JavaScript improvements ensure consistent behavior

### File Structure Updates
Key files have been updated to maintain system consistency:
- **Cart System**: Enhanced rendering logic (`cart.html`) with support for all shop categories
- **Payment Processing**: Updated `execute_db_command.php` with battlepasses support and improved ID mapping
- **Checkout Integration**: `checkout.php` processes all item types including battlepasses
- **Admin Tools**: Complete editor suite (`edit_shards.php`, `edit_keys.php`, `edit_passes.php`, `edit_ranks.php`)
- **Database Schema**: All shop categories have consistent table structures with price and sales support

## 📄 License

This project is proprietary software. All rights reserved.

## 📞 Support

For support and questions:
- **Website**: [sentrysmp.eu](https://sentrysmp.eu)
- **Discord**: [Join our server](https://discord.gg/sentrysmp)
- **Email**: support@sentrysmp.eu

---
