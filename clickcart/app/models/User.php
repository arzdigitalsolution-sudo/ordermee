<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class User extends BaseModel
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (name, email, password, role, brand_name, brand_logo, profile_image, bio, phone) 
                VALUES (:name, :email, :password, :role, :brand_name, :brand_logo, :profile_image, :bio, :phone)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'role' => $data['role'] ?? 'buyer',
            'brand_name' => $data['brand_name'] ?? null,
            'brand_logo' => $data['brand_logo'] ?? null,
            'profile_image' => $data['profile_image'] ?? null,
            'bio' => $data['bio'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower($email)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = sprintf('%s = :%s', $key, $key);
            $params[$key] = $value;
        }

        if (!$fields) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function incrementSales(int $sellerId, int $qty): void
    {
        $stmt = $this->db->prepare('UPDATE users SET total_sales = total_sales + :qty WHERE id = :id');
        $stmt->execute(['qty' => $qty, 'id' => $sellerId]);
    }

    public function markPlatformFeePaid(int $sellerId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET platform_fee_paid = 1 WHERE id = :id');
        $stmt->execute(['id' => $sellerId]);
    }

    public function resetPlatformFee(int $sellerId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET platform_fee_paid = 0 WHERE id = :id');
        $stmt->execute(['id' => $sellerId]);
    }

    public function listSellers(): array
    {
        $stmt = $this->db->query('SELECT id, brand_name, brand_logo, bio, total_sales, platform_fee_paid FROM users WHERE role = "seller" ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
