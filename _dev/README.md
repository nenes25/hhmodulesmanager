# Development Environment

This directory contains the Docker-based development environment for testing the module across multiple PrestaShop and PHP versions.

## Quick Start

1. Copy the environment file:
   ```bash
   cp _dev/.env.example _dev/.env
   ```

2. Start the environment:
   ```bash
   cd _dev && make up
   ```

3. Access PrestaShop at `http://localhost:8090`

## Available Commands

- `make up` - Start the PrestaShop environment
- `make down` - Stop the environment
- `make restart` - Restart the environment
- `make logs` - View container logs
- `make shell` - Access the PrestaShop container shell
- `make install` - Install composer dependencies
- `make install-module` - Install the module in PrestaShop
- `make test` - Run PHPUnit tests
- `make clean` - Remove all containers and volumes

## Testing Multiple PrestaShop Versions

Switch between PrestaShop versions easily:

```bash
# Switch to PrestaShop 1.7.8
make switch-1.7.8 && make up

# Switch to PrestaShop 8.1
make switch-8.1 && make up

# Switch to PrestaShop 9
make switch-9 && make up

# Switch to PrestaShop nightly
make switch-nightly && make up
```

Or run multiple versions simultaneously:

```bash
make multi
```

This will start:
- PrestaShop 1.7.8 on `http://localhost:8090`
- PrestaShop 8.1 on `http://localhost:8092`
- PrestaShop 9 on `http://localhost:8094`
- PrestaShop nightly on `http://localhost:8096`

## Testing PHP Versions

PrestaShop 9.1.1 supports PHP 8.1 to 8.5. Use the dedicated switch commands to test a specific combination:

```bash
# PrestaShop 9.1.1 with PHP 8.3
make switch-9.1.1-php83 && make up

# PrestaShop 9.1.1 with PHP 8.4
make switch-9.1.1-php84 && make up

# PrestaShop 9.1.1 with PHP 8.5
make switch-9.1.1-php85 && make up
```

Each PHP version gets its own isolated Docker project and volumes (`ps911php83`, `ps911php84`, `ps911php85`), so you can switch freely without data conflicts.

Once the environment is up, install and test the module:

```bash
make install-module
make shell
# then inside the container:
bin/console hhennes:module-manager:manage -v
bin/console hhennes:module-manager:generate
bin/console hhennes:module-manager:list-upgradable-modules
```

## PHPStorm Integration

The environment includes Xdebug support for debugging with PHPStorm:

1. Go to **Settings → PHP → Servers**
2. Add a new server with name `localhost`
3. Set path mapping: `/var/www/html/modules/hhmodulesmanager` → project root
4. Start listening for debug connections

## Database Access

- **phpMyAdmin**: `http://localhost:8091`
- **MySQL Host**: `localhost:3310`
- **Username**: `prestashop`
- **Password**: `prestashop`
