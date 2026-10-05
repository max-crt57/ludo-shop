<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

class CheckoutTest extends FunctionalTestCase
{
    public function testCheckoutPageRequiresCart(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/checkout');

        $this->assertResponseRedirects('/cart');
    }

    public function testCheckoutCreatesOrder(): void
    {
        $this->login('client@example.com');

        $user = $this->findUser('client@example.com');

        // Ajouter un produit au panier
        $product = $this->repository(Product::class)
            ->findOneBy(['reference' => 'CAT-001']);

        $this->assertNotNull($product);

        $cartService = $this->client
            ->getContainer()
            ->get(CartService::class);

        $cart = $cartService->getOrCreateCart($user);

        $cartService->addProduct($cart, $product, 1);

        // Afficher le formulaire de checkout pour récupérer le token CSRF
        $crawler = $this->client->request('GET', '/checkout');

        $this->assertResponseIsSuccessful();

        $token = $crawler
            ->filter('input[name="checkout_form[_token]"]')
            ->attr('value');

        $this->assertNotNull($token);

        // Envoyer le formulaire
        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        // Vérifier la création de la commande
        $this->assertResponseRedirects();

        $order = $this->repository(Order::class)
            ->findOneBy(['user' => $user]);

        $this->assertNotNull($order);
        $this->assertSame('pending', $order->getStatus()->value);
    }

    public function testPaymentCreatesPaidOrder(): void
    {
        $this->login('client@example.com');

        $user = $this->findUser('client@example.com');

        // Ajouter un produit au panier
        $product = $this->repository(Product::class)
            ->findOneBy(['reference' => 'CAT-001']);

        $this->assertNotNull($product);

        $cartService = $this->client
            ->getContainer()
            ->get(CartService::class);

        $cart = $cartService->getOrCreateCart($user);

        $cartService->addProduct($cart, $product, 1);

        // Récupérer le token CSRF du checkout
        $crawler = $this->client->request('GET', '/checkout');

        $this->assertResponseIsSuccessful();

        $token = $crawler
            ->filter('input[name="checkout_form[_token]"]')
            ->attr('value');

        $this->assertNotNull($token);

        // Créer la commande
        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects();

        $order = $this->repository(Order::class)
            ->findOneBy(['user' => $user]);

        $this->assertNotNull($order);
        $this->assertSame('pending', $order->getStatus()->value);

        // Payer la commande
        $this->client->request(
            'POST',
            '/orders/'.$order->getId().'/pay'
        );

        $this->assertResponseRedirects();

        // Vider le cache Doctrine avant de relire la commande
        $this->entityManager()->clear();

        $paidOrder = $this->repository(Order::class)
            ->find($order->getId());

        $this->assertNotNull($paidOrder);
        $this->assertSame('paid', $paidOrder->getStatus()->value);
    }

    public function testConfirmationPageIsDisplayed(): void
    {
        $this->login('client@example.com');

        $user = $this->findUser('client@example.com');

        // Ajouter un produit au panier
        $product = $this->repository(Product::class)
            ->findOneBy(['reference' => 'CAT-001']);

        $this->assertNotNull($product);

        $cartService = $this->client
            ->getContainer()
            ->get(CartService::class);

        $cart = $cartService->getOrCreateCart($user);

        $cartService->addProduct($cart, $product, 1);

        // Récupérer le token CSRF
        $crawler = $this->client->request('GET', '/checkout');

        $this->assertResponseIsSuccessful();

        $token = $crawler
            ->filter('input[name="checkout_form[_token]"]')
            ->attr('value');

        $this->assertNotNull($token);

        // Créer la commande
        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects();

        $order = $this->repository(Order::class)
            ->findOneBy(['user' => $user]);

        $this->assertNotNull($order);

        // Payer la commande
        $this->client->request(
            'POST',
            '/orders/'.$order->getId().'/pay'
        );

        $this->assertResponseRedirects();

        // Accéder à la page de confirmation
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains(
            'body',
            (string) $order->getId()
        );
    }
}
