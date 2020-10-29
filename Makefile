events-process:
	docker-compose up -d && \
	docker exec bothelp_php composer install && \
	docker exec -ti bothelp_php php generate.php && \
	docker exec -ti bothelp_php php index.php