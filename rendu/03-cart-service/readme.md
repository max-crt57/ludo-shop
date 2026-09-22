# Étape 3 - calcul du total

## Objectif

Dans cette étape, j'ai testé la méthode `CartService::getTotal()` avec des tests unitaires.

Cette méthode permet de calculer le montant total d'un panier en additionnant le total de chaque article présent dans celui-ci.

L'objectif était de vérifier plusieurs situations :

* un panier vide ;
* un panier contenant un seul produit ;
* un panier contenant plusieurs produits ;
* un produit présent en plusieurs exemplaires.

Cette étape m'a également permis d'utiliser les **stubs PHPUnit** afin d'isoler le service de ses dépendances Doctrine.

---

## 1. Vérification de `CartService`

Avant de créer mes tests, j'ai vérifié le fonctionnement de `CartService`.

Le constructeur du service nécessite une dépendance `EntityManagerInterface` :

```php
public function __construct(private readonly EntityManagerInterface $entityManager)
{
}
```

La méthode que je dois tester est :

```php
public function getTotal(Cart $cart): float
{
    $total = 0.0;

    foreach ($cart->getItems() as $item) {
        $total += $item->getLineTotal();
    }

    return round($total, 2);
}
```

J'ai donc vérifié que le calcul repose sur le total de chaque `CartItem`.

---

## 2. Création du fichier de test

J'ai créé le fichier :

```text
tests/Unit/CartServiceTest.php
```

J'ai utilisé les classes nécessaires :

```php
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
```

---

## 3. Utilisation d'un stub pour Doctrine

`CartService` utilise `EntityManagerInterface`, mais je ne veux pas utiliser réellement Doctrine dans un test unitaire.

J'ai donc créé un stub :

```php
$em = $this->createStub(EntityManagerInterface::class);
```

Puis j'ai créé mon service avec ce faux `EntityManager` :

```php
$this->service = new CartService($em);
```

J'utilise également un stub pour créer un utilisateur sans avoir besoin de configurer réellement un `User` :

```php
$user = $this->createStub(User::class);
```

Je peux ensuite créer mon panier :

```php
$cart = new Cart($user);
```

Le but est donc de tester uniquement le fonctionnement de `CartService::getTotal()` sans dépendre de la base de données ou de Doctrine.

---

## 4. Test d'un panier vide

J'ai commencé par tester le cas d'un panier ne contenant aucun article.

```php
public function testEmptyCartReturnsZero(): void
{
    $user = $this->createStub(User::class);
    $cart = new Cart($user);

    $this->assertSame(0.0, $this->service->getTotal($cart));
}
```

J'attends que le total d'un panier vide soit `0.0`.

---

## 5. Test avec un seul produit

J'ai ensuite testé un panier contenant un seul produit.

J'ai créé un produit `Catan` à `25 €`, puis un `CartItem` avec une quantité de `1` :

```php
public function testSingleItemReturnsCorrectTotal(): void
{
    $user = $this->createStub(User::class);
    $cart = new Cart($user);

    $product = new Product();
    $product->setName('Catan');
    $product->setPrice(25.00);

    $item = new CartItem($product);
    $item->setQuantity(1);
    $item->setUnitPrice(25.00);

    $cart->addItem($item);

    $this->assertSame(25.00, $this->service->getTotal($cart));
}
```

Le résultat attendu est donc :

```text
25 × 1 = 25 €
```

---

## 6. Test avec plusieurs produits

J'ai ensuite créé un test avec deux produits différents.

Le premier produit coûte `25 €` et le deuxième coûte `30 €`.

```php
public function testMultipleItems(): void
{
    $user = $this->createStub(User::class);
    $cart = new Cart($user);

    $product1 = new Product();
    $product1->setName('Catan');
    $product1->setPrice(25.00);

    $item1 = new CartItem($product1);
    $item1->setQuantity(1);
    $item1->setUnitPrice(25.00);

    $product2 = new Product();
    $product2->setName('Dixit');
    $product2->setPrice(30.00);

    $item2 = new CartItem($product2);
    $item2->setQuantity(1);
    $item2->setUnitPrice(30.00);

    $cart->addItem($item1);
    $cart->addItem($item2);

    $this->assertSame(55.00, $this->service->getTotal($cart));
}
```

