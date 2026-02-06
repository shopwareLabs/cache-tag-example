<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Adapter\Cache\Event\AddCacheTagEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Debug subscriber to log all cache tags being added.
 *
 * This subscriber listens to the AddCacheTagEvent and logs all tags.
 * Useful for debugging and verifying that your cache tags are being added.
 *
 * HOW TO USE:
 * ===========
 * 1. Activate the plugin
 * 2. Set APP_DEBUG=1 in your .env
 * 3. Visit a product page in the storefront
 * 4. Check the logs: var/log/dev.log or var/log/prod.log
 *    Search for "[SwagCacheTagExample]"
 *
 * ALTERNATIVE: Check Response Headers
 * ====================================
 * 1. Open browser DevTools (F12) > Network tab
 * 2. Visit a page
 * 3. Click on the main document request
 * 4. Look for these headers:
 *    - x-shopware-cache-id: Contains all cache tags (comma separated)
 *    - x-symfony-cache: Shows cache status (fresh/stale)
 */
#[Package('framework')]
class DebugCacheTagSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddCacheTagEvent::class => 'onAddCacheTag',
        ];
    }

    public function onAddCacheTag(AddCacheTagEvent $event): void
    {
        // Only log tags from our plugin (filter by prefix)
        $ourTags = array_filter(
            $event->tags,
            static fn (string $tag): bool => str_starts_with($tag, 'my-plugin-')
                || str_starts_with($tag, 'custom-manufacturer-')
        );

        if ($ourTags !== []) {
            $this->logger->info('[SwagCacheTagExample] Cache tags added', [
                'tags' => $ourTags,
                'all_tags' => $event->tags,
            ]);
        }
    }
}
