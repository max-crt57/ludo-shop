# Étape 7 - Test fonctionnel du catalogue

## Objectif

Dans cette étape, j’ai créé des tests fonctionnels pour vérifier le fonctionnement du catalogue de produits.

Je vérifie notamment que :

* le catalogue affiche correctement les produits ;
* le filtre par catégorie fonctionne ;
* les produits inactifs ne sont pas affichés ;
* les produits matures sont cachés aux utilisateurs mineurs ;
* les produits matures restent accessibles aux utilisateurs majeurs.

## Fichier créé

J’ai créé le fichier :

```text
tests/Functional/CatalogTest.php
```

Le test utilise la classe `FunctionalTestCase`, qui permet de démarrer l’application Symfony avec une base de données de test et les fixtures.

## Tests réalisés

### 1. Affichage du catalogue

Avec :

```php
$this->client->request('GET', '/products');

$this->assertResponseIsSuccessful();
$this->assertSelectorTextContains('body', 'Catan');
```

Je vérifie que la page `/products` répond correctement et que le produit **Catan** est présent dans le catalogue.

### 2. Filtre par catégorie

J’ai testé la catégorie **Stratégie** avec la route :

```text
/categories/strategie
```

Les fixtures définissent bien la catégorie avec le slug `strategie`.

```php
$this->client->request('GET', '/categories/strategie');

$this->assertResponseIsSuccessful();
$this->assertSelectorTextContains('body', 'Catan');
$this->assertSelectorTextContains('body', '7 Wonders');
$this->assertSelectorTextNotContains('body', 'Dixit');
```

Je vérifie donc que :

* **Catan** est affiché ;
* **7 Wonders** est affiché ;
* **Dixit** n'est pas affiché car il n'appartient pas à la catégorie Stratégie.

### 3. Produit inactif

J’ai créé un produit de test directement dans la base de données avec :

```php
$inactive = new Product();
$inactive->setName('Produit Test');
$inactive->setReference('TEST-999');
$inactive->setPrice(10.00);
$inactive->setStock(5);
$inactive->setIsActive(false);
```

Après l'avoir enregistré, je vérifie qu'il n'apparaît pas dans le catalogue :

```php
$this->assertSelectorTextNotContains('body', 'Produit Test');
```

Cela permet de vérifier que les produits inactifs sont bien exclus du catalogue.

### 4. Produit mature pour un utilisateur mineur

Je me connecte avec l'utilisateur de fixture :

```text
minor@example.com
```

Puis je récupère le produit `LIM-001`, correspondant à **Limite Limite**.

Je vérifie ensuite que l'accès à sa page retourne une erreur 404 :

```php
$this->assertResponseStatusCodeSame(404);
```

Cela permet de vérifier qu'un utilisateur mineur ne peut pas accéder à un produit mature.

### 5. Produit mature pour un utilisateur majeur

Je me connecte cette fois avec :

```text
client@example.com
```

Puis j'accède au même produit `LIM-001`.

Je vérifie que la page est accessible et que le produit **Limite Limite** est bien affiché :

```php
$this->assertResponseIsSuccessful();
$this->assertSelectorTextContains('body', 'Limite Limite');
```

Cela permet de vérifier qu'un utilisateur majeur peut accéder aux produits matures.

## Vérification

J'ai exécuté la commande :

```powershell
php vendor/bin/phpunit tests/Functional/CatalogTest.php
```

Le résultat est disponible [ici](tests-valide.png)

Il correspond au test du produit mature pour un utilisateur mineur. Cette réponse 404 est volontaire et le test est bien considéré comme réussi.

## Conclusion

Cette étape m'a permis de vérifier le fonctionnement du catalogue avec plusieurs situations différentes.

J'ai testé l'affichage des produits, le filtrage par catégorie, la gestion des produits inactifs ainsi que les restrictions d'accès aux produits matures selon l'âge de l'utilisateur.

Les **5 tests et les 20 assertions** sont validés avec PHPUnit