<?php

declare(strict_types=1);

namespace ClickCart\Controllers;

use ClickCart\Helpers;
use ClickCart\Models\User;
use ClickCart\Models\PasswordReset;
use ClickCart\Helpers\Email;

class AuthController
{
    public function __construct(
        private User $userModel = new User(),
        private PasswordReset $passwordResetModel = new PasswordReset()
    )
    {
    }

    public function registerSeller(array $data): array
    {
        if (!$this->validateSellerRegistration($data)) {
            return ['success' => false, 'message' => 'Invalid input data'];
        }

        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $sellerId = $this->userModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => 'seller',
            'brand_name' => $data['brand_name'],
            'brand_logo' => $data['brand_logo'] ?? null,
            'profile_image' => $data['profile_image'] ?? null,
            'bio' => $data['bio'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return ['success' => true, 'seller_id' => $sellerId];
    }

    public function registerBuyer(array $data): array
    {
        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $buyerId = $this->userModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role' => 'buyer',
        ]);

        return ['success' => true, 'buyer_id' => $buyerId];
    }

    public function attemptLogin(string $email, string $password): array
    {
        if (!Helpers\rate_limit('login:' . $email, 5, 60)) {
            return ['success' => false, 'message' => 'Too many attempts. Please try again later.'];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        Helpers\clear_rate_limit('login:' . $email);
        Helpers\set_auth_user($user);
        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        Helpers\logout();
    }

    public function sendPasswordReset(string $email): void
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $this->passwordResetModel->createToken((int)$user['id'], $token, $expiresAt);

        $resetLink = Helpers\app_url('auth/reset.php?token=' . $token);
        $message = '<p>We received a password reset request for your ClickCart.pk account.</p>' .
            '<p><a href="' . $resetLink . '">Click here to reset your password</a>. The link expires in 60 minutes.</p>';
        Email\send_mail($user['email'], 'Password Reset', $message);
    }

    public function resetPassword(string $token, string $password): bool
    {
        $record = $this->passwordResetModel->findByToken($token);
        if (!$record) {
            return false;
        }

        if (strtotime($record['expires_at']) < time()) {
            return false;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->updateProfile((int)$record['user_id'], ['password' => $hashed]);
        $this->passwordResetModel->markUsed((int)$record['id']);
        return true;
    }

    private function validateSellerRegistration(array $data): bool
    {
        $required = ['name', 'email', 'password', 'brand_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false && strlen($data['password']) >= 8;
    }
}
