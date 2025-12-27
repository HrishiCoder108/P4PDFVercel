# Installation Guide

## Prerequisites

You need to have **PHP** and **Composer** installed on your system.

### Install PHP

**Windows:**
1. Download PHP from: https://windows.php.net/download/
2. Extract to a folder (e.g., `C:\php`)
3. Add PHP to your PATH environment variable:
   - Open System Properties → Environment Variables
   - Add `C:\php` to the PATH variable
4. Verify installation: `php -v`

**Mac:**
```bash
brew install php
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install php php-cli php-curl php-json php-mbstring
```

### Install Composer

**Windows:**
1. Download Composer-Setup.exe from: https://getcomposer.org/download/
2. Run the installer and follow the instructions
3. Verify installation: `composer --version`

**Mac/Linux:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

## Install Project Dependencies

Once PHP and Composer are installed:

```bash
cd P4PDF
composer install
```

This will install all required PHP packages including:
- `firebase/php-jwt` - For JWT token generation
- `guzzlehttp/guzzle` - For HTTP requests

## Alternative: Manual Installation

If you cannot install Composer, you can manually download the dependencies, but it's **highly recommended** to use Composer for easier dependency management.

## Verify Installation

After running `composer install`, you should see a `vendor/` directory created with all dependencies.

Test that everything works:
```bash
php -r "require 'vendor/autoload.php'; echo 'P4PDF is ready!';"
```

## Troubleshooting

**"composer: command not found"**
- Make sure Composer is installed and in your PATH
- On Windows, you may need to restart your terminal/PowerShell

**"php: command not found"**
- Install PHP and add it to your PATH
- Verify with `php -v`

**Dependencies fail to install**
- Check your internet connection
- Try: `composer install --no-cache`
- Ensure PHP version is 7.3 or higher

