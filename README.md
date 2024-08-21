
# Ticketly

[![License](https://img.shields.io/badge/license-MPL%202.0-blue.svg)](LICENSE-MPL.txt)
[![Commercial License](https://img.shields.io/badge/license-Commercial-orange.svg)](LICENSE-COMMERCIAL.txt)

Ticketly is an advanced ticket management and validation system designed to streamline event ticketing processes. Whether you're organizing small events or large conferences, Ticketly offers robust tools for creating, validating, and managing tickets efficiently.

## Features

- **Ticket Creation:** Easily generate tickets for events with unique QR codes.
- **Validation System:** Securely validate tickets at the point of entry with real-time feedback.
- **Event Management:** Manage multiple events and track ticket sales and validations.
- **Admin Panel:** A user-friendly interface for managing events, tickets, and validations.
- **Notifications:** Receive email notifications upon successful ticket purchases and validations.

## Demo

Visit [ticketly.lazarefortune.com](https://ticketly.lazarefortune.com) to see a live demo of Ticketly in action.

## Installation

To get started with Ticketly, follow these steps:

### Prerequisites

- **PHP 8.0+**
- **Composer**
- **Symfony CLI**
- **MySQL or PostgreSQL**

### Clone the Repository

```bash
git clone https://github.com/your-username/ticketly.git
cd ticketly
```

### Install Dependencies

```bash
composer install
pnpm install
```

### Set Up the Database

1. Configure your database connection in the `.env` file.
2. Run the following commands to create the database and apply migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Start the Development Server

```bash
symfony serve
```

Open your browser and navigate to `http://localhost:8000`.

## Usage

### Creating an Event

1. Log in to the admin panel.
2. Navigate to the "Events" section.
3. Click on "Create New Event" and fill in the necessary details.
4. Save the event to start managing tickets.

### Validating Tickets

- Use the ticket validator tool available in the admin panel or use a mobile device to scan the QR codes at the event entrance.

### Admin Access

To access the admin panel:

1. Register a new user account.
2. Grant the user `ROLE_ADMIN` in the database or via the user management interface.

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository.
2. Create a new branch for your feature: `git checkout -b feature-name`.
3. Commit your changes: `git commit -m 'Add some feature'`.
4. Push to the branch: `git push origin feature-name`.
5. Open a pull request.

## Licensing

This project is dual-licensed under the Mozilla Public License 2.0 and a Commercial License.

### Open Source License (MPL 2.0)

The source code is available under the terms of the Mozilla Public License 2.0. See the [LICENSE-MPL.txt](LICENSE-MPL.txt) file for more details.

### Commercial License

For commercial use, you must obtain a separate Commercial License. Please contact [Your Contact Information] for more details. See the [LICENSE-COMMERCIAL.txt](LICENSE-COMMERCIAL.txt) file for more details.

## Contact

For more information, feel free to contact me at:

- **Email:** [lazarefortune@gmail.com](mailto:lazarefortune@gmail.com)
- **Website:** [lazarefortune.com](https://lazarefortune.com)
- **Twitter:** [@your-twitter-handle](https://twitter.com/your-twitter-handle)

## Acknowledgements

Special thanks to all contributors and users who help make Ticketly better.

---

*This README was created with ❤️ by Lazare Fortune.*
