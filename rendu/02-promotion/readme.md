# Étape 2 - Test unitaire de `PromotionService`

## Objectif

Dans cette deuxième étape, j'ai créé des tests unitaires pour le service `PromotionService`.

Le but était de tester la logique métier permettant de déterminer si un produit est en promotion et de vérifier le prix actuel du produit.

Je n'ai pas utilisé de base de données, de `FunctionalTestCase` ou de kernel Symfony.

---

## 1. Vérifier le service `PromotionService`

J'ai commencé par vérifier que le fichier suivant existait :

```text
src/Service/PromotionService.php
```

J'ai vérifié son contenu pour comprendre les méthodes disponibles.

Le service contient notamment :

```php
getCurrentPrice()
isOnPromotion()
```

La méthode `isOnPromotion()` vérifie plusieurs conditions :

* les dates de promotion doivent être définies ;
* le prix promotionnel doit être inférieur au prix normal ;
* la date actuelle doit être comprise entre la date de début et la date de fin.

La méthode `getCurrentPrice()` retourne ensuite le prix promotionnel si la promotion est active, sinon elle retourne le prix normal.

---

## 2. Créer le fichier de test

J'ai créé le fichier :

```text
tests/Unit/PromotionServiceTest.php
```

J'ai commencé par créer le service dans `setUp()` :

```php
private PromotionService $service;

protected function setUp(): void
{
    $this->service = new PromotionService();
}
```

Cela me permet d'avoir un nouveau `PromotionService` pour chaque test.

---

## 3. Créer un produit pour les tests

Pour éviter de répéter le même code dans chaque test, j'ai créé une méthode privée :

```php
private function createProduct(float $price, ?float $promoPrice = null): Product
{
    $product = new Product();

    $product->setName('Test Product')->setPrice($price);

    if (null !== $promoPrice) {
        $product->setPromoPrice($promoPrice);
        $product->setPromoStartsAt(
            new \DateTimeImmutable('2026-08-01 00:00:00')
        );
        $product->setPromoEndsAt(
            new \DateTimeImmutable('2026-08-31 23:59:59')
        );
    }

    return $product;
}
```

Cette méthode me permet de créer rapidement un produit.

Par exemple :

```php
$product = $this->createProduct(50.00, 40.00);
```

crée un produit avec :

* prix normal : `50 €`
* prix promotionnel : `40 €`
* début de promotion : `01/08/2026 à 00:00:00`
* fin de promotion : `31/08/2026 à 23:59:59`

---

## 4. Tester l'absence de promotion

J'ai commencé par tester le cas où aucune promotion n'est définie :

```php
public function testReturnsNormalPriceWhenNoPromotion(): void
{
    $product = $this->createProduct(50.00);

    $this->assertSame(50.00, $this->service->getCurrentPrice($product));
    $this->assertFalse($this->service->isOnPromotion($product));
}
```

Je vérifie que :

* le prix actuel est `50 €` ;
* le produit n'est pas en promotion.

---

## 5. Tester une promotion active

J'ai ensuite testé le cas où la date actuelle se trouve pendant la période de promotion :

```php
public function testReturnsPromoPriceDuringPeriod(): void
{
    $product = $this->createProduct(50.00, 40.00);

    $now = new \DateTimeImmutable('2026-08-15 12:00:00');

    $this->assertSame(
        40.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertTrue(
        $this->service->isOnPromotion($product, $now)
    );
}
```

Le `15 août` est compris entre le `1er août` et le `31 août`.

Je vérifie donc que :

* le prix actuel est `40 €` ;
* la promotion est active.

---

## 6. Tester une date avant la promotion

J'ai ensuite testé le cas où la date actuelle est avant le début de la promotion :

```php
public function testReturnsNormalPriceBeforePromotionPeriod(): void
{
    $product = $this->createProduct(50.00, 40.00);

    $now = new \DateTimeImmutable('2026-07-15 12:00:00');

    $this->assertSame(
        50.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertFalse(
        $this->service->isOnPromotion($product, $now)
    );
}
```

La promotion n'a pas encore commencé.

Le prix normal de `50 €` doit donc être utilisé.

---

## 7. Tester une date après la promotion

J'ai testé le cas où la date actuelle est après la fin de la promotion :

```php
public function testReturnsNormalPriceAfterPromotionPeriod(): void
{
    $product = $this->createProduct(50.00, 40.00);

    $now = new \DateTimeImmutable('2026-09-15 12:00:00');

    $this->assertSame(
        50.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertFalse(
        $this->service->isOnPromotion($product, $now)
    );
}
```

La promotion est terminée.

Le prix normal de `50 €` doit donc être utilisé.

---

## 8. Tester un prix promotionnel égal au prix normal

