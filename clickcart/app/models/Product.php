<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class Product extends BaseModel
{
    public function listPublished(int $limit = 20, int $offset = 0, ?int $sellerId = null): array
    {
        $sql = 'SELECT p.*, u.brand_name, u.brand_logo 
                FROM products p 
                INNER JOIN users u ON u.id = p.seller_id 
                WHERE p.status = "published"';
        $params = [];

        if ($sellerId) {
            $sql .= ' AND p.seller_id = :seller_id';
            $params['seller_id'] = $sellerId;
        }

        $sql .= ' ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPublished(?int $sellerId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE status = "published"';
        $params = [];
        if ($sellerId) {
            $sql .= ' AND seller_id = :seller_id';
            $params['seller_id'] = $sellerId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, u.brand_name, u.brand_logo, u.bio FROM products p INNER JOIN users u ON u.id = p.seller_id WHERE p.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO products (seller_id, sku, title, description, price, sale_price, quantity, status, category_id, weight, dimensions) 
                VALUES (:seller_id, :sku, :title, :description, :price, :sale_price, :quantity, :status, :category_id, :weight, :dimensions)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'seller_id' => $data['seller_id'],
            'sku' => $data['sku'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'status' => $data['status'] ?? 'published',
            'category_id' => $data['category_id'] ?? null,
            'weight' => $data['weight'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $sellerId, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id, 'seller_id' => $sellerId];

        foreach ($data as $key => $value) {
            $fields[] = sprintf('%s = :%s', $key, $key);
            $params[$key] = $value;
        }

        if (!$fields) {
            return false;
        }

        $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id AND seller_id = :seller_id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id, int $sellerId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id AND seller_id = :seller_id');
        return $stmt->execute(['id' => $id, 'seller_id' => $sellerId]);
    }

    public function listBySeller(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE seller_id = :seller_id ORDER BY created_at DESC');
        $stmt->execute(['seller_id' => $sellerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reduceStock(int $productId, int $qty): void
    {
        $stmt = $this->db->prepare('UPDATE products SET quantity = GREATEST(quantity - :qty, 0) WHERE id = :id');
        $stmt->execute(['qty' => $qty, 'id' => $productId]);
    }
}
