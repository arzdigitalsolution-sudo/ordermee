<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class Category extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $name, ?int $parentId = null): int
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($name)) ?? '');
        $stmt = $this->db->prepare('INSERT INTO categories (name, slug, parent_id) VALUES (:name, :slug, :parent_id)');
        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
