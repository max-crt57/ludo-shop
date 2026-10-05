## 11 - Prix promotionnel au panier

### Objectif

Faire en sorte que le panier utilise le prix promotionnel d'un produit lorsqu'une promotion est active.

La fonctionnalité a été développée en suivant une démarche **TDD (Test-Driven Development)** :

**RED → GREEN → REFACTOR**

### 1. RED - Écriture du test

Un test unitaire `testPromotionalPriceIsUsed` a été ajouté dans :

```text
tests/Unit/CartServiceTest.php
```

Le test utilise un produit avec :

* Prix normal : **50 €**
* Prix promotionnel : **35 €**
* Début de promotion : `2026-01-01`
* Fin de promotion : `2099-01-01`
* Quantité ajoutée : **2**

Le résultat attendu est donc :

```text
35 × 2 = 70 €
```

Avant la modification du service, le test échoue :

```text
Failed asserting that 100.0 is identical to 70.0.
```

Capture d'écran disponible [ici](tests_red.png)

Le panier utilisait donc encore le prix normal de 50 €.

### 2. GREEN - Correction de `CartService`

`CartService` dépend maintenant de `PromotionService`.

Lors de l'ajout d'un produit, le prix enregistré dans `CartItem` est récupéré avec :

```php
$this->promotionService->getCurrentPrice($product)
```

Ainsi :

* si la promotion est active → le prix promotionnel est utilisé ;
* sinon → le prix normal est utilisé.

Le test `testPromotionalPriceIsUsed` passe alors avec :

```text
OK (1 test, 1 assertion)
```

Capture d'écran disponible [ici](tests_green.png)

### 3. PHPStan

Une analyse statique a été effectuée après la modification :

```text
php vendor/bin/phpstan analyse --no-progress
```

Résultat :

```text
[OK] No errors
```

### 4. REFACTOR - Vérification de `OrderService`

`OrderService` a été vérifié afin de rechercher le même problème.

Aucune modification n'était nécessaire : lors de la création d'une commande, `OrderService` récupère déjà le prix enregistré dans le panier :

```php
unitPrice: $item->getUnitPrice()
```

Le prix promotionnel est donc correctement transmis à la commande lorsque le panier l'a enregistré.

### 5. Tests complets

La suite complète des tests a ensuite été exécutée :

```text
php vendor/bin/phpunit --no-coverage
```

Résultat :

```text
Tests: 231, Assertions: 953, PHPUnit Notices: 24.
OK, but there were issues!
```

Capture d'écran disponible [ici](capture-assertions.png)

Les tests passent tous avec succès.

Le test ajouté pour le ticket est également visible dans le résultat `--testdox` :

```text
Cart Service (App\Tests\Unit\CartService)
 ✔ Empty cart returns zero
 ✔ Single item returns correct total
 ✔ Multiple items
 ✔ Quantity multiplier
 ✔ Promotional price is used
```

### Bilan

Le panier utilise maintenant automatiquement le **prix promotionnel lorsqu'une promotion est active**.

La fonctionnalité a été développée avec une démarche TDD :

```text
RED
 ↓
Test échoue : 100 € au lieu de 70 €
 ↓
GREEN
 ↓
Modification de CartService
 ↓
Test réussi
 ↓
REFACTOR
 ↓
Vérification de OrderService
 ↓
231 tests réussis
```

### Captures d'écran

Les captures demandées pour le projet final sont disponibles dans le dossier `rendu/3 captures`:

* `capture-merge-bloque.png` : preuve du blocage lorsque la CI n'est pas valide.
* `capture-assertions.png` : résultat de la suite PHPUnit avec **231 tests et 953 assertions**.
* `capture-pipeline-global.png` : pipeline GitHub Actions global avec tous les contrôles au vert.