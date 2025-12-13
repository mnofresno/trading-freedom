# Trading Freedom

A cross-platform mobile application for managing and visualizing cryptocurrency balances from multiple exchanges in a centralized way. Trading Freedom allows you to monitor all your assets from different trading platforms in a single intuitive interface.

## 📱 Features

- **Multi-Exchange Support**: Support for multiple cryptocurrency exchanges
  - Bittrex
  - Bitfinex
  - Poloniex
- **Balance Visualization**: Check your balances in real-time from your mobile device
- **Multiple Currencies**: View values in USD and mBTC
- **Market Values**: Check the current market value of your assets
- **API Key Management**: Configure and manage your API keys directly from the app
- **Modern Interface**: UI built with React Native and Tailwind CSS (NativeWind)
- **Security**: Secure token storage using Expo Secure Store
- **JWT Authentication**: Robust and secure authentication system

## 🏗️ Architecture

The project is divided into two main components:

### Backend (REST API)
- **Framework**: Laravel 5.4
- **Language**: PHP 7.4
- **Database**: MariaDB/MySQL
- **Authentication**: JWT (JSON Web Tokens)
- **Containerization**: Docker with PHP-FPM and Nginx

### Frontend (Mobile Application)
- **Framework**: React Native with Expo
- **Language**: TypeScript
- **Navigation**: Expo Router
- **Styling**: NativeWind (Tailwind CSS for React Native)
- **HTTP Client**: Axios
- **Secure Storage**: Expo Secure Store

## 🛠️ Tech Stack

### Backend
- **Laravel** 5.4 - PHP Framework
- **JWT Auth** (tymon/jwt-auth) - Token-based authentication
- **Laravel CORS** - CORS handling for API
- **Exchange Integrations**:
  - `messerli90/bittrex` - Bittrex API client
  - `mariodian/bitfinex-api-php` - Bitfinex API client
  - `htunlogic/laravel-poloniex` - Poloniex API client
- **PHPUnit** - Testing framework
- **Docker** - Containerization

### Frontend
- **React Native** 0.81.5
- **Expo** ~54.0.29
- **Expo Router** ~6.0.19 - File-based routing system
- **TypeScript** 5.9.2
- **NativeWind** 4.2.1 - Tailwind CSS for React Native
- **Axios** 1.13.2 - HTTP client
- **Expo Secure Store** ~15.0.8 - Secure credential storage

### Infrastructure
- **Docker Compose** - Container orchestration
- **Nginx** - Web server
- **MariaDB 10.6** - Database

## 📋 Prerequisites

- **Docker** and **Docker Compose** installed
- **Node.js** 18+ (for frontend development)
- **Composer** (included in Docker container)
- **Expo CLI** (optional, for mobile development)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/trading-freedom.git
cd trading-freedom
```

### 2. Configure the Backend

```bash
cd backend

# Copy environment file
cp .env.example .env

# Edit .env with your configurations
# Especially important:
# - DB_DATABASE=trading_freedom
# - DB_USERNAME=user
# - DB_PASSWORD=password
# - JWT_SECRET (generate with: php artisan jwt:secret)
```

### 3. Start Services with Docker

From the project root:

```bash
docker-compose up -d
```

This will start:
- **Backend API** at `http://localhost:8000`
- **MariaDB Database** on port `3306`

### 4. Install Backend Dependencies

```bash
# Enter the backend container
docker exec -it trading-freedom-app bash

# Install Composer dependencies
composer install

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations
php artisan migrate
```

### 5. Configure the Frontend

```bash
cd frontend

# Install dependencies
npm install

# Configure API URL in services/api.ts
# Default points to: https://trading-freedom.fresno.ar/api
# For local development, change to: http://localhost:8000/api
```

### 6. Run the Frontend

```bash
# From the frontend directory
npm start

# Or for specific platforms:
npm run android  # Android
npm run ios      # iOS
npm run web      # Web
```

## 📁 Project Structure

