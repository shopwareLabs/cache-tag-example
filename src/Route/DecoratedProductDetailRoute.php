<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Route;

use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRouteResponse;
use Shopware\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Example route decorator demonstrating cache tag addition at the route level.
 *
 * USE CASE:
 * =========
 * Use route decoration when you need to:
 * - Add cache tags based on route-specific logic
 * - Access data that's only available during route execution
 * - Extend route functionality while adding cache tags
 *
 * ALTERNATIVE APPROACH:
 * =====================
 * For simpler cases, prefer using event subscribers (like CacheTagSubscriber)
 * as they are less invasive and easier to maintain.
 *
 * IMPORTANT:
 * ==========
 * The decorated route pattern is still valid in 6.7+, but the way you add
 * cache tags has changed. Instead of dispatching cache tag events,
 * you now use CacheTagCollector::addTag().
 */
class DecoratedProductDetailRoute extends AbstractProductDetailRoute
{
    public function __construct(
        private readonly AbstractProductDetailRoute $decorated,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function getDecorated(): AbstractProductDetailRoute
    {
        return $this->decorated;
    }

    public function load(
        string $productId,
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): ProductDetailRouteResponse {
        // Call the decorated route first
        $response = $this->decorated->load($productId, $request, $context, $criteria);

        // Add custom cache tags based on the loaded product
        $product = $response->getProduct();

        // Example: Tag with custom properties
        $customFields = $product->getCustomFields() ?? [];
        if (isset($customFields['my_plugin_external_id'])) {
            $this->cacheTagCollector->addTag(
                'my-plugin-external-' . $customFields['my_plugin_external_id']
            );
        }

        // Example: Tag based on product properties
        if ($product->getIsCloseout()) {
            $this->cacheTagCollector->addTag('my-plugin-closeout-products');
        }

        // Example: Tag with category information
        foreach ($product->getCategoryIds() as $categoryId) {
            $this->cacheTagCollector->addTag('my-plugin-category-' . $categoryId);
        }

        return $response;
    }
}
