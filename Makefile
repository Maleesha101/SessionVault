.PHONY: up down reset test logs shell

up:
	docker compose up --build

down:
	docker compose down

reset:
	./scripts/reset-lab.sh

test:
	docker compose run --rm artisan test

logs:
	docker compose logs -f

shell:
	docker compose run --rm artisan tinker
