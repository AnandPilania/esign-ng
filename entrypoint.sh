echo "env"
cp .env.example .env
echo "Generate keys"
php artisan passport:install
echo "Script was run"
