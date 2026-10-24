<?php

class User {
    private ?int $user_id;
    private ?string $nama;
    private ?string $email;
    private ?string $password;
    private ?string $security_question;
    private ?string $security_answer;
    private ?string $role;

    public function __construct(
        ?int $user_id = null,
        ?string $nama = null,
        ?string $email = null,
        ?string $password = null,
        ?string $security_question = null,
        ?string $security_answer = null,
        ?string $role = 'customer'
    ) {
        $this->user_id = $user_id;
        $this->nama = $nama;
        $this->email = $email;
        $this->password = $password;
        $this->security_question = $security_question;
        $this->security_answer = $security_answer;
        $this->role = $role;
    }

    // +boolean updateProfil()
    public function updateProfil(): bool {
        // Skeleton: Logika update profil pengguna ke database
        return true;
    }

    // Getters and Setters
    public function getUserId(): ?int {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void {
        $this->user_id = $user_id;
    }

    public function getNama(): ?string {
        return $this->nama;
    }

    public function setNama(?string $nama): void {
        $this->nama = $nama;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(?string $password): void {
        $this->password = $password;
    }

    public function getSecurityQuestion(): ?string {
        return $this->security_question;
    }

    public function setSecurityQuestion(?string $security_question): void {
        $this->security_question = $security_question;
    }

    public function getSecurityAnswer(): ?string {
        return $this->security_answer;
    }

    public function setSecurityAnswer(?string $security_answer): void {
        $this->security_answer = $security_answer;
    }

    public function getRole(): ?string {
        return $this->role;
    }

    public function setRole(?string $role): void {
        $this->role = $role;
    }
}
