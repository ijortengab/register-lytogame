<?php

namespace IjorTengab;

require 'RegisterLytoID.php';

if (PHP_SAPI != 'cli') {
    throw new Exception('Sorry. Run for CLI Only.');
}

// Set Options default.
$default = [
    'username' => '',
    'password' => 'r4h4si4',
    'gender' => 'pria',
    'pin' => '654321', // Kode Pribadi. (6 digit)
    'email' => '',
    'cn' => '', // Common Name.
    'date' => '', // Tanggal Lahir (1~31)
    'month' => '', // Bulan Lahir (1~12)
    'year' => '', // Tahun Lahir (4 digit)
];
// Set Options per account.
$accounts = [
    ['username' => 'aaaa', 'cn' => 'AAAA'],
    ['username' => 'bbbb', 'cn' => 'BBBB'],
    ['username' => 'cccc', 'cn' => 'CCCC'],
    ['username' => 'dddd', 'cn' => 'DDDD'],
];
// Run.
while($account = array_shift($accounts)) {
    $options = array_merge($default, $account);
    $obj = new RegisterLytoID($options);
    $obj->execute();
    if ($obj->is_success_registered) {
        echo 'SUKSES' . PHP_EOL;
        sleep(2);
    }
}
