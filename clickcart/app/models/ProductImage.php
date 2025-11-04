<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class ProductImage extends BaseModel
{
    public function addImage(int $productId, string $imageUrl, bool $isPrimary = false): int
    {
        $stmt = $this->db->prepare('INSERT INTO product_images (product_id, image_url, is_primary) VALUES (:product_id, :image_url, :is_primary)');
        $stmt->execute([
            'product_id' => $productId,
            'image_url' => $imageUrl,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByProduct(int $productId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC');
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByProduct(int $productId): void
    {
        $stmt = $this->db->prepare('DELETE FROM product_images WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);
    }
}
