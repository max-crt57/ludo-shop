# Ticket 9 - Tests fonctionnels du checkout

## Objectif

Ajouter des tests fonctionnels pour vérifier le bon fonctionnement du parcours de commande :

- accès à la page de checkout ;
- création d'une commande depuis le panier ;
- paiement d'une commande ;
- affichage de la page de confirmation.

## Fichier concerné

```text
tests/Functional/CheckoutTest.php
````

## Tests réalisés

### `testCheckoutPageRequiresCart`

Vérifie que lorsqu'un utilisateur accède au checkout sans avoir de panier contenant un produit, il est redirigé vers la page du panier.

### `testCheckoutCreatesOrder`

Vérifie qu'un utilisateur peut :

1. ajouter un produit à son panier ;
2. accéder au checkout ;
3. remplir le formulaire de livraison ;
4. créer une commande.

Le test vérifie ensuite que la commande existe et que son statut initial est `pending`.

### `testPaymentCreatesPaidOrder`

Vérifie qu'une commande créée peut être payée.

Le test contrôle que son statut passe de :

```text
pending
```

à :

```text
paid
```

Après le paiement, l'EntityManager est vidé avec `clear()` puis la commande est récupérée depuis la base de données afin de vérifier que la modification a bien été persistée.

### `testConfirmationPageIsDisplayed`

Vérifie qu'après le paiement d'une commande, la page de confirmation est correctement affichée et contient l'identifiant de la commande.

## Vérification

Commande utilisée pour exécuter les tests :

```powershell
php vendor/bin/phpunit tests/Functional/CheckoutTest.php
```

Le résultat est disponible [ici](test_valide.png)

## Résultat

Le ticket 9 est validé.

Les **4 tests fonctionnels passent avec succès**