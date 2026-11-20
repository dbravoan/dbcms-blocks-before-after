<?php

declare(strict_types=1);

namespace DbCms\Blocks\BeforeAfter\Blocks;

use DbCms\Core\Blocks\AbstractBlockType;
use DbCms\Core\Models\Market;
use DbCms\Core\Services\Cms\FrontViewResolver;

class BeforeAfterBlock extends AbstractBlockType
{
    public function getTranslationPaths(): array
    {
        return ['title', 'before_label', 'after_label'];
    }

    public function getType(): string
    {
        return 'before_after';
    }

    public function getName(): string
    {
        return 'Comparador Antes / Después';
    }

    public function getIcon(): string
    {
        return 'fa-solid fa-scale-balanced';
    }

    public function getCategory(): string
    {
        return 'media';
    }

    public function getDescription(): string
    {
        return 'Control deslizante interactivo para comparar dos imágenes (antes y después).';
    }

    public function getDefaultPayload(): array
    {
        return [
            'title' => 'Comparación de imágenes',
            'image_url' => '/images/placeholder.svg',
            'after_image_url' => '/images/placeholder.svg',
            'before_label' => 'Antes',
            'after_label' => 'Después',
        ];
    }

    /** @return array<string, mixed> */
    public function getExamplePayload(string $locale): array
    {
        if (str_starts_with(strtolower($locale), 'es')) {
            return $this->getDefaultPayload();
        }

        return array_replace($this->getDefaultPayload(), [
            'title' => 'Image comparison',
            'before_label' => 'Before',
            'after_label' => 'After',
        ]);
    }

    public function render(array $payload, ?Market $market = null, ?string $locale = null): string
    {
        $view = app(FrontViewResolver::class)->resolve('before_after.blocks.comparison', 'before_after::blocks.comparison');

        return view($view, [
            'title' => (string) ($payload['title'] ?? ''),
            'beforeImage' => (string) ($payload['image_url'] ?? ''),
            'afterImage' => (string) ($payload['after_image_url'] ?? ''),
            'beforeLabel' => (string) ($payload['before_label'] ?? 'Antes'),
            'afterLabel' => (string) ($payload['after_label'] ?? 'Después'),
        ])->render();
    }
}
