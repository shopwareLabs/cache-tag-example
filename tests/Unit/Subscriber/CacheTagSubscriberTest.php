<?php declare(strict_types=1);

namespace Swag\CacheTagExample\Tests\Unit\Subscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Adapter\Cache\CacheTagCollector;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\Context\LanguageInfo;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Tax\TaxCollection;
use Shopware\Storefront\Page\Product\ProductPage;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Swag\CacheTagExample\Subscriber\CacheTagSubscriber;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CacheTagSubscriber::class)]
class CacheTagSubscriberTest extends TestCase
{
    private CacheTagCollector&MockObject $cacheTagCollector;

    private CacheTagSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->cacheTagCollector = $this->createMock(CacheTagCollector::class);
        $this->subscriber = new CacheTagSubscriber($this->cacheTagCollector);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = CacheTagSubscriber::getSubscribedEvents();

        static::assertArrayHasKey(ProductPageLoadedEvent::class, $events);
        static::assertSame('onProductPageLoaded', $events[ProductPageLoadedEvent::class]);
    }

    public function testOnProductPageLoadedAddsManufacturerTag(): void
    {
        $manufacturerId = Uuid::randomHex();
        $productId = Uuid::randomHex();

        $product = new SalesChannelProductEntity();
        $product->setId($productId);
        $product->setManufacturerId($manufacturerId);
        $product->setCategoryIds([]);

        $page = new ProductPage();
        $page->setProduct($product);

        $event = new ProductPageLoadedEvent(
            $page,
            $this->createSalesChannelContext(),
            new Request()
        );

        // Collect all tags that are added
        $addedTags = [];
        $this->cacheTagCollector
            ->expects(static::atLeastOnce())
            ->method('addTag')
            ->willReturnCallback(function (...$tags) use (&$addedTags): void {
                $addedTags = array_merge($addedTags, $tags);
            });

        $this->subscriber->onProductPageLoaded($event);

        // Verify manufacturer tag was added
        static::assertContains('custom-manufacturer-' . $manufacturerId, $addedTags);
    }

    public function testOnProductPageLoadedAddsExternalDataTag(): void
    {
        $productId = Uuid::randomHex();

        $product = new SalesChannelProductEntity();
        $product->setId($productId);
        $product->setCategoryIds([]);

        $page = new ProductPage();
        $page->setProduct($product);

        $event = new ProductPageLoadedEvent(
            $page,
            $this->createSalesChannelContext(),
            new Request()
        );

        // Collect all tags that are added
        $addedTags = [];
        $this->cacheTagCollector
            ->expects(static::atLeastOnce())
            ->method('addTag')
            ->willReturnCallback(function (...$tags) use (&$addedTags): void {
                $addedTags = array_merge($addedTags, $tags);
            });

        $this->subscriber->onProductPageLoaded($event);

        static::assertContains('my-plugin-external-data', $addedTags);
        static::assertContains('my-plugin-product-' . $productId, $addedTags);
    }

    private function createSalesChannelContext(): SalesChannelContext
    {
        $salesChannel = new SalesChannelEntity();
        $salesChannel->setId(Uuid::randomHex());

        $currency = new CurrencyEntity();
        $currency->setId(Uuid::randomHex());

        $customerGroup = new CustomerGroupEntity();
        $customerGroup->setId(Uuid::randomHex());

        $paymentMethod = new PaymentMethodEntity();
        $paymentMethod->setId(Uuid::randomHex());

        $shippingMethod = new ShippingMethodEntity();
        $shippingMethod->setId(Uuid::randomHex());

        $country = new CountryEntity();
        $country->setId(Uuid::randomHex());

        $shippingLocation = new ShippingLocation($country, null, null);

        $languageInfo = new LanguageInfo(
            name: 'English',
            localeCode: 'en-GB',
        );

        return new SalesChannelContext(
            context: \Shopware\Core\Framework\Context::createDefaultContext(),
            token: Uuid::randomHex(),
            domainId: null,
            salesChannel: $salesChannel,
            currency: $currency,
            currentCustomerGroup: $customerGroup,
            taxRules: new TaxCollection(),
            paymentMethod: $paymentMethod,
            shippingMethod: $shippingMethod,
            shippingLocation: $shippingLocation,
            customer: null,
            itemRounding: new CashRoundingConfig(2, 0.01, true),
            totalRounding: new CashRoundingConfig(2, 0.01, true),
            languageInfo: $languageInfo,
        );
    }
}