Le résultat attendu est :

```text
25 + 30 = 55 €
```

Cela permet de vérifier que le service additionne bien les différents articles du panier.

---

## 7. Test du multiplicateur de quantité

Enfin, j'ai vérifié que la quantité d'un produit est correctement prise en compte.

J'ai créé un produit à `25 €` avec une quantité de `3` :

```php
public function testQuantityMultiplier(): void
{
    $user = $this->createStub(User::class);
    $cart = new Cart($user);

    $product = new Product();
    $product->setName('Catan');
    $product->setPrice(25.00);

    $item = new CartItem($product);
    $item->setQuantity(3);
    $item->setUnitPrice(25.00);

    $cart->addItem($item);

    $this->assertSame(75.00, $this->service->getTotal($cart));
}
```

Le résultat attendu est :

```text
25 × 3 = 75 €
```

Cela permet de vérifier que `getTotal()` prend bien en compte la quantité de chaque article.

---

## 8. Fichier final

Mon fichier `tests/Unit/CartServiceTest.php` contient donc les quatre tests :

```php
<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);

        $this->service = new CartService($em);
    }

    public function testEmptyCartReturnsZero(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $this->assertSame(0.0, $this->service->getTotal($cart));
    }

    public function testSingleItemReturnsCorrectTotal(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);

        $cart->addItem($item);

        $this->assertSame(25.00, $this->service->getTotal($cart));
    }

    public function testMultipleItems(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product1 = new Product();
        $product1->setName('Catan');
        $product1->setPrice(25.00);

        $item1 = new CartItem($product1);
        $item1->setQuantity(1);
        $item1->setUnitPrice(25.00);

        $product2 = new Product();
        $product2->setName('Dixit');
        $product2->setPrice(30.00);

        $item2 = new CartItem($product2);
        $item2->setQuantity(1);
        $item2->setUnitPrice(30.00);

        $cart->addItem($item1);
        $cart->addItem($item2);

        $this->assertSame(55.00, $this->service->getTotal($cart));
    }

    public function testQuantityMultiplier(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(3);
        $item->setUnitPrice(25.00);

        $cart->addItem($item);

        $this->assertSame(75.00, $this->service->getTotal($cart));
    }
}
```

---

## 9. Vérification des tests

J'ai lancé la commande suivante :

```bash
php vendor/bin/phpunit tests/Unit/CartServiceTest.php
```

Cette commande permet de lancer uniquement les tests de `CartServiceTest.php`.

J'ai également vérifié le formatage du projet avec PHP CS Fixer :

```bash
php vendor/bin/php-cs-fixer fix --dry-run --diff
```

Le résultat obtenu pour PHP CS Fixer est :

```text
Found 0 of 134 files that can be fixed
```

Cela signifie qu'aucune correction de formatage n'est nécessaire.

Le message concernant PHP 8.3.33 est un avertissement indiquant que le projet définit PHP 8.2 comme version minimale. Il ne bloque pas le contrôle de formatage.

---

## 10. Bilan

Avec cette étape, j'ai appris à écrire des tests unitaires pour un service qui possède une dépendance externe.

J'ai notamment utilisé :

* `createStub()` pour simuler `EntityManagerInterface` ;
* `createStub()` pour créer un `User` sans dépendance réelle ;
* `Cart` et `CartItem` pour construire un panier de test ;
* `assertSame()` pour vérifier les montants calculés.

J'ai testé les quatre situations demandées :

| Test                                | Situation     | Résultat attendu |
| ----------------------------------- | ------------- | ---------------: |
| `testEmptyCartReturnsZero`          | Panier vide   |         `0.00 €` |
| `testSingleItemReturnsCorrectTotal` | 1 produit × 1 |        `25.00 €` |
| `testMultipleItems`                 | 2 produits    |        `55.00 €` |
| `testQuantityMultiplier`            | 1 produit × 3 |        `75.00 €` |

L'étape 3 m'a donc permis de tester le calcul du total du panier tout en isolant `CartService` de Doctrine grâce aux stubs PHPUnit.