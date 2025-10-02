<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/jwt.php';

final class JwtTest extends TestCase
{
    private string $secret = "clave_super_secreta";

    public function testGenerarYValidarTokenCorrecto(): void
    {
        $payload = ["id" => 1, "rol" => "usuario", "exp" => time() + 3600];
        $jwt = generarJWT($payload, $this->secret);

        $validado = validarJWT($jwt, $this->secret);

        $this->assertIsArray($validado);
        $this->assertSame(1, $validado['id']);
        $this->assertSame("usuario", $validado['rol']);
    }

    public function testTokenExpiradoNoEsValido(): void
    {
        $payload = ["id" => 1, "rol" => "usuario", "exp" => time() - 10];
        $jwt = generarJWT($payload, $this->secret);

        $this->assertFalse(validarJWT($jwt, $this->secret));
    }

    public function testTokenAlteradoNoEsValido(): void
    {
        $payload = ["id" => 1, "rol" => "admin", "exp" => time() + 3600];
        $jwt = generarJWT($payload, $this->secret);

        // alteramos la firma
        $partes = explode(".", $jwt);
        $jwtFalso = $partes[0] . "." . $partes[1] . ".firmaFalsa";

        $this->assertFalse(validarJWT($jwtFalso, $this->secret));
    }
}