J'ai vérifié qu'un prix promotionnel identique au prix normal ne devait pas être considéré comme une promotion :

```php
public function testPromoPriceEqualToNormalIsNotActive(): void
{
    $product = $this->createProduct(50.00, 50.00);

    $now = new \DateTimeImmutable('2026-08-15 12:00:00');

    $this->assertSame(
        50.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertFalse(
        $this->service->isOnPromotion($product, $now)
    );
}
```

Même si la date est comprise dans la période, le prix promotionnel n'est pas inférieur au prix normal.

Il n'y a donc pas de promotion.

---

## 9. Tester un prix promotionnel supérieur au prix normal

J'ai également vérifié le cas où le prix promotionnel est supérieur au prix normal :

```php
public function testPromoPriceGreaterThanNormalIsNotActive(): void
{
    $product = $this->createProduct(50.00, 60.00);

    $now = new \DateTimeImmutable('2026-08-15 12:00:00');

    $this->assertSame(
        50.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertFalse(
        $this->service->isOnPromotion($product, $now)
    );
}
```

Un produit à `50 €` ne peut pas avoir une promotion à `60 €`.

Le service doit donc conserver le prix normal.

---

## 10. Tester des dates inversées

J'ai testé le cas où la date de début est après la date de fin.

Pour cela, j'ai créé un deuxième helper :

```php
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
```

J'ai ensuite créé le test :

```php
public function testInvertedDatesAreNotActive(): void
{
    $product = $this->createProductWithDates(
        50.00,
        40.00,
        new \DateTimeImmutable('2026-08-31 23:59:59'),
        new \DateTimeImmutable('2026-08-01 00:00:00')
    );

    $now = new \DateTimeImmutable('2026-08-15 12:00:00');

    $this->assertSame(
        50.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertFalse(
        $this->service->isOnPromotion($product, $now)
    );
}
```

La date de début étant après la date de fin, la promotion ne doit pas être active.

---

## 11. Tester la borne de début

J'ai vérifié que la promotion était active exactement au moment où elle commence :

```php
public function testBoundaryStartIsIncluded(): void
{
    $product = $this->createProduct(50.00, 40.00);

    $now = new \DateTimeImmutable('2026-08-01 00:00:00');

    $this->assertSame(
        40.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertTrue(
        $this->service->isOnPromotion($product, $now)
    );
}
```

La date est exactement :

```text
01/08/2026 00:00:00
```

La promotion doit donc être active.

---

## 12. Tester la borne de fin

Enfin, j'ai vérifié que la promotion était encore active exactement à sa date de fin :

```php
public function testBoundaryEndIsIncluded(): void
{
    $product = $this->createProduct(50.00, 40.00);

    $now = new \DateTimeImmutable('2026-08-31 23:59:59');

    $this->assertSame(
        40.00,
        $this->service->getCurrentPrice($product, $now)
    );

    $this->assertTrue(
        $this->service->isOnPromotion($product, $now)
    );
}
```

La date est exactement :

```text
31/08/2026 23:59:59
```

La promotion doit donc encore être active.

---

## 13. Fichier final

Au final, mon fichier `tests/Unit/PromotionServiceTest.php` contient les 9 tests et les deux méthodes utilitaires :

```text
PromotionServiceTest
│
├── setUp()
│
├── testReturnsNormalPriceWhenNoPromotion()
├── testReturnsPromoPriceDuringPeriod()
├── testReturnsNormalPriceBeforePromotionPeriod()
├── testReturnsNormalPriceAfterPromotionPeriod()
├── testPromoPriceEqualToNormalIsNotActive()
├── testPromoPriceGreaterThanNormalIsNotActive()
├── testInvertedDatesAreNotActive()
├── testBoundaryStartIsIncluded()
├── testBoundaryEndIsIncluded()
│
├── createProduct()
└── createProductWithDates()
```

---

## 14. Lancer les tests

Pour lancer uniquement les tests de `PromotionServiceTest.php`, j'ai utilisé :

```powershell
php vendor/bin/phpunit tests/Unit/PromotionServiceTest.php
```

Le fichier contient donc :

```text
9 tests
18 assertions
```

Les 9 tests permettent de vérifier les différents comportements de `PromotionService`.

---

## Bilan

Dans cette deuxième étape, j'ai appris à tester un service contenant de la logique métier sans utiliser de base de données.

J'ai vérifié :

* l'absence de promotion ;
* une promotion active ;
* une date avant la promotion ;
* une date après la promotion ;
* un prix promotionnel égal au prix normal ;
* un prix promotionnel supérieur au prix normal ;
* des dates inversées ;
* la date exacte de début ;
* la date exacte de fin.

Les tests permettent notamment de vérifier que les **bornes de la période de promotion sont incluses**.

**Étape 2 terminée : 9 tests et 18 assertions.**