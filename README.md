# Mirza Pro Bot

A comprehensive Telegram bot management system with advanced features for service management, payment processing, statistics, and more.

## 🌟 Features

- **Service Management**: Full control over VPN services and configurations
- **Payment Processing**: Multiple payment gateways (Card-to-Card, Zarinpal, NowPayments, etc.)
- **Statistics & Reports**: Detailed statistics with Excel export functionality
- **Expense Management**: Track and manage monthly expenses with document uploads
- **Partnership Management**: Define partners and calculate profit shares
- **Admin Panel**: Web-based admin panel for easy management
- **Multi-Panel Support**: Support for Marzban, X-UI, and other panels
- **Automated Notifications**: Server renewal reminders and service expiration alerts

## 📋 Requirements

- **PHP**: 7.4 or higher
- **MySQL/MariaDB**: 5.7 or higher
- **Web Server**: Apache or Nginx
- **SSL Certificate**: Required for webhook functionality
- **Telegram Bot Token**: Get from [@BotFather](https://t.me/BotFather)

## 🚀 Installation

### Quick Install (Recommended)

1. **Download and run the installation script:**

```bash
wget https://raw.githubusercontent.com/itum/mirza_pro/main/install.sh
chmod +x install.sh
sudo ./install.sh
```

2. **Follow the installation wizard:**
   - Enter your database credentials (MySQL/MariaDB)
   - Enter your Telegram Bot API key
   - Enter your admin Telegram user ID
   - Enter your domain name
   - Enter your bot username

3. **Set up the database:**
   - The installer will automatically create the database tables
   - Or manually run: `https://yourdomain.com/table.php`

4. **Configure webhook:**
   - The installer will set up the webhook automatically
   - Or manually set it via Telegram Bot API

### Manual Installation

1. **Clone the repository:**

```bash
git clone https://github.com/itum/mirza_pro.git
cd mirza_pro
```

2. **Configure database:**

Edit `config.php` and set your database credentials:

```php
$dbname = 'your_database_name';
$usernamedb = 'your_database_user';
$passworddb = 'your_database_password';
$APIKEY = 'your_telegram_bot_token';
$adminnumber = 'your_telegram_user_id';
$domainhosts = 'https://yourdomain.com';
$usernamebot = 'your_bot_username';
```

3. **Set up database tables:**

Visit `https://yourdomain.com/table.php` in your browser to create all required tables.

4. **Set file permissions:**

```bash
chmod -R 755 /var/www/html/mirzaprobotconfig/
chown -R www-data:www-data /var/www/html/mirzaprobotconfig/
```

5. **Configure cron jobs:**

Add the following cron jobs to run automated tasks:

```bash
* * * * * php /var/www/html/mirzaprobotconfig/cronbot/index.php
0 0 * * * php /var/www/html/mirzaprobotconfig/cronbot/server_renewal_notification.php
```

6. **Set up SSL certificate:**

```bash
sudo certbot --nginx -d yourdomain.com
```

## 📖 Configuration

### Database Configuration

The bot uses MySQL/MariaDB. Make sure your database supports:
- UTF8MB4 charset
- InnoDB engine
- Foreign key constraints

### Bot Configuration

1. Get your bot token from [@BotFather](https://t.me/BotFather)
2. Get your Telegram user ID (you can use [@userinfobot](https://t.me/userinfobot))
3. Update `config.php` with your credentials

### Payment Gateways

Configure payment gateways in the admin panel:
- **Zarinpal**: Iranian payment gateway
- **NowPayments**: Cryptocurrency payments
- **Card-to-Card**: Manual payment confirmation

## 🔧 Usage

### Admin Commands

- `/start` - Start the bot
- Access admin panel via web interface: `https://yourdomain.com/panel/`

### Statistics & Reports

1. Access statistics menu in the bot
2. Enter the statistics password (set in admin panel)
3. View monthly statistics, expenses, and partner shares
4. Export detailed Excel reports

### Expense Management

1. Go to Statistics menu → Manage Expenses
2. Add expenses with optional descriptions and documents
3. Set start/end dates for server expenses
4. Edit or delete expenses (with full audit log)

### Partnership Management

1. Go to Statistics menu → Manage Partners
2. Add partners with percentage shares
3. View calculated profit shares for each month
4. Export partner share reports

## 📁 Project Structure

```
mirza_pro/
├── admin.php              # Admin panel logic
├── index.php              # Main bot handler
├── function.php           # Helper functions
├── config.php             # Configuration file
├── table.php              # Database table creation
├── keyboard.php           # Keyboard layouts
├── install.sh             # Installation script
├── cronbot/               # Cron job scripts
│   ├── index.php
│   └── server_renewal_notification.php
├── panel/                 # Web admin panel
├── api/                   # API endpoints
└── payment/               # Payment gateway integrations
```

## 🔐 Security

- Always use HTTPS for webhook and admin panel
- Keep your bot token and database credentials secure
- Regularly update the bot code
- Use strong passwords for statistics access
- Enable firewall rules for your server

## 🛠️ Troubleshooting

### Bot not responding

1. Check webhook status: `https://api.telegram.org/bot<TOKEN>/getWebhookInfo`
2. Verify SSL certificate is valid
3. Check server logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`

### Database connection errors

1. Verify database credentials in `config.php`
2. Check MySQL service is running: `sudo systemctl status mysql`
3. Ensure database user has proper permissions

### Cron jobs not working

1. Check cron service: `sudo systemctl status cron`
2. Verify file paths in cron jobs
3. Check cron logs: `grep CRON /var/log/syslog`

## 📝 License

This project is open source. See the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 💖 Support

If you find this project useful, please consider supporting it:

👉 [Support the Project on NowPayments](https://nowpayments.io/donation/permiumbotmirza)

## ⭐ Star the Project

Don't forget to **Star** this repository to help others discover it!

## 📞 Contact

- **GitHub**: [@itum](https://github.com/itum)
- **Repository**: [https://github.com/itum/mirza_pro](https://github.com/itum/mirza_pro)

---

Made with ❤️ by the Mirza Pro Team
