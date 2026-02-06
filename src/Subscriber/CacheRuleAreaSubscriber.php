<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Subscriber;

use Shopware\Core\Framework\Adapter\Cache\Http\Extension\ResolveCacheRelevantRuleIdsExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Example subscriber demonstrating how to add custom rule areas to the cache key.
 *
 * BACKGROUND:
 * ===========
 * In Shopware 6.7+, the cache system was reworked to reduce cache permutations.
 * Previously, ALL active rules were included in the cache key, leading to
 * potentially quadrillions of cache variations.
 *
 * Now, only rules from specific "rule areas" that are relevant to the current
 * request are included in the cache key.
 *
 * Default rule areas include:
 * - PRODUCT_AREA (for product prices)
 * - SHIPPING_AREA (for shipping methods)
 * - PAYMENT_AREA (for payment methods)
 *
 * WHEN TO USE THIS:
 * =================
 * If your plugin:
 * - Defines custom rules that affect page content
 * - Has custom entity associations with rules
 * - Needs rule-based content variations in the HTTP cache
 *
 * Then you should add your custom rule area to ensure the cache key
 * includes your relevant rules.
 *
 * @see https://www.shopware.com/en/news/new-caching-system/
 */
class CacheRuleAreaSubscriber implements EventSubscriberInterface
{
    /**
     * Define your custom rule area constant.
     * Use this when defining rule associations in your entity definitions.
     */
    public const MY_CUSTOM_RULE_AREA = 'myPluginCustomArea';

    public static function getSubscribedEvents(): array
    {
        return [
            // Subscribe to the extension's pre-event to add custom rule areas
            ResolveCacheRelevantRuleIdsExtension::NAME . '.pre' => 'onResolveRuleAreas',
        ];
    }

    /**
     * Add custom rule areas to be included in cache key calculation.
     *
     * This ensures that rules from your custom area are considered
     * when generating the cache hash (sw-cache-hash cookie).
     */
    public function onResolveRuleAreas(ResolveCacheRelevantRuleIdsExtension $extension): void
    {
        // Add your custom rule area to the list of areas to consider
        $extension->ruleAreas[] = self::MY_CUSTOM_RULE_AREA;

        // You can also add multiple areas if needed
        // $extension->ruleAreas[] = 'anotherCustomArea';
    }
}
