<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class JwtService
{
    /**
     * Gera um token JWT com os dados do usuário
     *
     * @param string $username
     * @return string
     */
    public function generateToken(string $username)
    {
        $now = time();
        $ttl = config('app.jwt_ttl', 60);
        
        $payload = [
            'iss' => config('app.url'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + ($ttl * 60),
            'jti' => bin2hex(random_bytes(16)),
            'username' => $username,
        ];
        
        return JWT::encode(
            $payload,
            config('app.jwt_key'),
            config('app.jwt_algo')
        );
    }
    
    /**
     * Decodifica um token JWT
     *
     * @param string $token
     * @return object
     * @throws \Exception
     */
    public function decodeToken($token)
    {
        if ($this->isTokenBlacklisted($token)) {
            throw new \Exception('Token invalidado por logout');
        }
        
        return JWT::decode(
            $token,
            new Key(config('app.jwt_key'), config('app.jwt_algo', 'HS256'))
        );
    }
    
    /**
     * Adiciona um token à blacklist
     *
     * @param string $token
     * @return void
     */
    public function blacklistToken($token)
    {
        try {
            $decoded = $this->decodeToken($token);
            $tokenHash = hash('sha256', $token);
            $expiration = Carbon::createFromTimestamp($decoded->exp);
            
            Cache::put('blacklist_' . $tokenHash, true, $expiration);
        } catch (\Exception $e) {
            // Se não conseguir decodificar, apenas ignora
        }
    }
    
    /**
     * Verifica se um token está na blacklist
     *
     * @param string $token
     * @return bool
     */
    public function isTokenBlacklisted($token)
    {
        $tokenHash = hash('sha256', $token);
        return Cache::has('blacklist_' . $tokenHash);
    }
    
    /**
     * Verifica se o token é válido
     *
     * @param string|null $token
     * @return bool
     */
    public function validateToken($token)
    {
        if (!$token) {
            return false;
        }
        
        try {
            $this->decodeToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtém o usuário do token atual
     *
     * @param string $token
     * @return object|null
     */
    public function getUser($token)
    {
        try {
            return $this->decodeToken($token);
        } catch (\Exception $e) {
            return null;
        }
    }
}