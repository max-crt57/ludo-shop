# Étape 6 - Authentification : login réussi + échoué

## Objectif

Dans cette étape, j'ai réalisé des tests fonctionnels sur le système d'authentification de l'application.

L'objectif était de vérifier que :

* la page de connexion est accessible ;
* un utilisateur avec de bonnes identifiants peut se connecter ;
* une connexion avec un mauvais mot de passe affiche une erreur ;
* la déconnexion redirige correctement vers la page d'accueil.

Cette étape m'a également permis de manipuler des requêtes HTTP réelles avec Symfony et de récupérer le token CSRF du formulaire de connexion.

## Fichier créé

J'ai créé le fichier :

```text
tests/Functional/LoginTest.php
```

Le test hérite de `FunctionalTestCase`, ce qui me permet d'utiliser le client HTTP Symfony :

```php
class LoginTest extends FunctionalTestCase
```

## Tests réalisés

### 1. Vérification de la page de connexion

Avec `testLoginPageIsAccessible`, j'effectue une requête :

```text
GET /login
```

Je vérifie ensuite que la réponse est correcte avec :

```php
$this->assertResponseIsSuccessful();
```

Je vérifie également qu'au moins un formulaire est présent dans la page :

```php
$this->assertGreaterThan(0, $crawler->filter('form')->count());
```

### 2. Connexion réussie

Avec `testSuccessfulLoginRedirectsToCatalog`, je commence par récupérer la page `/login`.

Je récupère ensuite le token CSRF présent dans le formulaire :

```php
$csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');
```

J'envoie ensuite une requête POST avec les identifiants du compte de test :

```php
$this->client->request('POST', '/login', [
    '_username' => 'client@example.com',
    '_password' => 'Client123!',
    '_csrf_token' => $csrfToken,
]);
```

Je vérifie que la connexion provoque une redirection, puis que la page finale correspond à la route `app_home`.

### 3. Connexion échouée

Avec `testFailedLoginShowsError`, j'utilise le même principe mais avec un mauvais mot de passe :

```text
wrong-password
```

Je vérifie que la requête redirige vers la page de connexion et que le message d'erreur est présent :

```php
$this->assertSelectorTextContains('.alert-danger', 'Invalid credentials');
```

Cela permet de vérifier que l'utilisateur est correctement informé lorsque ses identifiants sont incorrects.

### 4. Déconnexion

J'ai ajouté le test demandé :

```php
public function testLogoutRedirectsToHome(): void
```

J'effectue une requête :

```text
GET /logout
```

Je vérifie ensuite la redirection et la route finale :

```php
$this->assertResponseRedirects();

$this->client->followRedirect();

$this->assertRouteSame('app_home');
```

## Points importants

### Token CSRF

Pour les requêtes POST, j'ai récupéré le token CSRF directement depuis le formulaire de connexion :

```php
$csrfToken = $crawler->filter('input[name="_csrf_token"]')->attr('value');
```

Je le renvoie ensuite dans la requête POST.

Cela permet de reproduire correctement le fonctionnement réel du formulaire de connexion.

### Suivi des redirections

J'ai utilisé :

```php
$this->client->followRedirect();
```

Cela permet de suivre la redirection HTTP afin de vérifier la page et la route réellement atteintes après la connexion ou la déconnexion.

## Vérification PHPUnit

J'ai lancé :

```bash
php vendor/bin/phpunit tests/Functional/LoginTest.php
```

Le résultat est disponible [ici](test-valide.png)

## Vérification PHP-CS-Fixer

J'ai d'abord appliqué le formatage avec :

```bash
php vendor/bin/php-cs-fixer fix tests/Functional/LoginTest.php
```

Le fichier a été corrigé.

J'ai ensuite vérifié l'ensemble du projet avec :

```bash
php vendor/bin/php-cs-fixer fix --dry-run --diff
```

Résultat :

```text
Found 0 of 137 files that can be fixed
```

Le code respecte donc les règles de formatage du projet.

> Le warning concernant l'utilisation de PHP 8.3 alors que le projet indique PHP 8.2 comme version minimale n'est pas une erreur et n'empêche pas les tests de fonctionner.

## Conclusion

Cette étape m'a permis de tester le fonctionnement complet du formulaire d'authentification avec des requêtes HTTP réelles.

J'ai vérifié les cas de connexion réussie, de connexion échouée, l'accessibilité de la page de login et la déconnexion. Les **4 tests et 9 assertions passent avec succès**