```
trading-freedom/
├── backend/                 # Laravel Backend
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/ # API Controllers
│   │   ├── Models/          # Eloquent Models
│   │   │   ├── User.php
│   │   │   ├── Asset.php
│   │   │   ├── UserKey.php
│   │   │   └── ExchangeProvider.php
│   │   └── Services/         # Exchange integration services
│   │       ├── MultiCrawlerService.php
│   │       ├── BittrexCrawlerService.php
│   │       ├── BitfinexCrawlerService.php
│   │       └── PoloniexCrawlerService.php
│   ├── database/
│   │   └── migrations/       # Database migrations
│   ├── routes/
│   │   └── api.php           # API routes
│   ├── config/               # Configurations
│   └── Dockerfile
├── frontend/                 # React Native Frontend
│   ├── app/                  # Screens (Expo Router)
│   │   ├── (tabs)/           # Tab screens
│   │   ├── login.tsx
│   │   └── signup.tsx
│   ├── components/           # Reusable components
│   ├── services/
│   │   └── api.ts            # API client
│   ├── context/
│   │   └── AuthContext.tsx   # Authentication context
│   └── package.json
├── docker-compose.yml        # Docker Compose configuration
└── README.md
```

## 🔧 Configuration

### Backend Environment Variables

Edit `backend/.env`:

```env
APP_NAME="Trading Freedom"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=trading_freedom
DB_USERNAME=user
DB_PASSWORD=password

JWT_SECRET=your_jwt_secret_here
```

### Frontend API Configuration

Edit `frontend/services/api.ts`:

```typescript
const API_URL = 'http://localhost:8000/api'; // For local development
// or
const API_URL = 'https://your-domain.com/api'; // For production
```

## 📱 Usage

### Registration and Authentication

1. Open the mobile application
2. Register with your email and password
3. Log in with your credentials

### Configure Exchanges

1. Go to the Settings section
2. Add a new Exchange Provider
3. Enter your API Key and API Secret
4. Select the exchange (Bittrex, Bitfinex, or Poloniex)

### View Balances

1. On the main dashboard you'll see all your balances
2. Values are displayed in USD and mBTC
3. You can refresh data by pulling down (pull to refresh)
4. Select different exchanges if you have multiple configured

## 🧪 Testing

### Backend

```bash
# Run PHP tests
docker exec -it trading-freedom-app php artisan test

# Or with PHPUnit directly
docker exec -it trading-freedom-app vendor/bin/phpunit
```

### Frontend

```bash
cd frontend
npm test
```

## 🔐 Security

- API Keys are stored encrypted in the database
- JWT tokens are stored securely using Expo Secure Store
- API communication uses HTTPS in production
- Credentials are never exposed in source code

## 🤝 Contributing

Contributions are welcome. If you want to add new features or support for more exchanges:

1. Fork the project
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

### Adding Support for a New Exchange

1. Create a new service in `backend/app/Services/` that implements `ICrawlerService`
2. Add the exchange API client to `composer.json`
3. Register the new crawler in `MultiCrawlerService`
4. Add the exchange to the `exchange_providers` table via migration

## 📝 API Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/register` - Register

### Balances (requires authentication)
- `GET /api/balances/{exchange_id}` - Get balances for an exchange

### Exchanges (requires authentication)
- `GET /api/exchanges/own` - Get user's exchanges
- `GET /api/exchanges` - List all available exchanges

### API Keys (requires authentication)
- `GET /api/apikeys` - List user's API keys
- `POST /api/apikeys` - Create new API key
- `PUT /api/apikeys/{id}` - Update API key
- `DELETE /api/apikeys/{id}` - Delete API key

### User (requires authentication)
- `GET /api/users/own` - Get current user information
- `PUT /api/users/own` - Update user profile

## 🐛 Troubleshooting

### Backend won't start
- Verify Docker is running
- Check logs: `docker-compose logs app`
- Verify port 8000 is not in use

### Database connection error
- Make sure the `db` container is running: `docker-compose ps`
- Check credentials in `backend/.env`
- Wait a few seconds after starting containers for the DB to be ready

### Frontend can't connect to API
- Verify the URL in `frontend/services/api.ts`
- Make sure the backend is running at `http://localhost:8000`
- Check CORS configuration in `backend/config/cors.php`

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Mariano Martín Fresno**

- If you need to add new features or have questions, feel free to contact me
- Contributions and Pull Requests are welcome

## 🙏 Acknowledgments

- Laravel Framework
- Expo and React Native Community
- All contributors who have helped improve this project
