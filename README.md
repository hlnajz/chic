# Chic  
WordPress Labbaalli Store Clothing Project CMS

## Table of Contents
- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Introduction
labbaalli Clothing is a WordPress-based CMS project designed for clothing stores. It provides a user-friendly interface for managing products, orders, and customers, allowing store owners to easily set up and manage their online clothing store.

## Features
- Responsive design
- Easy product management
- Order tracking
- Customer management
- Customizable themes

## Installation

Since this is a WordPress-based project, the installation doesn't require Node.js or npm. Follow these steps:

1. **Download WordPress**:
   - Download WordPress from the official site: [WordPress.org](https://wordpress.org/download/).
   
2. **Set up a Web Server**:
   - Install **XAMPP** or **MAMP** (or any local server environment) to run the WordPress site locally. If you want it on a live server, you'll need to host it with a WordPress-compatible provider.

3. **Set up a Database**:
   - Create a new database in **phpMyAdmin** (or another database management tool) for your WordPress site.

4. **Install WordPress**:
   - Move the downloaded WordPress files to your web server's root folder (e.g., `htdocs` for XAMPP or `www` for MAMP).
   - Go to `http://localhost` in your browser to begin the WordPress installation process.
   - During the setup, enter the database details and follow the instructions to complete the WordPress installation.

5. **Download & Install Astra Theme**:
   - Clone or download the Chic theme from this repository:
     ```bash
     git clone https://github.com/hlnajz/labbaalli-clothing.git
     ```
   - Move the **Astra** folder into the `wp-content/themes/` directory of your WordPress installation.
   - Go to **Appearance > Themes** in the WordPress dashboard, and activate the **Astra** theme.

## Usage
1. After installation, log in to your WordPress admin panel by navigating to `http://localhost/wp-admin` (or your live site if deployed).
2. Add products, set up your store's pages, and customize the site’s appearance using the **Clothing theme** settings.
3. For the live site, visit [labbaalli.live](http://labbaalli.live).

## Contributing
Contributions are welcome! Please fork the repository, make your changes, and submit a pull request.

## License
This project is licensed under the MIT License.
