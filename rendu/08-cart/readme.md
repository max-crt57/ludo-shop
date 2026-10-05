# Ticket 8 - Tests fonctionnels du panier

## Objectif

L'objectif de ce ticket est de tester le parcours utilisateur du panier avec plusieurs actions successives :

* ajouter un produit au panier ;
* modifier sa quantité ;
* supprimer un produit ;
* vérifier que le panier affiche correctement son total.

Le panier utilisant la session et nécessitant un utilisateur connecté, les tests sont réalisés avec un utilisateur de test.

## Fichier créé

Le fichier suivant a été créé :

```text
tests/Functional/CartTest.php
```

## Tests réalisés

### 1. Ajout d'un produit

Le test `testAddProductToCart()` :

* connecte l'utilisateur `client@example.com` ;
* récupère le produit `CAT-001` en base de données ;
* effectue une requête `POST` vers `/cart/add/{id}` ;
* vérifie que la requête provoque une redirection ;
* suit la redirection ;
* vérifie que le produit **Catan** apparaît dans le panier.

### 2. Modification de la quantité

Le test `testCartQuantityIsUpdated()` :

* connecte l'utilisateur ;
* récupère le panier ;
* ajoute le produit avec une quantité initiale de 1 ;
* récupère le `CartItem` créé ;
* effectue une requête `POST` vers `/cart/items/{id}/update` avec une quantité de 3 ;
* vérifie la redirection ;
* vérifie que la nouvelle quantité est affichée.

### 3. Suppression d'un produit

Le test `testRemoveProductFromCart()` :

* connecte l'utilisateur ;
* ajoute le produit au panier ;
* récupère le `CartItem` correspondant ;
* effectue une requête `POST` vers `/cart/items/{id}/remove` ;
* vérifie la redirection ;
* vérifie que le produit **Catan** n'apparaît plus dans le panier.

### 4. Vérification du total

Le test `testCartShowsCorrectTotal()` :

* connecte l'utilisateur ;
* récupère le produit `CAT-001` ;
* ajoute 2 exemplaires au panier ;
* accède à `/cart` ;
* vérifie que la page est accessible ;
* vérifie que l'élément `#cart-total` contenant le total du panier existe.

## Résultat

Les quatre tests fonctionnels passent avec succès :

Résultat [ici](tests-valide.png)

Commande utilisée pour vérifier les tests :

```bash
php vendor/bin/phpunit tests/Functional/CartTest.php
```

Le ticket 8 est donc validé.