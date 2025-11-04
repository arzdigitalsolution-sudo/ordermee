<?php

declare(strict_types=1);

namespace ClickCart\Controllers;

use ClickCart\Helpers;
use ClickCart\Models\Product;
use ClickCart\Models\ProductImage;

class ProductController
{
    public function __construct(
        private Product $productModel = new Product(),
        private ProductImage $imageModel = new ProductImage()
    ) {
    }

    public function addProduct(int $sellerId, array $data, array $files = []): array
    {
        $required = ['title', 'price', 'quantity'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Missing field: ' . $field];
            }
        }

        $sku = $data['sku'] ?? 'CC-' . strtoupper(bin2hex(random_bytes(3)));

        $productId = $this->productModel->create([
            'seller_id' => $sellerId,
            'sku' => $sku,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'quantity' => $data['quantity'],
            'status' => $data['status'] ?? 'published',
            'category_id' => $data['category_id'] ?? null,
            'weight' => $data['weight'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
        ]);

        foreach ($files as $file) {
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $path = Helpers\store_upload($file, 'products/' . $sellerId);
            if ($path) {
                $this->imageModel->addImage($productId, $path, false);
            }
        }

        return ['success' => true, 'product_id' => $productId];
    }

    public function updateProduct(int $productId, int $sellerId, array $data, array $files = []): array
    {
        $updated = $this->productModel->update($productId, $sellerId, $data);

        foreach ($files as $file) {
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $path = Helpers\store_upload($file, 'products/' . $sellerId);
            if ($path) {
                $this->imageModel->addImage($productId, $path, false);
            }
        }

        return ['success' => $updated];
    }

    public function deleteProduct(int $productId, int $sellerId): bool
    {
        return $this->productModel->delete($productId, $sellerId);
    }
}
