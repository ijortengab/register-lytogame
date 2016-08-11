<?php
/**
 * @author
 *   IjorTengab
 * @file
 *   RegisterLytoID.php
 * @last_modified
 *   2016 08 11
 *
 * File untuk mendaftar account di lytogame
 * tanpa browser via CLI.
 *
 * Cara menggunakan:
 * ```
 * $obj = new Register('ID LYTO HERE');
 * $obj->setOptions('ARRAY OPTIONS HERE');
 * $obj->execute();
 * ```
 *
 *
 */

namespace IjorTengab;

class RegisterLytoID {
    public $verbose = true;
    public $log = true;
    public $is_success_registered = false;
    protected $log_string = [];
    protected $username_raw;
    protected $username_clean;
    protected $username_registered;
    protected $password_raw;
    protected $password_clean;
    protected $pin_raw;
    protected $pin_clean;
    protected $email_raw;
    protected $email_clean;
    protected $cn_raw;
    protected $cn_clean;
    protected $gender_raw;
    protected $gender_clean;
    protected $date_raw;
    protected $date_clean;
    protected $month_raw;
    protected $month_clean;
    protected $year_raw;
    protected $year_clean;
    protected $max_failed_captcha = 3;
    protected $current_failed_captcha = 0;
    private $cookie_name;
    private $cookie_value;
    private $captcha;
    /**
     *
     */
    public function __construct($mixed = null, $options = [])
    {
        if (PHP_SAPI != 'cli') {
            throw new Exception('Sorry. Run for CLI Only.');
        }
        // $mixed.
        if (is_string($mixed)) {
            $this->setUsername($mixed);
        }
        elseif (is_array($mixed)) {
            $this->setOptions($mixed);
        }
        // If $mixed is string.
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }
    /**
     *
     */
    public function __destruct()
    {
        if ($this->log) {
            $dir = __DIR__ . DIRECTORY_SEPARATOR . 'log';
            if (!is_dir($dir)) {
                mkdir($dir, true, 0775);
            }
            $file = $dir . DIRECTORY_SEPARATOR . $this->username_registered . '.log';
            file_put_contents($file, implode("\r\n", $this->log_string) . "\r\n", FILE_APPEND);
        }
    }
    /**
     *
     */
    protected function setOption($key, $value)
    {
        return $this->setOptions([$key => $value]);
    }
    /**
     *
     */
    protected function setOptions($options=[])
    {
        if (isset($options['username'])) {
            $this->setUsername($options['username']);
        }
        if (isset($options['password'])) {
            $this->setPassword($options['password']);
        }
        if (isset($options['pin'])) {
            $this->setPIN($options['pin']);
        }
        if (isset($options['email'])) {
            $this->setEmail($options['email']);
        }
        if (isset($options['cn'])) {
            $this->setCN($options['cn']);
        }
        if (isset($options['gender'])) {
            $this->setGender($options['gender']);
        }
        if (isset($options['date'])) {
            $this->setDate($options['date']);
        }
        if (isset($options['month'])) {
            $this->setMonth($options['month']);
        }
        if (isset($options['year'])) {
            $this->setYear($options['year']);
        }
        return $this;
    }
    /**
     *
     */
    protected function setUsername($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomUsername();
        }
        $this->username_raw = $string;
        // Aturan pertama karakter hanya boleh huruf lowercase dan angka.
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]/', '', $string);
        // Aturan kedua tidak boleh diawali dengan angka.
        while(preg_match('/^\d/', $string)){
            // Hapus angka tersebut.
            $string = substr($string, 1);
        }
        if (false === $string) {
            return $this->setRandomUsername();
        }
        // Aturan ketiga tidak boleh kurang dari 4 karakter.
        while (strlen($string) < 4 ) {
            $string = $string . '0';
        }
        // Aturan keempat tidak boleh lebih dari 12 karakter.
        if (strlen($string) > 12 ) {
            $string = substr($string, 0, 12);
        }
        $this->username_clean = $string;
    }
    /**
     *
     */
    protected function setRandomUsername()
    {
        $strlen = rand(4,12);
        $letter_uppercase = '';
        for ($x = 0; $x < $strlen ; $x++) {
            $letter_uppercase .= chr(64+rand(1,26));
        }
        $this->username_clean = strtolower($letter_uppercase);
    }
    /**
     *
     */
    public function getUsername()
    {
        if (null === $this->username_clean) {
            $this->setRandomUsername();
        }
        return $this->username_clean;
    }
    /**
     *
     */
    protected function setPassword($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomPassword();
        }
        $this->password_raw = $string;
        // Aturan pertama karakter hanya boleh huruf dan angka.
        $string = preg_replace('/[^a-zA-Z0-9]/', '', $string);
        // Aturan kedua tidak boleh kurang dari 6 karakter.
        while (strlen($string) < 6 ) {
            $string = $string . '0';
        }
        // Aturan ketiga tidak boleh lebih dari 12 karakter.
        if (strlen($string) > 12 ) {
            $string = substr($string, 0, 12);
        }
        $this->password_clean = $string;
    }
    /**
     *
     */
    protected function setRandomPassword()
    {
        // Password - Harus terdiri dari 6-12 karakter.
        $strlen = rand(6,12);
        $letter_uppercase = '';
        for ($x = 0; $x < $strlen ; $x++) {
            $letter_uppercase .= chr(64+rand(1,26));
        }
        $this->password_clean = strtolower($letter_uppercase);
        // return $this;
    }
    /**
     *
     */
    public function getPassword()
    {
        if (null === $this->password_clean) {
            $this->setRandomPassword();
        }
        return $this->password_clean;
    }
    /**
     *
     */
    protected function setPIN($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomPIN();
        }
        $this->pin_raw = $string;
        // Aturan pertama karakter hanya boleh angka.
        $string = preg_replace('/[^0-9]/', '', $string);
        if ($string == '') {
            return $this->setRandomPIN();
        }
        // Aturan kedua panjang karakter harus 6.
        while (strlen($string) < 6 ) {
            $string = $string . '0';
        }
        if (strlen($string) > 6 ) {
            $string = substr($string, 0, 6);
        }
        $this->pin_clean = $string;
    }
    /**
     *
     */
    protected function setRandomPIN()
    {
        $string = '';
        for ($x = 0; $x < 6 ; $x++) {
            $string .= rand(0,9);
        }
        $this->pin_clean = $string;
    }
    /**
     *
     */
    public function getPIN()
    {
        if (null === $this->pin_clean) {
            $this->setRandomPIN();
        }
        return $this->pin_clean;
    }
    /**
     *
     */
    protected function setEmail($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomEmail();
        }
        $this->email_raw = $string;
        //
        $test = (bool) filter_var($string, FILTER_VALIDATE_EMAIL);
        if (false === $test) {
            return $this->setRandomEmail();
        }
        $this->email_clean = $string;
    }
    /**
     *
     */
    protected function setRandomEmail()
    {
        $string = $this->getUsername();
        $this->email_clean = $string . '@gmail.com';
    }
    /**
     *
     */
    public function getEmail()
    {
        if (null === $this->email_clean) {
            $this->setRandomEmail();
        }
        return $this->email_clean;
    }
    /**
     *
     */
    protected function setCN($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomCN();
        }
        $this->cn_raw = $string;
        // Aturan pertama tidak boleh kurang dari 4 karakter.
        while (strlen($string) < 4 ) {
            $string = $string . '0';
        }
        // Aturan kedua tidak boleh lebih dari 30 karakter.
        if (strlen($string) > 30 ) {
            $string = substr($string, 0, 30);
        }
        $this->cn_clean = $string;
    }
    /**
     *
     */
    protected function setRandomCN()
    {
        $string = $this->getUsername();
        $this->cn_clean = $string;
    }
    /**
     *
     */
    public function getCN()
    {
        if (null === $this->cn_clean) {
            $this->setRandomCN();
        }
        return $this->cn_clean;
    }
    /**
     *
     */
    protected function setGender($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomGender();
        }
        $this->gender_raw = $string;
        $string = (string) $string;
        $string = strtolower($string);
        if (!in_array($string, ['1', '0', 'male', 'female','pria', 'wanita'])) {
            return $this->setRandomGender();
        }
        // Perbaiki.
        if (in_array($string, ['male', 'pria'])) {
            $string = '1';
        }
        if (in_array($string, ['female', 'wanita'])) {
            $string = '0';
        }
        $this->gender_clean = $string;
    }
    /**
     *
     */
    protected function setRandomGender()
    {
        $string = (string) rand(0,1);
        $this->gender_clean = $string;
    }
    /**
     *
     */
    public function getGender()
    {
        if (null === $this->gender_clean) {
            $this->setRandomGender();
        }
        return $this->gender_clean;
    }
    /**
     *
     */
    protected function setDate($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomDate();
        }
        $this->date_raw = $string;
        // Aturan pertama karakter hanya boleh angka.
        $string = preg_replace('/[^0-9]/', '', $string);
        if ($string == '') {
            return $this->setRandomDate();
        }
        // Kurang dari 1 gak mungkin.
        if ($string < 1) {
            return $this->setRandomDate();
        }
        // Lebih dari 31 gak mungkin.
        if ($string > 31) {
            return $this->setRandomDate();
        }
        $this->date_clean = $string;
        $this->validateDateMonth();
    }
    /**
     *
     */
    protected function setRandomDate()
    {
        $string = rand(1,31);
        $this->date_clean = $string;
        $this->validateDateMonth();
    }
    /**
     *
     */
    public function getDate()
    {
        if (null === $this->date_clean) {
            $this->setRandomDate();
        }
        return $this->date_clean;
    }
    /**
     *
     */
    protected function setMonth($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomMonth();
        }
        $this->month_raw = $string;
        // Aturan pertama karakter hanya boleh angka.
        $string = preg_replace('/[^0-9]/', '', $string);
        if ($string == '') {
            return $this->setRandomMonth();
        }
        // Kurang dari 1 gak mungkin.
        if ($string < 1) {
            return $this->setRandomMonth();
        }
        // Lebih dari 12 gak mungkin.
        if ($string > 12) {
            return $this->setRandomMonth();
        }
        $this->month_clean = $string;
        $this->validateDateMonth();
    }
    /**
     *
     */
    protected function setRandomMonth()
    {
        $string = rand(1,12);
        $this->month_clean = $string;
        $this->validateDateMonth();
    }
    /**
     *
     */
    public function getMonth()
    {
        if (null === $this->month_clean) {
            $this->setRandomMonth();
        }
        return $this->month_clean;
    }
    /**
     *
     */
    protected function validateDateMonth()
    {
        if (null !== $this->month_clean && null !== $this->date_clean) {
            if ($this->month_clean == 2 && $this->date_clean > 28) {
                $this->date_clean = 28;
            }
            elseif (in_array($this->month_clean, [2,4,6,9,11])  && $this->date_clean > 30) {
                $this->date_clean = 30;
            }
        }
    }
    /**
     *
     */
    protected function setYear($string)
    {
        if (!is_string($string) || empty($string)) {
            return $this->setRandomYear();
        }
        $this->year_raw = $string;
        // Aturan pertama karakter hanya boleh angka.
        $string = preg_replace('/[^0-9]/', '', $string);
        if ($string == '') {
            return $this->setRandomYear();
        }
        // Usia pemain kita anggap maksimal 50.
        // minimal 12.
        $current_year = date('Y');
        $max_year = $current_year - 12;
        $min_year = $current_year - 50;
        if ($string < $min_year) {
            $string = $min_year;
        }
        if ($string > $max_year) {
            $string = $max_year;
        }
        $this->year_clean = $string;
    }
    /**
     *
     */
    protected function setRandomYear()
    {
        $current_year = date('Y');
        $max_year = $current_year - 12;
        $min_year = $current_year - 50;
        $string = rand($min_year, $max_year);
        $this->year_clean = $string;
    }
    /**
     *
     */
    public function getYear()
    {
        if (null === $this->year_clean) {
            $this->setRandomYear();
        }
        return $this->year_clean;
    }
    /**
     *
     */
    public function execute()
    {
        $this->beforeExecute();
        $this->runRequest();
        $this->afterExecute();
    }
    /**
     *
     */
    protected function beforeExecute()
    {
        // Beritahu kepada user kalo ada perubahan.
        if (null !== $this->username_raw && $this->username_raw != $this->getUsername() ) {
            $this->log('Username dikoreksi dari "' . $this->username_raw . '" menjadi "' . $this->getUsername() . '".'  );
        }
        if (null !== $this->password_raw && $this->password_raw != $this->getPassword() ) {
            $this->log('Password dikoreksi dari "' . $this->password_raw . '" menjadi "' . $this->getPassword() . '".'  );
        }
        if (null !== $this->pin_raw && $this->pin_raw != $this->getPIN() ) {
            $this->log('PIN dikoreksi dari "' . $this->pin_raw . '" menjadi "' . $this->getPIN() . '".'  );
        }
        if (null !== $this->email_raw && $this->email_raw != $this->getEmail() ) {
            $this->log('Email dikoreksi dari "' . $this->email_raw . '" menjadi "' . $this->getEmail() . '".'  );
        }
        if (null !== $this->cn_raw && $this->cn_raw != $this->getCN() ) {
            $this->log('Common Name dikoreksi dari "' . $this->cn_raw . '" menjadi "' . $this->getCN() . '".'  );
        }
        if (null !== $this->gender_raw && $this->gender_raw != $this->getGender() ) {
            $this->log('Gender dikoreksi dari "' . $this->gender_raw . '" menjadi "' . $this->getGender() . '".'  );
        }
        if (null !== $this->date_raw && $this->date_raw != $this->getDate() ) {
            $this->log('Date dikoreksi dari "' . $this->date_raw . '" menjadi "' . $this->getDate() . '".'  );
        }
        if (null !== $this->month_raw && $this->month_raw != $this->getMonth() ) {
            $this->log('Month dikoreksi dari "' . $this->month_raw . '" menjadi "' . $this->getMonth() . '".'  );
        }
        if (null !== $this->year_raw && $this->year_raw != $this->getYear() ) {
            $this->log('Year dikoreksi dari "' . $this->year_raw . '" menjadi "' . $this->getYear() . '".'  );
        }
    }
    /**
     *
     */
    protected function runRequest()
    {
        $this->username_registered = $this->username_clean;
        $response = $this->requestHTTPInit(true);
        if (false === $response) {
            return false;
        }
        preg_match('/Set-Cookie: ([^=]*)=([^;]*);/', $response, $m);
        $this->cookie_name = $m[1];
        $this->cookie_value = $m[2];
        sleep(0);
        $response = $this->requestHTTPCaptcha(true);
        if (false === $response) {
            return false;
        }
        $binaryCaptcha = $response;
        sleep(2);
        $response = $this->requestHTTPCheckUsername(true);
        if (false === $response) {
            return false;
        }
        if ($response == '0') {
            $this->log('Username "' . $this->username_clean . '" has exists.');
            return false;
        }
        elseif ($response != '1') {
            $this->log('Result not expected. [1]');
            return false;
        }
        file_put_contents('captcha.bmp', $binaryCaptcha);
        $this->inputCaptchaManual();
        $response = $this->requestHTTPSubmitForm(true);
        if (false === $response) {
            return false;
        }
        if (preg_match('/Location: daftar\.asp/', $response)) {
            // Captcha Gagal. :-(
            return $this->runRequestAgain();
        }
        elseif (preg_match('/Location: thankyou\.asp/', $response)) {
            $this->is_success_registered = true;
            $this->requestHTTPThankYou();
            $this->logSuccess();
            return true;
        }
        else {
            $this->log('Result not expected. [2]');
            return false;
        }
    }
    /**
     *
     */
    protected function runRequestAgain()
    {
        $response = $this->requestHTTPInitAgain(true);
        if (false === $response) {
            return false;
        }
        $check = preg_quote('Verifikasi Kamu tidak sama dengan gambar');
        if (!preg_match('/'.$check.'/', $response)) {
            $test = preg_match('/<ul class="peringatan">(.*)<\/div>/', $response, $m);
            if (isset($m[1])) {
                $alert = strip_tags($m[1]);
                $this->log($alert);
            }
            else {
                $this->log('Result not expected. [3]');
            }
            return false;
        }
        $this->log('Input Captcha wrong.');
        $this->current_failed_captcha++;
        $this->log($this->current_failed_captcha . '/'. $this->max_failed_captcha .' failed. ');
        if ($this->current_failed_captcha / $this->max_failed_captcha >= 1) {
            $this->log('Max failed has been reached.');
            return false;
        }
        $response = $this->requestHTTPCaptcha(true);
        if (false === $response) {
            return false;
        }
        file_put_contents('captcha.bmp', $response);
        sleep(2);
        $this->inputCaptchaManual();
        $response = $this->requestHTTPSubmitForm(true);
        if (false === $response) {
            return false;
        }
        if (preg_match('/Location: daftar\.asp/', $response)) {
            // Captcha Gagal. :-(
            return $this->runRequestAgain();
        }
        elseif (preg_match('/Location: thankyou\.asp/', $response)) {
            $this->is_success_registered = true;
            $this->requestHTTPThankYou();
            $this->logSuccess();
            return true;
        }
        else {
            $this->log('Result not expected. [4]');
            return false;
        }
    }
    /**
     *
     */
    public function inputCaptchaManual()
    {
        if ($this->verbose) {
            echo "Masukkan Nilai Captcha yang tertera pada file captcha.bmp." . PHP_EOL;
            echo "(Input value otomatis dijadikan UPPERCASE)" . PHP_EOL;
        }
        $line = fgets(STDIN);
        $line = rtrim($line);
        $line = strtoupper($line);
        $this->captcha = $line;
        unlink('captcha.bmp');
    }
    /**
     *
     */
    protected function afterExecute()
    {
        // return $this;
    }
    /**
     *
     */
    protected function log($string)
    {
        if ($this->verbose) {
            echo $string . PHP_EOL;
        }
        $this->log_string[] = $string;
    }
    /**
     *
     */
    protected function flatPostFields($array)
    {
        if (!is_array($array)) {
            return;
        }
        $_array = [];
        foreach ($array as $key => $value) {
            $_array[] = urlencode($key) . '=' . urlencode($value);
        }
        return implode('&', $_array);
    }
    /**
     *
     */
    protected function requestHTTPInit($return_response = false)
    {
        $url = 'https://member.lytogame.com/member/daftar.asp';
        $header = [
            'Host: member.lytogame.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            $this->log('Request INIT Failed.');
            return false;
        }
        return $return_response ? $result : true;
    }
    /**
     *
     */
    protected function requestHTTPCaptcha($return_response = false)
    {
        $url = 'https://member.lytogame.com/member/captcha.asp';
        $header = [
            'Host: member.lytogame.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Referer: https://member.lytogame.com/member/daftar.asp',
            'Cookie: ' . $this->cookie_name . '=' . $this->cookie_value . ';',
            'Connection: keep-alive',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            $this->log('Request Captcha Failed.');
            return false;
        }
        return $return_response ? $result : true;
    }
    /**
     *
     */
    protected function requestHTTPCheckUsername($return_response = false)
    {
        $url = 'https://member.lytogame.com/member/checkusnvalid.asp';
        $fields = ['username' => $this->username_clean];
        $postfields = $this->flatPostFields($fields);
        $header = [
            'Host: member.lytogame.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
            'Referer: https://member.lytogame.com/member/daftar.asp',
            'Content-Length: ' . strlen($postfields),
            'Cookie: ' . $this->cookie_name . '=' . $this->cookie_value . ';',
            'Connection: keep-alive',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            $this->log('Request Check Username Failed.');
            return false;
        }
        return $return_response ? $result : true;
    }
    /**
     *
     */
    protected function requestHTTPSubmitForm($return_response = false)
    {
        $url = 'https://member.lytogame.com/member/daftar_.asp';
        $fields = [
            'alias' => $this->username_clean,
            'sandi' => $this->getPassword(),
            'cekSandi' => $this->getPassword(),
            'kodep' => $this->getPIN(),
            'kodep2' => $this->getPIN(),
            'email' => $this->getEmail(),
            'nama' => $this->getCN(),
            'kelamin' => $this->getGender(),
            'tanggal' => $this->getDate(),
            'bulan' => $this->getMonth(),
            'tahun' => $this->getYear(),
            'ceksetuju3' => 'setuju',
            'recaptcha_response_field' => $this->captcha,
        ];
        $postfields = $this->flatPostFields($fields);
        $header = [
            'Host: member.lytogame.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Referer: https://member.lytogame.com/member/daftar.asp',
            'Cookie: ' . $this->cookie_name . '=' . $this->cookie_value . ';',
            'Connection: keep-alive',
            'Content-Length: ' . strlen($postfields),
            'Content-Type: application/x-www-form-urlencoded',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 302) {
            $this->log('Request Submit Form Failed.');
            return false;
        }
        return $return_response ? $result : true;
    }
    /**
     *
     */
    protected function requestHTTPThankYou($return_response = false)
    {
        $url = 'https://member.lytogame.com/member/thankyou.asp';
        $header = [
            'Host: member.lytogame.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Referer: https://member.lytogame.com/member/daftar.asp',
            'Cookie: ' . $this->cookie_name . '=' . $this->cookie_value . ';',
            'Connection: keep-alive',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            $this->log('Request ThankYou Failed.');
            return false;
        }
        return $return_response ? $result : true;
    }
    /**
     *
     */
    protected function requestHTTPInitAgain($return_response = false)
    {
        $url = 'https://member.lytogame.com/member/daftar.asp';
        $header = [
            'Host: member.lytogame.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Referer: https://member.lytogame.com/member/daftar.asp',
            'Cookie: ' . $this->cookie_name . '=' . $this->cookie_value . ';',
            'Connection: keep-alive',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            $this->log('Request INIT Again Failed.');
            return false;
        }
        return $return_response ? $result : true;
    }
    /**
     *
     */
    protected function logSuccess()
    {
        $this->log('Sukes mendaftar dengan rincian sbb:');
        $this->log('Username: ' . $this->username_registered);
        $this->log('Password: ' . $this->password_clean);
        $this->log('Kode PIN: ' . $this->pin_clean);
        $this->log('Email: ' . $this->email_clean);
        $this->log('Nama: ' . $this->cn_clean);
        $this->log('Gender: ' . $this->gender_clean . ' (0=female;1=male)');
        $this->log('Tanggal Lahir: ' . $this->date_clean);
        $this->log('Bulan Lahir: ' . $this->month_clean);
        $this->log('Tahun Lahir: ' . $this->year_clean);
        $this->log('Captcha: ' . $this->captcha);
    }
}