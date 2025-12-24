# Docker Setup Guide

This guide explains how to run the Multi-Vendor E-Commerce Marketplace using Docker.

## Prerequisites

- Docker Desktop installed
- Docker Compose installed
- At least 4GB RAM available
- At least 10GB free disk space

## Quick Start

1. **Clone the repository and navigate to the project directory**

2. **Start all services:**
   ```bash
   docker-compose up -d
   ```

3. **Wait for services to be ready** (first run may take several minutes)

4. **Access the applications:**
   - **Frontend**: http://localhost:3000
   - **Backend API**: http://localhost:8000
   - **API Documentation**: http://localhost:8000/api/documentation
   - **Database**: localhost:5432 (PostgreSQL)
   - **Redis**: localhost:6379

## Services Overview

### Database (PostgreSQL)
- **Port**: 5432
- **Database**: marketplace
- **Username**: marketplace_user
- **Password**: marketplace_password

### Cache & Queue (Redis)
- **Port**: 6379
- **No authentication required**

### Backend (Laravel)
- **Port**: 8000
- **Framework**: Laravel 12
- **PHP Version**: 8.2
- **Database**: PostgreSQL
- **Cache/Queue**: Redis

### Frontend (Next.js)
- **Port**: 3000
- **Framework**: Next.js 14
- **Node Version**: 18

### Queue Worker
- **Runs in background**
- **Processes**: Email notifications, payment processing, etc.

## Environment Configuration

### Backend Environment Variables
The docker-compose.yml includes default environment variables. For production:

1. Copy `E-com-BackEnd/.env.example` to `E-com-BackEnd/.env`
2. Update the following variables:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   DB_PASSWORD=your_secure_password
   REDIS_PASSWORD=your_redis_password
   RAZORPAY_KEY_ID=your_razorpay_key
   RAZORPAY_KEY_SECRET=your_razorpay_secret
   ```

### Frontend Environment Variables
Update `E-com-FrontEnd/.env.local`:
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_RAZORPAY_KEY=rzp_test_your_key_here
```

## Docker Commands

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f frontend
```

### Rebuild Services
```bash
# Rebuild all
docker-compose up -d --build

# Rebuild specific service
docker-compose up -d --build backend
```

### Execute Commands in Containers

#### Backend Commands
```bash
# Run Laravel commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan queue:work

# Access container shell
docker-compose exec backend bash
```

#### Frontend Commands
```bash
# Install new dependencies
docker-compose exec frontend npm install package-name

# Run development server (if needed)
docker-compose exec frontend npm run dev

# Access container shell
docker-compose exec frontend sh
```

### Database Commands
```bash
# Access PostgreSQL
docker-compose exec postgres psql -U marketplace_user -d marketplace

# Backup database
docker-compose exec postgres pg_dump -U marketplace_user marketplace > backup.sql

# Restore database
docker-compose exec -T postgres psql -U marketplace_user -d marketplace < backup.sql
```

## Development Workflow

### Making Backend Changes
1. Edit files in `E-com-BackEnd/`
2. Rebuild backend: `docker-compose up -d --build backend`
3. Run migrations if needed: `docker-compose exec backend php artisan migrate`

### Making Frontend Changes
1. Edit files in `E-com-FrontEnd/`
2. Rebuild frontend: `docker-compose up -d --build frontend`

### Adding New Dependencies

#### Backend (PHP/Composer)
```bash
docker-compose exec backend composer require package-name
docker-compose up -d --build backend
```

#### Frontend (Node.js)
```bash
docker-compose exec frontend npm install package-name
docker-compose up -d --build frontend
```

## Troubleshooting

### Services Won't Start
```bash
# Check service status
docker-compose ps

# Check logs
docker-compose logs

# Restart services
docker-compose restart
```

### Port Conflicts
If ports 3000, 8000, 5432, or 6379 are already in use:
1. Stop conflicting services
2. Or modify ports in `docker-compose.yml`

### Database Connection Issues
```bash
# Check if PostgreSQL is running
docker-compose ps postgres

# Check PostgreSQL logs
docker-compose logs postgres

# Restart database
docker-compose restart postgres
```

### Permission Issues
```bash
# Fix storage permissions
docker-compose exec backend chown -R www-data:www-data storage bootstrap/cache

# Fix frontend permissions
docker-compose exec frontend chown -R node:node /app
```

### Out of Disk Space
```bash
# Clean up Docker
docker system prune -a

# Remove volumes (WARNING: This deletes data)
docker-compose down -v
```

## Production Deployment

For production deployment:

1. **Update environment variables** with production values
2. **Use external database** instead of Docker PostgreSQL
3. **Configure reverse proxy** (nginx) for SSL
4. **Set up monitoring** and logging
5. **Configure backups** for database and files

### Example Production docker-compose.yml
```yaml
version: '3.8'
services:
  backend:
    build: ./E-com-BackEnd
    environment:
      APP_ENV: production
      APP_DEBUG: false
      DB_HOST: your-external-db.com
      # ... other production env vars
    ports:
      - "8000:8000"

  frontend:
    build: ./E-com-FrontEnd
    environment:
      NEXT_PUBLIC_API_URL: https://api.yourdomain.com
    ports:
      - "3000:3000"
```

## File Structure

```
.
├── docker-compose.yml          # Main Docker Compose file
├── DOCKER_SETUP.md            # This guide
├── E-com-BackEnd/             # Laravel backend
│   ├── Dockerfile            # Backend Docker configuration
│   ├── .dockerignore        # Files to exclude from build
│   └── ...                  # Laravel application files
└── E-com-FrontEnd/           # Next.js frontend
    ├── Dockerfile           # Frontend Docker configuration
    ├── .dockerignore       # Files to exclude from build
    └── ...                 # Next.js application files
```

## Support

If you encounter issues:

1. Check the logs: `docker-compose logs`
2. Verify Docker Desktop is running
3. Ensure ports are available
4. Check system resources (RAM, disk space)
5. Refer to individual service documentation (Laravel, Next.js)

For more information, see the main project README.md and API_ENDPOINTS.md.
