<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CacheTagExample\Service\CustomCacheInvalidator;

#[CoversClass(CustomCacheInvalidator::class)]
class CustomCacheInvalidatorTest extends TestCase
{
    private CacheInvalidator&MockObject $cacheInvalidator;

    private CustomCacheInvalidator $service;

    protected function setUp(): void
    {
        $this->cacheInvalidator = $this->createMock(CacheInvalidator::class);
        $this->service = new CustomCacheInvalidator($this->cacheInvalidator);
    }

    public function testInvalidateExternalData(): void
    {
        $this->cacheInvalidator
            ->expects(static::once())
            ->method('invalidate')
            ->with(['my-plugin-external-data']);

        $this->service->invalidateExternalData();
    }

    public function testInvalidateManufacturer(): void
    {
        $manufacturerId = Uuid::randomHex();

        $this->cacheInvalidator
            ->expects(static::once())
            ->method('invalidate')
            ->with(['custom-manufacturer-' . $manufacturerId]);

        $this->service->invalidateManufacturer($manufacturerId);
    }

    public function testInvalidateProducts(): void
    {
        $productIds = [Uuid::randomHex(), Uuid::randomHex()];

        $this->cacheInvalidator
            ->expects(static::once())
            ->method('invalidate')
            ->with([
                'my-plugin-product-' . $productIds[0],
                'my-plugin-product-' . $productIds[1],
            ]);

        $this->service->invalidateProducts($productIds);
    }

    public function testInvalidateNavigation(): void
    {
        $salesChannelId = Uuid::randomHex();

        $this->cacheInvalidator
            ->expects(static::once())
            ->method('invalidate')
            ->with([
                'my-plugin-navigation',
                'my-plugin-nav-' . $salesChannelId,
            ]);

        $this->service->invalidateNavigation($salesChannelId);
    }

    public function testInvalidateCloseoutProducts(): void
    {
        $this->cacheInvalidator
            ->expects(static::once())
            ->method('invalidate')
            ->with(['my-plugin-closeout-products']);

        $this->service->invalidateCloseoutProducts();
    }
}
