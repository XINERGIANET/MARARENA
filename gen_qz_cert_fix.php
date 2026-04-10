<?php
$base = __DIR__ . '/storage/app/qz';
if (!is_dir($base)) {
    mkdir($base, 0777, true);
}

$opensslCnf = 'C:/xampp/php/extras/ssl/openssl.cnf';
if (!file_exists($opensslCnf)) {
    fwrite(STDERR, "No se encontró openssl.cnf en $opensslCnf\n");
    exit(1);
}

$localCnf = $base . '/qz-openssl.cnf';
$cnf = <<<CNF
[ req ]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
x509_extensions = v3_req
req_extensions = v3_req

[ dn ]
C = PE
ST = Lambayeque
L = Chiclayo
O = Mararena
OU = Sistemas
CN = xinergia.net

[ v3_req ]
subjectAltName = @alt_names

[ alt_names ]
DNS.1 = xinergia.net
DNS.2 = www.xinergia.net
CNF;
file_put_contents($localCnf, $cnf);

$args = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'digest_alg' => 'sha256',
    'config' => $localCnf,
    'x509_extensions' => 'v3_req',
    'req_extensions' => 'v3_req',
];

$dn = [
    'countryName' => 'PE',
    'stateOrProvinceName' => 'Lambayeque',
    'localityName' => 'Chiclayo',
    'organizationName' => 'Mararena',
    'organizationalUnitName' => 'Sistemas',
    'commonName' => 'xinergia.net',
];

$privkey = openssl_pkey_new($args);
if (!$privkey) {
    while ($e = openssl_error_string()) {
        fwrite(STDERR, $e . "\n");
    }
    exit(1);
}

$csr = openssl_csr_new($dn, $privkey, $args);
if (!$csr) {
    while ($e = openssl_error_string()) {
        fwrite(STDERR, $e . "\n");
    }
    exit(1);
}

$x509 = openssl_csr_sign($csr, null, $privkey, 3650, $args);
if (!$x509) {
    while ($e = openssl_error_string()) {
        fwrite(STDERR, $e . "\n");
    }
    exit(1);
}

$certOut = '';
$keyOut = '';
openssl_x509_export($x509, $certOut);
openssl_pkey_export($privkey, $keyOut, null, ['config' => $localCnf]);

file_put_contents($base . '/certificate.pem', $certOut);
file_put_contents($base . '/private-key.pem', $keyOut);

echo "OK\n";
?>
