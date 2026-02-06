<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Service;

use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;

/**
 * Example service demonstrating how to invalidate cache by custom tags.
 *
 * INVALIDATION WORKFLOW:
 * ======================
 * 1. Your plugin adds cache tags to pages using CacheTagCollector::addTag()
 * 2. The HTTP cache stores the response with these tags
 * 3. When data changes, you call CacheInvalidator::invalidate() with your tags
 * 4. All cached responses with matching tags are invalidated
 *
 * WHEN TO USE:
 * ============
 * - When external data changes (API webhooks, imports, etc.)
 * - When custom entities are updated
 * - When you need to invalidate cache for specific scenarios
 *
 * BEST PRACTICES:
 * ===============
 * - Use consistent tag naming conventions (e.g., 'my-plugin-{entity}-{id}')
 * - Don't over-invalidate - be specific with your tags
 * - Consider using delayed invalidation for bulk updates
 */
class CustomCacheInvalidator
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
    ) {
    }

    /**
     * Invalidate cache for a specific external data source.
     *
     * Call this when your external data has changed and cached
     * pages need to be refreshed.
     */
    public function invalidateExternalData(): void
    {
        $this->cacheInvalidator->invalidate(['my-plugin-external-data']);
    }

    /**
     * Invalidate cache for a specific manufacturer.
     *
     * Call this when manufacturer data has changed.
     */
    public function invalidateManufacturer(string $manufacturerId): void
    {
        $this->cacheInvalidator->invalidate([
            'custom-manufacturer-' . $manufacturerId,
        ]);
    }

    /**
     * Invalidate cache for specific products.
     *
     * @param array<string> $productIds
     */
    public function invalidateProducts(array $productIds): void
    {
        $tags = array_map(
            static fn (string $id) => 'my-plugin-product-' . $id,
            $productIds
        );

        $this->cacheInvalidator->invalidate($tags);
    }

    /**
     * Invalidate all navigation caches for a sales channel.
     */
    public function invalidateNavigation(string $salesChannelId): void
    {
        $this->cacheInvalidator->invalidate([
            'my-plugin-navigation',
            'my-plugin-nav-' . $salesChannelId,
        ]);
    }

    /**
     * Invalidate cache for closeout products.
     *
     * Useful when stock levels change and closeout products
     * need to be refreshed.
     */
    public function invalidateCloseoutProducts(): void
    {
        $this->cacheInvalidator->invalidate(['my-plugin-closeout-products']);
    }
}
