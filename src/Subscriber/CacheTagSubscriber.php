<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Subscriber;

use Shopware\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Example subscriber demonstrating the NEW way to add cache tags in Shopware 6.7+
 *
 * MIGRATION GUIDE:
 * ================
 *
 * BEFORE (6.6 and earlier - DEPRECATED):
 * --------------------------------------
 * You would subscribe to events like:
 * - NavigationRouteCacheTagsEvent
 * - ProductDetailRouteCacheTagsEvent
 * - CategoryRouteCacheTagsEvent
 * - etc.
 *
 * Example (OLD - don't use anymore):
 *
 *     public static function getSubscribedEvents(): array
 *     {
 *         return [
 *             ProductDetailRouteCacheTagsEvent::class => 'onProductDetailCacheTags',
 *         ];
 *     }
 *
 *     public function onProductDetailCacheTags(ProductDetailRouteCacheTagsEvent $event): void
 *     {
 *         $event->addTags(['my-custom-tag']);
 *     }
 *
 * AFTER (6.7+ - NEW APPROACH):
 * ----------------------------
 * Inject CacheTagCollector and call addTag() on page loaded events or in route decorators.
 *
 * The cache tags are now collected at the HTTP layer, not at the Store-API route layer.
 * This is more efficient and simplifies the caching architecture.
 *
 * @see https://www.shopware.com/en/news/new-caching-system/
 * @see https://developer.shopware.com/docs/guides/plugins/plugins/framework/caching/
 */
class CacheTagSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Subscribe to page loaded events for storefront pages
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
            NavigationPageLoadedEvent::class => 'onNavigationPageLoaded',
        ];
    }

    /**
     * Add custom cache tags when a product page is loaded.
     *
     * Use case examples:
     * - Tag pages with external data sources for invalidation
     * - Tag pages with custom entity IDs
     * - Tag pages with time-based identifiers for scheduled invalidation
     */
    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();

        // Example 1: Add a custom tag based on product manufacturer
        $manufacturerId = $product->getManufacturerId();
        if ($manufacturerId !== null) {
            $this->cacheTagCollector->addTag('custom-manufacturer-' . $manufacturerId);
        }

        // Example 2: Add tags for external data sources
        // If your plugin fetches data from an external API, you can tag the page
        // so you can invalidate it when the external data changes
        $this->cacheTagCollector->addTag('my-plugin-external-data');

        // Example 3: Add multiple tags at once
        $this->cacheTagCollector->addTag(
            'my-plugin-product-' . $product->getId(),
            'my-plugin-category-' . ($product->getCategoryIds()[0] ?? 'none')
        );
    }

    /**
     * Add custom cache tags when a navigation page is loaded.
     */
    public function onNavigationPageLoaded(NavigationPageLoadedEvent $event): void
    {
        // Example: Tag all navigation pages with a global tag for easy invalidation
        $this->cacheTagCollector->addTag('my-plugin-navigation');

        // Example: Tag based on sales channel
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();
        $this->cacheTagCollector->addTag('my-plugin-nav-' . $salesChannelId);
    }
}
