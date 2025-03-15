# Run docker
docker-composer up -d

# Install 
docker-composer exec app bash
composer i
php artisan jwt:secret

# Set .env
AUTH_GUARD=api