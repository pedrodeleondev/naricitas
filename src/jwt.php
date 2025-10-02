<?php
$secret = "clave_super_secreta";

function generarJWT($payload, $secret) {
    $header = json_encode(['alg'=>'HS256','typ'=>'JWT']);
    $base64Header = str_replace(['+','/','='],['-','_',''], base64_encode($header));
    $base64Payload = str_replace(['+','/','='],['-','_',''], base64_encode(json_encode($payload)));
    $firma = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $base64Firma = str_replace(['+','/','='],['-','_',''], base64_encode($firma));
    return "$base64Header.$base64Payload.$base64Firma";
}

function validarJWT($jwt, $secret) {
    $partes = explode('.', $jwt);
    if(count($partes)!==3) return false;
    list($h,$p,$f)=$partes;
    $verif = str_replace(['+','/','='],['-','_',''], base64_encode(hash_hmac('sha256',"$h.$p",$secret,true)));
    if($f!==$verif) return false;
    $data=json_decode(base64_decode($p),true);
    if(isset($data['exp']) && $data['exp']<time()) return false;
    return $data;
}
?>
