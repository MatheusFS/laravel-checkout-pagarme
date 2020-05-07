<?php

namespace MatheusFS\LaravelCheckoutPagarMe;

use Illuminate\Support\Facades\Mail;
use MatheusFS\LaravelCheckoutPagarMe\Mail\Postback as MailPostback;

class Postback {

    public function orders() {

    }

    public function transactions() {

        $this->_log();
    }

    protected function _log() {

        $this->_logInEmail();
        $this->_logInFile();
    }

    protected function _logInEmail() {

        Mail::to('matheus@refresher.com.br')->send(new MailPostback());
    }

    protected function _logInFile() {

    }
}