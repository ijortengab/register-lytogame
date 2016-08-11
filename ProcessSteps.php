<?php

namespace IjorTengab;

class ProcessSteps extends AbstractProcessSteps
{
    protected $options = [];
    protected $current_input;
    /**
     *
     */
    public function steps() 
    {
        $steps[] = ['text' => '****************************************************'];
        $steps[] = ['text' => '            CLI untuk registrasi ID Lyto            '];
        $steps[] = ['text' => '****************************************************'];
        $steps[] = ['delay' => 1];
        $steps[] = [
            'text' => 'Masukkan username. Required.',
            'input' => '$this->options["username"]',
            'eval' => '$this->notEmpty($this->options["username"])',
        ];
        $steps['input password'] = [
            'text' => 'Masukkan password. Required.',
            'input' => '$this->options["password"]',
            'eval' => '$this->notEmpty($this->options["password"])',
        ];
        $steps['input gender'] = [
            'text' => 'Pilih Gender. Option: (1/male/pria), (0/female/wanita).',
            'input' => '$this->options["gender"]',
            'eval' => '$this->inArray($this->options["gender"], ["1","male","pria","0","female","wanita"])',
        ];
        $steps[] = [
            'text' => 'Masukkan kode PIN. Optional.',
            'input' => '$this->options["pin"]',
        ];
        $steps[] = [
            'text' => 'Masukkan Email. Optional.',
            'input' => '$this->options["email"]',
        ];
        $steps[] = [
            'text' => 'Masukkan Nama. Optional.',
            'input' => '$this->options["cn"]',
        ];
        $steps[] = [
            'text' => 'Masukkan Tanggal Lahir. Optional.',
            'input' => '$this->options["date"]',
        ];
        $steps[] = [
            'text' => 'Masukkan Bulan Lahir. Optional.',
            'input' => '$this->options["month"]',
        ];
        $steps[] = [
            'text' => 'Masukkan Tahun Lahir. Optional.',
            'input' => '$this->options["year"]',
        ];
        $steps[] = ['text' => '****************************************************'];
        $steps[] = ['text' => '                       PREVIEW                      '];
        $steps[] = ['text' => '****************************************************'];
        $steps[] = ['text' => ''];
        $steps[] = ['text' => 'Value yang kosong akan otomatis di-set oleh sistem.'];
        $steps[] = ['text' => ''];
        $steps[] = ['eval' => '$this->preview()'];
        $steps[] = ['delay' => 1];
        $steps[] = ['text' => ''];
        $steps[] = [
            'text' => 'Apakah anda ingin melanjutkan? (yes/no)',
            'input' => '$this->current_input',
            'eval' => '$this->go()',
        ];
        return $steps;
    }
    /**
     *
     */
    protected function notEmpty($value)
    {
        if (empty($value)) {
            $this->t('Warning: Tidak boleh kosong.');
            array_unshift($this->steps, $this->current_step);
        }
    }
    /**
     *
     */
    protected function inArray($value, $array, $default = null)
    {
        $value = trim($value);
        if (!in_array($value, $array)) {
            $this->t('Warning: Input tidak berada dalam lingkup pilihan.');
            array_unshift($this->steps, $this->current_step);
        }
    }
    /**
     *
     */
    protected function preview()
    {
        foreach ($this->options as $key => $value) {
            $t = ' - ' . $key . ': '. $value;
            $this->t($t);
        }
    }
    /**
     *
     */
    protected function go()
    {
        $value = $this->current_input;
        if (in_array($value, ['yes', 'y'])) {
            $this->t('');
            $this->t('********************');
            $this->t(' Registrasi dimulai ');
            $this->t('********************');
            $this->t('');
            $this->d(1);
            $obj = new RegisterLytoID($this->options);
            $obj->execute();
            $this->d(1);
            $this->t('');
            $this->t('********************');
            $this->t(' Registrasi selesai ');
            $this->t('********************');
            $this->t('');
        }
        elseif (in_array($value, ['no', 'n'])) {
            $this->t('Proses dibatalkan.');
            return;
        }
        else {
            array_unshift($this->steps, $this->current_step);
        }
    }
}
