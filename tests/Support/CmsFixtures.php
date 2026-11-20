<?php

declare(strict_types=1);

namespace Tests\Support;

use DbCms\Core\Content\Pages\Models\Page;
use DbCms\Core\Models\Language;
use DbCms\Core\Models\Market;

final class CmsFixtures
{
    /** @param list<string> $locales
     * @param  list<string>  $markets
     */
    public static function localization(array $locales = ['es', 'en'], array $markets = ['es']): void
    {
        foreach ($markets as $marketCode) {
            $market = Market::firstOrCreate(['code' => $marketCode], ['name' => $marketCode, 'currency' => 'EUR', 'default_locale' => $locales[0], 'is_active' => true, 'is_default' => $marketCode === $markets[0]]);
            foreach ($locales as $locale) {
                $language = Language::firstOrCreate(['code' => $locale], ['name' => $locale, 'native_name' => $locale, 'direction' => 'ltr', 'is_active' => true, 'is_default' => $locale === $locales[0]]);
                $market->languages()->syncWithoutDetaching([$language->id => ['is_default' => $locale === $locales[0]]]);
            }
        }
    }

    /** @param list<string> $locales
     * @param  list<string>  $markets
     */
    public static function home(array $locales = ['es'], array $markets = ['es']): Page
    {
        $page = Page::create(['template' => 'default', 'layout' => 'default', 'is_published' => true]);
        foreach ($locales as $locale) {
            $page->translations()->create(['locale' => $locale, 'title' => 'Fixture home', 'slug' => 'home']);
        }
        $page->markets()->sync(Market::query()->whereIn('code', $markets)->pluck('id')->all());

        return $page;
    }
}
