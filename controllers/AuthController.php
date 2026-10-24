<?php

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private ?string $tokenSesi;
    private ?string $waktuLogin;

    public function __construct(?string $tokenSesi = null, ?string $waktuLogin = null) {
        $this->tokenSesi = $tokenSesi;
        $this->waktuLogin = $waktuLogin;
    }

    // +boolean register(User data)
    public function register(User $data): bool {
        // Skeleton: Validasi dan registrasi user baru
        return true;
    }

    // +boolean login(String email, String pass)
    public function login(string $email, string $pass): bool {
        // Skeleton: Validasi kredensial dan set session/token login
        return true;
    }

    // +void logout()
    public function logout(): void {
        // Skeleton: Hapus session dan reset token login
    }

    // Getters and Setters
    public function getTokenSesi(): ?string {
        return $this->tokenSesi;
    }

    public function setTokenSesi(?string $tokenSesi): void {
        $this->tokenSesi = $tokenSesi;
    }

    public function getWaktuLogin(): ?string {
        return $this->waktuLogin;
    }

    public function setWaktuLogin(?string $waktuLogin): void {
        $this->waktuLogin = $waktuLogin;
    }
}
