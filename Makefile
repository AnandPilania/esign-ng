ifndef u
u:=sukoyaka
endif

ifndef env
env:=dev
endif

OS:=$(shell uname)

docker-start:
	cp laravel-echo-server.json.example laravel-echo-server.json
	@if [ $(OS) = "Linux" ]; then\
		sed -i -e "s/localhost:8000/web:8000/g" laravel-echo-server.json; \
		sed -i -e "s/\"redis\": {}/\"redis\": {\"host\": \"redis\"}/g" laravel-echo-server.json; \
	else\
		sed -i '' -e "s/localhost:8000/web:8000/g" laravel-echo-server.json; \
		sed -i '' -e "s/\"redis\": {}/\"redis\": {\"host\": \"redis\"}/g" laravel-echo-server.json; \
	fi
	docker-compose up -d

docker-restart:
	docker-compose down
	make docker-start
	make docker-init-db-full
	make docker-link-storage

docker-connect:
	docker exec -it sukoyaka ash

init-app:
	cp .env.example .env
	composer install
	rm -rf node_modules
	npm install
	php artisan key:generate
	php artisan jwt:secret
	php artisan migrate
	php artisan db:seed
	php artisan passport:keys
	php artisan passport:install
	php artisan storage:link

docker-init:
	docker exec -it sukoyaka make init-app

init-db-full:
	make autoload
	php artisan migrate:fresh
	php artisan db:seed
	make update-master

reinit-db:
	make autoload
	php artisan migrate:fresh
	php artisan db:seed
	php artisan passport:install

docker-init-db-full:
	docker exec -it sukoyaka make init-db-full

docker-link-storage:
	docker exec -it sukoyaka php artisan storage:link

init-db:
	make autoload
	php artisan migrate:fresh

start:
	php artisan serve

log:
	tail -f storage/logs/laravel.log

test-js:
	npm test

test-php:
	vendor/bin/phpcs --standard=phpcs.xml && vendor/bin/phpmd app text phpmd.xml

build:
	npm run dev

watch:
	npm run watch

docker-watch:
	docker exec -it sukoyaka make watch

autoload:
	composer dump-autoload

cache:
	php artisan cache:clear && php artisan view:clear

docker-cache:
	docker exec sukoyaka make cache

route:
	php artisan route:list

generate-master:
	php bin/generate_master.php $(lang)

update-master:
	php artisan master:update $(lang)

deploy:
	ssh $(u)@$(server) "mkdir -p $(dir)"
	rsync -avhzL --delete \
				--no-perms --no-owner --no-group \
				--exclude .git \
				--exclude .idea \
				--exclude .env \
				--exclude laravel-echo-server.json \
				--exclude node_modules \
				--exclude vendor \
				--exclude bootstrap/cache \
				--exclude storage/logs \
				--exclude storage/framework \
				--exclude storage/app \
				--exclude public/hot \
				--exclude public/storage \
				--exclude public/mix-manifest.json \
				--exclude public/mix.js \
				. $(u)@$(server):$(dir)/

connect-master:
	ssh root@160.16.117.169

connect-slave:
	ssh root@160.16.50.160

init-db-local:
	ssh $(u)@192.168.1.202 "cd /var/www/sukoyaka$(n)/ && make init-db-full"

deploy-local:
	make deploy server=192.168.1.202 dir=/var/www/sukoyaka$(n)
	ssh $(u)@192.168.1.201 "cd /var/www/sukoyaka$(n) && make cache"

deploy-staging:
	make deploy server=139.162.10.80 u=root dir=/var/www/sukoyaka
	ssh root@139.162.10.80 "cd /var/www/sukoyaka/ && make cache"

docker-deploy-staging:
	make deploy server=139.162.10.80 u=root dir=/var/www/sukoyaka
	ssh root@139.162.10.80 "cd /var/www/sukoyaka/ && make docker-cache"

deploy-production:
	make deploy server=160.16.117.169 u=root dir=/var/www/sukoyaka
	ssh root@160.16.117.169 "cd /var/www/sukoyaka/ && make cache"

compile:
	ssh $(u)@$(server) "cd $(dir) && npm install && composer install && make cache && make autoload && npm run production"

deploy-local-full:
	make deploy server=192.168.1.201 dir=/var/www/sukoyaka
	make compile server=192.168.1.201 dir=/var/www/sukoyaka

deploy-staging-full:
	make deploy server=160.16.117.169 u=root dir=/root/sukoyaka
	make compile server=160.16.117.169 u=root dir=/root/sukoyaka
	ssh root@160.16.117.169 "rsync -avhzL --delete --no-perms --no-owner --no-group \
																	--exclude .env \
																	--exclude public/storage \
																	--exclude bootstrap/cache \
																	--exclude storage/logs \
																	--exclude storage/framework \
																	--exclude storage/app \
																	/root/sukoyaka/* /var/www/sukoyaka/"
	ssh root@160.16.117.169 "cd /var/www/sukoyaka/ && make cache"

deploy-production-full:
	make deploy server=160.16.117.169 u=root dir=/root/sukoyaka
	make compile server=160.16.117.169 u=root dir=/root/sukoyaka
	ssh root@160.16.117.169 "rsync -avhzL --delete --no-perms --no-owner --no-group \
																	--exclude .env \
																	--exclude public/storage \
																	--exclude bootstrap/cache \
																	--exclude storage/logs \
																	--exclude storage/framework \
																	--exclude storage/app \
																	/root/sukoyaka/* /var/www/sukoyaka/"
	ssh root@160.16.117.169 "cd /var/www/sukoyaka/ && make cache"
