<?php
$base = __DIR__ . '/storage/app/qz';
if (!is_dir($base)) mkdir($base, 0777, true);

$cnf = $base . '/qz-openssl.cnf';
$cnfContent = <<<CNF
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

file_put_contents($cnf, $cnfContent);

$args = [
  "private_key_bits" => 2048,
  "private_key_type" => OPENSSL_KEYTYPE_RSA,
  "digest_alg" => "sha256",
  "config" => $cnf,
  "x509_extensions" => "v3_req",
  "req_extensions" => "v3_req",
];

$dn = [
  "countryName" => "PE",
  "stateOrProvinceName" => "Lambayeque",
  "localityName" => "Chiclayo",
  "organizationName" => "Mararena",
  "organizationalUnitName" => "Sistemas",
  "commonName" => "xinergia.net",
];

$privkey = openssl_pkey_new($args);
$csr = openssl_csr_new($dn, $privkey, $args);
$x509 = openssl_csr_sign($csr, null, $privkey, 3650, $args);

openssl_x509_export($x509, $certOut);
openssl_pkey_export($privkey, $keyOut);

file_put_contents($base . '/certificate.pem', $certOut);
file_put_contents($base . '/private-key.pem', $keyOut);

echo "OK\n";
