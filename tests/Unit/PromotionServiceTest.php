<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Service\PromotionService;
use PHPUnit\Framework\TestCase;

class PromotionServiceTest extends TestCase
{
    private PromotionService $service;

    protected function setUp(): void
    {
        $this->service = new PromotionService();
    }

    public function testReturnsNormalPriceWhenNoPromotion(): void
    {
        $product = $this->createProduct(50.00);

        $this->assertSame(50.00, $this->service->getCurrentPrice($product));
        $this->assertFalse($this->service->isOnPromotion($product));
    }

    public function testReturnsPromoPriceDuringPeriod(): void
    {
        $product = $this->createProduct(50.00, 40.00);

        $now = new \DateTimeImmutable('2026-08-15 12:00:00');

        $this->assertSame(40.00, $this->service->getCurrentPrice($product, $now));
        $this->assertTrue($this->service->isOnPromotion($product, $now));
    }

    public function testReturnsNormalPriceBeforePromotionPeriod(): void
    {
        $product = $this->createProduct(50.00, 40.00);

        $now = new \DateTimeImmutable('2026-07-15 12:00:00');

        $this->assertSame(50.00, $this->service->getCurrentPrice($product, $now));
        $this->assertFalse($this->service->isOnPromotion($product, $now));
    }

    public function testReturnsNormalPriceAfterPromotionPeriod(): void
    {
        $product = $this->createProduct(50.00, 40.00);

        $now = new \DateTimeImmutable('2026-09-15 12:00:00');

        $this->assertSame(50.00, $this->service->getCurrentPrice($product, $now));
        $this->assertFalse($this->service->isOnPromotion($product, $now));
    }

    public function testPromoPriceEqualToNormalIsNotActive(): void
    {
        $product = $this->createProduct(50.00, 50.00);

        $now = new \DateTimeImmutable('2026-08-15 12:00:00');

        $this->assertSame(50.00, $this->service->getCurrentPrice($product, $now));
        $this->assertFalse($this->service->isOnPromotion($product, $now));
    }

    public function testPromoPriceGreaterThanNormalIsNotActive(): void
    {
        $product = $this->createProduct(50.00, 60.00);

        $now = new \DateTimeImmutable('2026-08-15 12:00:00');

        $this->assertSame(50.00, $this->service->getCurrentPrice($product, $now));
        $this->assertFalse($this->service->isOnPromotion($product, $now));
    }

    public function testInvertedDatesAreNotActive(): void
    {
        $product = $this->createProductWithDates(
            50.00,
            40.00,
            new \DateTimeImmutable('2026-08-31 23:59:59'),
            new \DateTimeImmutable('2026-08-01 00:00:00')
        );

        $now = new \DateTimeImmutable('2026-08-15 12:00:00');

        $this->assertSame(50.00, $this->service->getCurrentPrice($product, $now));
        $this->assertFalse($this->service->isOnPromotion($product, $now));
    }

    public function testBoundaryStartIsIncluded(): void
    {
        $product = $this->createProduct(50.00, 40.00);

        $now = new \DateTimeImmutable('2026-08-01 00:00:00');

        $this->assertSame(40.00, $this->service->getCurrentPrice($product, $now));
        $this->assertTrue($this->service->isOnPromotion($product, $now));
    }

    public function testBoundaryEndIsIncluded(): void
    {
        $product = $this->createProduct(50.00, 40.00);

        $now = new \DateTimeImmutable('2026-08-31 23:59:59');

        $this->assertSame(40.00, $this->service->getCurrentPrice($product, $now));
        $this->assertTrue($this->service->isOnPromotion($product, $now));
    }

    private function createProduct(float $price, ?float $promoPrice = null): Product
    {
        $product = new Product();

        $product->setName('Test Product')->setPrice($price);

        if (null !== $promoPrice) {
            $product->setPromoPrice($promoPrice);
            $product->setPromoStartsAt(new \DateTimeImmutable('2026-08-01 00:00:00'));
            $product->setPromoEndsAt(new \DateTimeImmutable('2026-08-31 23:59:59'));
        }

        return $product;
    }

    private function createProductWithDates(
        float $price,
        float $promoPrice,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt
    ): Product {
        $product = new Product();

        $product->setName('Test Product')->setPrice($price);
        $product->setPromoPrice($promoPrice);
        $product->setPromoStartsAt($startsAt);
        $product->setPromoEndsAt($endsAt);

        return $product;
    }
}