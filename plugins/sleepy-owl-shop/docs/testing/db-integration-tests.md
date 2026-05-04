# DB Integration Tests

---

## Purpose

Test `Wpdb*Repository` implementations against real MySQL with full WP bootstrap. Catches schema bugs, SQL errors, and WP table JOIN issues. Complements unit tests, which use in-memory fakes.

---

## How it works

WP core runs in Docker. The plugin is volume-mounted into the container. Integration tests run inside the container where `$wpdb` and all WP tables are available.

Run from host:
```bash
docker exec <wp_container> bash -c \
  'cd /var/www/html/wp-content/plugins/sleepy-owl-shop \
   && vendor/bin/pest --configuration phpunit-integration.xml'
```

Composer shortcut (run inside container):
```json
"test:integration": "vendor/bin/pest --configuration phpunit-integration.xml"
```

---

## Bootstrap — `tests/bootstrap-integration.php`

```php
define('WP_ROOT', getenv('WP_ROOT') ?: '/var/www/html');

require_once WP_ROOT . '/wp-load.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
```

`WP_ROOT` env var allows overriding the WP path per container setup without touching code.

---

## Config — `phpunit-integration.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap-integration.php" colors="true">
    <testsuites>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## Transaction isolation — `tests/Integration/Pest.php`

Every integration test runs inside a transaction that is rolled back after the test. No test data persists between tests.

```php
beforeEach(function () {
    global $wpdb;
    $wpdb->query('START TRANSACTION');
});

afterEach(function () {
    global $wpdb;
    $wpdb->query('ROLLBACK');
    wp_cache_flush();
});
```

Applied automatically to every test in `tests/Integration/` via Pest's `uses()->in()`.

---

## File structure

```
tests/
├── bootstrap-integration.php
└── Integration/
    ├── Pest.php                              ← transaction hooks for all Integration tests
    └── Vendor/
        └── WpdbVendorRepositoryTest.php
```

---

## Conventions

- Test file: `Wpdb{Aggregate}RepositoryTest.php`
- Inject `$wpdb` directly — no abstraction layer
- Use `$wpdb->prefix . 'so_{table}'` for plugin custom tables
- No mocks — real DB only
- Each test is isolated by transaction rollback

---

## Schema prerequisite

Integration tests require plugin tables to exist. In dev: activate the plugin once in WP Admin to trigger `register_activation_hook` migrations.

---

## Example

```php
use SleepyOwl\Vendor\Domain\Model\Aggregate\Vendor;
use SleepyOwl\Vendor\Domain\Model\ValueObject\VendorStatus;
use SleepyOwl\Infrastructure\Persistence\Vendor\WpdbVendorRepository;
use SleepyOwl\Shared\Domain\Model\ValueObject\CommissionRate;
use SleepyOwl\Shared\Domain\Model\ValueObject\VendorId;

it('persists and retrieves vendor by id', function () {
    global $wpdb;
    $repo   = new WpdbVendorRepository($wpdb);
    $vendor = Vendor::register(new VendorId('abc-123'), 'Test Shop', new CommissionRate(10));
    $vendor->releaseEvents();

    $repo->add($vendor);

    $found = $repo->findById(new VendorId('abc-123'));
    expect($found)->not->toBeNull();
    expect($found->getBusinessName())->toBe('Test Shop');
    expect($found->getStatus())->toBe(VendorStatus::Pending);
});

it('returns null for unknown vendor', function () {
    global $wpdb;
    $repo = new WpdbVendorRepository($wpdb);

    expect($repo->findById(new VendorId('no-such-id')))->toBeNull();
});
```