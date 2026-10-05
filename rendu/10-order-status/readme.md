# Ticket 10 - Tests fonctionnels du changement de statut

## Objectif

Ajouter des tests fonctionnels pour vérifier le workflow de gestion du statut des commandes par un administrateur :

* permettre à un administrateur de modifier le statut d'une commande ;
* empêcher un client de modifier le statut ;
* vérifier la création d'une entrée dans l'historique des statuts ;
* vérifier l'envoi d'une notification par email lors du changement de statut.

## Fichier concerné

```text
tests/Functional/OrderStatusTest.php
```

## Tests réalisés

### `testAdminCanChangeStatus`

Vérifie qu'un administrateur peut modifier le statut d'une commande.

Le test crée d'abord une commande payée, connecte l'utilisateur avec le compte administrateur, puis change le statut de la commande vers :

```text
shipped
```

Le statut est ensuite vérifié directement depuis la base de données.

### `testClientCannotChangeStatus`

Vérifie qu'un utilisateur possédant le rôle client ne peut pas modifier le statut d'une commande.

Une tentative de modification est effectuée avec le compte client et doit retourner une erreur HTTP :

```text
403 Forbidden
```

### `testStatusChangeCreatesHistoryEntry`

Vérifie qu'un changement de statut entraîne la création d'une entrée dans l'historique des statuts.

Le test utilise l'entité :

```text
OrderStatusHistory
```

et vérifie qu'au moins une entrée est présente après le changement de statut.

### `testStatusChangeSendsEmail`

Vérifie qu'un email de notification est envoyé après la modification du statut.

Le test utilise le profiler Symfony et le collector `mailer` afin de vérifier qu'au moins un message a été généré.

## Méthodes utilitaires

Deux méthodes privées sont utilisées pour éviter de répéter le même code dans les tests.

### `createPaidOrder()`

Crée une commande complète :

1. connexion avec le compte client ;
2. ajout d'un produit au panier ;
3. accès au checkout ;
4. remplissage du formulaire ;
5. création de la commande ;
6. paiement de la commande ;
7. vérification que son statut est `paid`.

L'EntityManager est ensuite vidé avec `clear()` afin de récupérer une commande fraîche depuis la base de données.

### `changeStatus()`

Accède à la page d'administration de la commande, récupère le formulaire de changement de statut et le soumet avec le nouveau statut.

## Vérification

Commande utilisée pour exécuter les tests :

```powershell
php vendor/bin/phpunit tests/Functional/OrderStatusTest.php
```

Le résultat est disponible [ici](tests_valide.png)

## Résultat

Le ticket 10 est validé.

Les **4 tests fonctionnels passent avec succès**, avec **59 assertions validées**