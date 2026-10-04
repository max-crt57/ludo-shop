# Test 4 - Validation du schéma Doctrine

## Objectif

L’objectif de ce test est de vérifier que le schéma de la base de données est bien synchronisé avec les métadonnées définies dans mes entités Doctrine.

Ce test permet donc de vérifier la cohérence de l’infrastructure de la base de données. Il ne teste pas une fonctionnalité métier de l’application.

---

## Fichier créé

J’ai créé le fichier :

```text
tests/Functional/SchemaValidationTest.php
```

Le test hérite de `FunctionalTestCase`, qui permet notamment de démarrer l’environnement Symfony et de préparer une base de données propre avant l'exécution du test.

---

## Test réalisé

J’ai utilisé `SchemaValidator` de Doctrine pour vérifier que le schéma de la base de données correspond bien aux métadonnées de mes entités.

```php
<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;

class SchemaValidationTest extends FunctionalTestCase
{
    public function testSchemaIsSynchronized(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->entityManager();

        $validator = new SchemaValidator($em);

        $this->assertTrue(
            $validator->schemaInSyncWithMetadata(),
            'Le schéma Doctrine n\'est pas synchronisé avec les métadonnées.',
        );
    }
}
```

La méthode :

```php
$validator->schemaInSyncWithMetadata()
```

retourne `true` lorsque le schéma de la base de données est correctement synchronisé avec les métadonnées Doctrine.

---

## Vérification

J’ai exécuté la commande suivante :

```powershell
php vendor/bin/phpunit tests/Functional/SchemaValidationTest.php
```

### Résultat obtenu

Le résultat est disponible juste [ici](test_valide.png)

Le test est donc validé.

---

## Conclusion

Avec ce test, j’ai vérifié que :

* le démarrage de l’environnement fonctionnel fonctionne ;
* Doctrine arrive à récupérer les métadonnées de mes entités ;
* la base de données de test est correctement initialisée ;
* le schéma de la base est synchronisé avec les entités Doctrine ;
* le test `testSchemaIsSynchronized` passe avec succès.

**Résultat final : 1 test réussi sur 1, soit 100 %**