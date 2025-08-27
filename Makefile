WEB_CONTAINER = web
ENV=production

setup:
	chmod +x ./make-setup.sh
	./make-setup.sh $(WEB_CONTAINER) local

clear-logs:
	docker compose exec $(WEB_CONTAINER) truncate -s 0 storage/logs/laravel.log

clear-cache:
	docker compose exec $(WEB_CONTAINER) php artisan cache:clear
	docker compose exec $(WEB_CONTAINER) php artisan view:clear
	docker compose exec $(WEB_CONTAINER) php artisan route:clear
	docker compose exec $(WEB_CONTAINER) php artisan config:clear

build-assets: clear-cache
	docker compose exec $(WEB_CONTAINER) npm run build -- --mode=$(ENV)

restart-worker:
	docker compose exec $(WEB_CONTAINER) supervisorctl -c /etc/supervisor/conf.d/supervisord.conf restart all

worker-status:
	docker compose exec $(WEB_CONTAINER) supervisorctl -c /etc/supervisor/conf.d/supervisord.conf status all
