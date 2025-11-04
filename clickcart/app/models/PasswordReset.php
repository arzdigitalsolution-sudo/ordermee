<?php

declare(strict_types=1);

namespace ClickCart\Models;

use PDO;

class PasswordReset extends BaseModel
{
    public function createToken(int $userId, string $token, string $expiresAt): void
    {
        $stmt = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)');
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE token = :token AND used_at IS NULL LIMIT 1');
        $stmt->execute(['token' => $token]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
