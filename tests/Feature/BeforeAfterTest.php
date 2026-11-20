<?php

declare(strict_types=1);

use DbCms\Core\Blocks\BlockPackageManager;
use DbCms\Core\Blocks\Contracts\BlockRegistryInterface;
use Illuminate\Validation\ValidationException;
use Tests\Support\CmsFixtures;

beforeEach(function (): void {
    CmsFixtures::localization(['es', 'en']);
});

it('preserves its definition and editor schema when disabled and restores availability', function (): void {
    $packages = app(BlockPackageManager::class);
    $registry = app(BlockRegistryInterface::class);
    expect($packages->all())->toHaveKey('before_after');
    expect($registry->isAvailable('before_after'))->toBeTrue();
    $packages->setEnabled('before_after', false);
    expect($registry->all())->toHaveKey('before_after')
        ->and($registry->getActiveBlocks())->not->toHaveKey('before_after')
        ->and($registry->get('before_after')->getEditorSchema())->toHaveKeys(['image_url', 'after_image_url'])
        ->and($registry->render('before_after', ['title' => 'Hidden']))->toBe('');
    expect(fn () => $registry->validate('before_after', ['image_url' => '/before.webp']))->toThrow(ValidationException::class);
    $packages->setEnabled('before_after', true);
    expect($registry->isAvailable('before_after'))->toBeTrue();
});

it('renders local comparison images with an accessible keyboard slider', function (): void {
    $registry = app(BlockRegistryInterface::class);
    $payload = ['title' => 'Comparación pública', 'image_url' => '/storage/before.webp', 'after_image_url' => '/storage/after.webp', 'before_label' => 'Antes', 'after_label' => 'Después'];
    $registry->validate('before_after', $payload);
    $html = $registry->render('before_after', $payload);
    expect($html)->toContain('Comparación pública', '/storage/before.webp', '/storage/after.webp', 'type="range"', 'aria-label="Antes / Después"');
});

it('rejects executable image protocols through canonical validation', function (string $field): void {
    app(BlockRegistryInterface::class)->validate('before_after', [$field => 'javascript:alert(1)']);
})->with(['image_url', 'after_image_url'])->throws(ValidationException::class);

it('never renders executable persisted image urls', function (): void {
    $html = app(BlockRegistryInterface::class)->render('before_after', ['title' => 'Comparison', 'image_url' => 'javascript:alert(1)', 'after_image_url' => 'javascript:alert(2)']);
    expect($html)->not->toContain('javascript:', 'data:text/html');
});

it('retains valid example schemas and translated comparison copy in Spanish and English', function (): void {
    $registry = app(BlockRegistryInterface::class);
    $block = $registry->get('before_after');
    $es = $block->getExamplePayload('es');
    $en = $block->getExamplePayload('en');
    foreach ([$es, $en] as $payload) {
        expect(array_keys($payload))->toBe(array_keys($block->getDefaultPayload()))
            ->and(array_diff(array_keys($payload), array_keys($block->getEditorSchema())))->toBe([]);
        $registry->validate('before_after', $payload);
    }
    foreach ($block->getTranslationPaths() as $path) {
        expect(data_get($en, $path))->not->toBe(data_get($es, $path));
    }
});

it('escapes labels and blocks unreviewed external media through the public response policy', function (): void {
    $this->withoutVite();
    $page = CmsFixtures::home();
    $section = $page->sections()->create(['type' => 'before_after', 'order' => 0, 'is_active' => true, 'status' => 'published']);
    $section->translations()->create(['locale' => 'es', 'content_json' => ['title' => '<script>owned</script>', 'image_url' => 'https://outside.example/before.webp', 'after_image_url' => 'https://outside.example/after.webp', 'before_label' => 'Antes', 'after_label' => 'Después']]);
    $response = $this->get('/es/es')->assertOk()->assertSee('type="range"', false)->assertDontSee('<script>owned</script>', false);
    $policy = (string) $response->headers->get('Content-Security-Policy');
    expect(preg_match('/(?:^|;\s*)img-src\s+([^;]+)/', $policy, $directive))->toBe(1);
    expect($directive[1])->toContain("'self'")->not->toContain('outside.example');
});

it('delivers the selected block and its built runtime on a public page', function (): void {
    $page = CmsFixtures::home();
    $section = $page->sections()->create(['type' => 'before_after', 'order' => 0, 'is_active' => true, 'status' => 'published']);
    $section->translations()->create(['locale' => 'es', 'content_json' => [
        'title' => 'Comparación pública',
        'image_url' => '/storage/before.webp', 'after_image_url' => '/storage/after.webp',
        'before_label' => 'Antes', 'after_label' => 'Después',
    ]]);
    $response = $this->get('/es/es')->assertOk()->assertSee('Comparación pública')->assertSee('aria-label="Antes / Después"', false);
    $directory = getenv('DBCMS_COMPARISON_FIXTURE_DIR');
    if (is_string($directory) && $directory !== '') {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($directory.'/comparison.html', $response->getContent());
    }
});
