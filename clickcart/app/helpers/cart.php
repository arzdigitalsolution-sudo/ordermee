<?php

declare(strict_types=1);

namespace ClickCart\Helpers\Cart;

use ClickCart\Helpers;

const CART_KEY = 'shopping_cart';

function get_cart(): array
{
    Helpers\session_start_secure();
    return $_SESSION[CART_KEY] ?? [];
}

function add_to_cart(int $productId, int $sellerId, string $title, float $price, int $qty = 1, ?string $image = null): void
{
    Helpers\session_start_secure();
    $cart = $_SESSION[CART_KEY] ?? [];

    if (isset($cart[$productId])) {
        $cart[$productId]['qty'] += $qty;
    } else {
        $cart[$productId] = [
            'product_id' => $productId,
            'seller_id' => $sellerId,
            'title' => $title,
            'price' => $price,
            'qty' => $qty,
            'image' => $image,
        ];
    }

    $_SESSION[CART_KEY] = $cart;
}

function remove_from_cart(int $productId): void
{
    Helpers\session_start_secure();
    $cart = $_SESSION[CART_KEY] ?? [];
    unset($cart[$productId]);
    $_SESSION[CART_KEY] = $cart;
}

function update_qty(int $productId, int $qty): void
{
    Helpers\session_start_secure();
    $cart = $_SESSION[CART_KEY] ?? [];
    if (isset($cart[$productId])) {
        $cart[$productId]['qty'] = $qty;
    }
    $_SESSION[CART_KEY] = $cart;
}

function clear_cart(): void
{
    Helpers\session_start_secure();
    unset($_SESSION[CART_KEY]);
}

function cart_totals(): array
{
    $cart = get_cart();
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    return [
        'subtotal' => $subtotal,
        'item_count' => count($cart),
    ];
}

function group_by_seller(): array
{
    $cart = get_cart();
    $grouped = [];
    foreach ($cart as $item) {
        $grouped[$item['seller_id']][] = $item;
    }
    return $grouped;
}
