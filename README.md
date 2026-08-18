# OVMS Admin

OVMS Admin is a full-stack project for managing OVMS vehicle telemetry and related services.

## Project structure

- Laravel app: [laravel](laravel)
- Python trips recorder: [python/trips-recorder](python/trips-recorder)
- Mosquitto configuration: [mosquitto](mosquitto)
- GitHub workflows: [.github/workflows](.github/workflows)

## Components

### Laravel
The Laravel application exposes the web interface and backend services.

### Python trips recorder
The Python service collects MQTT telemetry data and stores trip information.

### Mosquitto
The Mosquitto broker is configured for MQTT messaging between the services.

## Local development

### Prerequisites
- Docker and Docker Compose
- Python 3.12+
- PHP 8.4+
- Composer

### Run the stack
```bash
docker compose up -d
```

### Run Python tests
```bash
cd python/trips-recorder/src
pytest -q tests
```

### Run Laravel tests
```bash
cd laravel
php artisan test
```

## CI

The repository uses GitHub Actions workflows organized by purpose:
- PR validation under [.github/workflows/pr](.github/workflows/pr)
- Merge builds under [.github/workflows/merge](.github/workflows/merge)
- Keep-up-to-date automation under [.github/workflows/keep-up-to-date](.github/workflows/keep-up-to-date)
