# 01 - `Product`

## Objectif

Dans cette première étape, j'ai créé des tests unitaires pour l'Entity `Product`.

J'ai testé principalement les méthodes :

* `isMature()`
* `isAvailable()`

Le but est de vérifier que ces méthodes retournent bien le résultat attendu selon l'état du produit.

---

## 1. Vérifier mon projet

Je me suis d'abord placé dans le dossier de mon projet :

```powershell
cd "E:\BUT\3ième Année\Semestre 5\R5-07 - Automatisation de la production\ludo-shop-etudiant"
```

J'ai vérifié que mon projet contenait notamment :

```text
src/
tests/
vendor/
composer.json
```

---

## 2. Vérifier l'Entity `Product`

J'ai vérifié que le fichier suivant existait :

```text
src/Entity/Product.php
```

J'ai également vérifié que les méthodes nécessaires à mes tests étaient présentes :

```php
isMature()
setIsMature()
isAvailable()
setIsActive()
setStock()
```

Je n'ai pas eu besoin de modifier `Product.php`.

---

## 3. Créer `ProductTest.php`

J'ai créé le fichier :

```text
tests/Unit/ProductTest.php
```

J'y ai mis le code suivant :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testIsMatureReturnsTrueWhenFlagged(): void
    {
        $product = new Product();

        $product->setIsMature(true);

        $this->assertTrue($product->isMature());
    }

    public function testIsMatureReturnsFalseByDefault(): void
    {
        $product = new Product();

        $product->setIsMature(false);

        $this->assertFalse($product->isMature());
    }

    public function testIsAvailableWhenActiveAndInStock(): void
    {
        $product = new Product();

        $product->setIsActive(true);
        $product->setStock(10);

        $this->assertTrue($product->isAvailable());
    }

    public function testIsNotAvailableWhenInactive(): void
    {
        $product = new Product();

        $product->setIsActive(false);
        $product->setStock(10);

        $this->assertFalse($product->isAvailable());
    }

    public function testIsNotAvailableWhenOutOfStock(): void
    {
        $product = new Product();

        $product->setIsActive(true);
        $product->setStock(0);

        $this->assertFalse($product->isAvailable());
    }
}
```

---

## 4. Les tests que j'ai réalisés

### Test 1 - `isMature()` retourne `true`

J'ai créé un produit et je l'ai marqué comme mature :

```php
$product->setIsMature(true);
```

Puis j'ai vérifié que :

```php
$product->isMature()
```

retournait bien `true`.

---

### Test 2 - `isMature()` retourne `false`

J'ai créé un produit non mature :

```php
$product->setIsMature(false);
```

Puis j'ai vérifié que `isMature()` retournait `false`.

---

### Test 3 - Produit disponible

J'ai créé un produit actif avec 10 produits en stock :

```php
$product->setIsActive(true);
$product->setStock(10);
```

J'ai ensuite vérifié que `isAvailable()` retournait `true`.

---

### Test 4 - Produit inactif

J'ai créé un produit avec du stock mais qui n'est pas actif :

```php
$product->setIsActive(false);
$product->setStock(10);
```

J'ai vérifié que `isAvailable()` retournait `false`.

---

### Test 5 - Produit sans stock

J'ai créé un produit actif mais avec un stock de `0` :

```php
$product->setIsActive(true);
$product->setStock(0);
```

J'ai vérifié que `isAvailable()` retournait `false`.

---

## 6. Lancer les tests

Pour vérifier mes tests, j'ai exécuté la commande :

```powershell
php vendor/bin/phpunit tests/Unit/ProductTest.php
```

---

## 7. Résultat

J'ai obtenu :

```text
PHPUnit 12.5.33 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.33
Configuration: E:\BUT\3ième Année\Semestre 5\R5-07 - Automatisation de la production\ludo-shop-etudiant\phpunit.dist.xml

.....                                                               5 / 5 (100%)

Time: 00:00.170, Memory: 18.00 MB

OK (5 tests, 5 assertions)
```

Les cinq `.` correspondent aux cinq tests qui ont réussi.

Le résultat :

```text
OK (5 tests, 5 assertions)
```

confirme donc que mes **5 tests sont réussis** et que mes **5 assertions sont correctes**.

---

## 8. Structure obtenue

À la fin de cette étape, j'ai obtenu la structure suivante :

```text
ludo-shop-etudiant/
│
├── src/
│   └── Entity/
│       └── Product.php
│
├── tests/
│   └── Unit/
│       └── ProductTest.php
│
├── vendor/
│
├── composer.json
└── phpunit.dist.xml
```

---

## Bilan

Dans cette première étape, j'ai appris à créer un test unitaire avec PHPUnit.

J'ai testé différents états d'un produit afin de vérifier le fonctionnement de `isMature()` et `isAvailable()`.

Les résultats sont tous corrects :

| Test                                  | Situation          | Résultat |
| ------------------------------------- | ------------------ | -------- |
| `testIsMatureReturnsTrueWhenFlagged`  | Produit mature     | `true`   |
| `testIsMatureReturnsFalseByDefault`   | Produit non mature | `false`  |
| `testIsAvailableWhenActiveAndInStock` | Actif + stock > 0  | `true`   |
| `testIsNotAvailableWhenInactive`      | Produit inactif    | `false`  |
| `testIsNotAvailableWhenOutOfStock`    | Stock = 0          | `false`  |

**Étape 1 terminée : 5 tests réussis sur 5.**
