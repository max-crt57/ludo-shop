# Test 5 - Commande console : `app:fixtures:check`

## Objectif

L'objectif de ce test est de vérifier le bon fonctionnement de la commande Symfony Console :

```text
app:fixtures:check
```

Cette commande vérifie la cohérence des données de démonstration présentes dans les fixtures.

Ce test permet de vérifier une commande console avec PHPUnit, et pas seulement une page web ou un service.

---

## Fichier créé

J'ai créé le fichier :

```text
tests/Functional/FixturesCheckCommandTest.php
```

---

## Principe du test

Pour tester la commande Symfony, j'utilise `CommandTester`.

`CommandTester` permet d'exécuter une commande Symfony directement depuis un test PHPUnit et de récupérer :

* son code de retour ;
* le texte affiché dans la console.

J'ai également construit manuellement `FixturesCheckCommand` en lui fournissant l'`EntityManagerInterface` nécessaire à son fonctionnement.

---

## Tests réalisés

J'ai réalisé deux tests.

### 1. Fixtures valides

Le premier test est :

```php
testCommandPassesOnValidFixtures()
```

Il exécute la commande avec les fixtures normales :

```php
$exitCode = $tester->execute([]);
```

Je vérifie ensuite que :

```php
$this->assertSame(0, $exitCode);
```

Le code `0` signifie que la commande s'est terminée correctement.

Je vérifie également que la sortie contient le mot :

```text
vérifications
```

Cela permet de confirmer que la commande a bien affiché son message de vérification.

---

### 2. Fixtures invalides

Le deuxième test est :

```php
testCommandFailsOnInvalidData()
```

Je récupère un produit présent dans les fixtures :

```php
$product = $this->repository(Product::class)->findOneBy([]);
```

Je modifie ensuite volontairement ses données pour créer une incohérence :

```php
$product->setIsMature(true);
$product->setMinAge(14);
```

Un produit mature doit avoir un âge minimum d'au moins 18 ans.

Je sauvegarde ensuite la modification :

```php
$this->entityManager()->flush();
$this->entityManager()->clear();
```

Puis j'exécute à nouveau la commande.

Cette fois, je vérifie que le code de retour est `1` :

```php
$this->assertSame(1, $exitCode);
```

Je vérifie également que la sortie contient le mot :

```text
mature
```

Cela confirme que la commande a bien détecté l'incohérence.

---

## Code du test

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Command\FixturesCheckCommand;
use App\Entity\Product;
use Symfony\Component\Console\Tester\CommandTester;

class FixturesCheckCommandTest extends FunctionalTestCase
{
    private function getTester(): CommandTester
    {
        return new CommandTester(new FixturesCheckCommand(
            $this->entityManager(),
        ));
    }

    public function testCommandPassesOnValidFixtures(): void
    {
        $tester = $this->getTester();

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('vérifications', $tester->getDisplay());
    }

    public function testCommandFailsOnInvalidData(): void
    {
        // Je corromps les fixtures : produit mature avec un âge minimum trop bas
        $product = $this->repository(Product::class)->findOneBy([]);

        $this->assertNotNull($product);

        $product->setIsMature(true);
        $product->setMinAge(14);

        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $tester = $this->getTester();

        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('mature', $tester->getDisplay());
    }
}
```

---

## Vérification

J'ai exécuté la commande :

```powershell
php vendor/bin/phpunit tests/Functional/FixturesCheckCommandTest.php
```

### Résultat obtenu

Le résultat est disponible [ici](tests-valide.png)

---

## Vérification du formatage

J'ai également vérifié le code avec PHP CS Fixer :

```powershell
php vendor/bin/php-cs-fixer fix --dry-run --diff
```

PHP CS Fixer a détecté uniquement l'absence d'un retour à la ligne final dans `FixturesCheckCommandTest.php`.

J'ai donc corrigé automatiquement le fichier avec :

```powershell
php vendor/bin/php-cs-fixer fix tests/Functional/FixturesCheckCommandTest.php
```

Le warning concernant PHP 8.3 alors que le projet indique PHP 8.2 comme version minimale est un avertissement de compatibilité de PHP CS Fixer et non une erreur du test.

---

## Conclusion

Avec ce test, j'ai vérifié que :

* la commande `app:fixtures:check` peut être testée avec PHPUnit ;
* `CommandTester` permet d'exécuter la commande et de récupérer sa sortie ;
* les fixtures valides provoquent un code de retour `0` ;
* une incohérence sur un produit mature est correctement détectée ;
* les fixtures invalides provoquent un code de retour `1` ;
* le message d'erreur est bien présent dans la sortie de la commande.

### Résultat final

| Élément            | Résultat          |
| ------------------ | ----------------- |
| Tests PHPUnit      | **2 / 2 réussis** |
| Assertions         | **5**             |
| Fixtures valides   | **Code 0**        |
| Fixtures invalides | **Code 1**        |
| `CommandTester`    | **Fonctionnel**   |
| Test 5             | **Validé**        |
