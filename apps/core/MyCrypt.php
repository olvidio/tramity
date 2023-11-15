<?php

namespace core;
class MyCrypt
{
    /*
     * Función para encriptar password
     *
     */
    function encode($clear, $hashed = NULL)
    {
        $salt_len = 100;
        if (empty($hashed)) {
            for ($salt = '', $x = 0; $x++ < $salt_len; $salt .= bin2hex(chr(random_int(0, 255)))) {
                // nada, ya se ejecuta en la tercera expresión
            }
        } else {
            $salt = substr($hashed, 0, $salt_len * 2);  //  extract existing salt
        }
        return $salt . hash('whirlpool', $salt . $clear);
    }

    public function is_valid_password($user, $password)
    {

        $cmd_cracklib_check = shell_exec(sprintf("command -v cracklib-check"));
        $cmd_pwscore = shell_exec(sprintf("command -v pwscore"));

        if (!empty($cmd_cracklib_check) && !empty($cmd_pwscore)) {
            return $this->is_valid_password_2($user, $password, 55);
        } else {
            return $this->is_valid_password_1($user, $password);
        }
    }

    private function is_valid_password_1($user, $password)
    {
        /* Del Windows:
         * Enabling this policy setting requires passwords to meet the following requirements:
         1.-Passwords may not contain the user's samAccountName (Account Name) value or entire displayName (Full Name value). Both checks are not case sensitive.
         The samAccountName is checked in its entirety only to determine whether it is part of the password. If the samAccountName is less than three characters long, this check is skipped.
         The displayName is parsed for delimiters: commas, periods, dashes or hyphens, underscores, spaces, pound signs, and tabs. If any of these delimiters are found, the displayName is split and all parsed sections (tokens) are confirmed to not be included in the password. Tokens that are less than three characters are ignored, and substrings of the tokens are not checked. For example, the name "Erin M. Hagens" is split into three tokens: "Erin", "M", and "Hagens". Because the second token is only one character long, it is ignored. Therefore, this user could not have a password that included either "erin" or "hagens" as a substring anywhere in the password.

         2.- The password contains characters from three of the following categories:
         *Uppercase letters of European languages (A through Z, with diacritic marks, Greek and Cyrillic characters)
         *Lowercase letters of European languages (a through z, sharp-s, with diacritic marks, Greek and Cyrillic characters)
         *Base 10 digits (0 through 9)
         *Nonalphanumeric characters: ~!@#$%^&*_-+=`|(){}[]:;"'<>,.?/
         *Any Unicode character that is categorized as an alphabetic character but is not uppercase or lowercase. This includes Unicode characters from Asian languages.
         */

        $lower_user = strtolower($user);
        $lower_pwd = strtolower($password);
        $txt_err = '';

        if (strpos($lower_pwd, '"') !== false) {
            $txt_err .= empty($txt_err) ? '' : "\n";
            $txt_err .= "$user: password($password) ";
            $txt_err .= _("No se pueden usar las comillas en el password");
        }

        if (strpos($lower_pwd, $lower_user) !== false) {
            $txt_err .= empty($txt_err) ? '' : "\n";
            $txt_err .= "$user: password($password) ";
            $txt_err .= _("El nombre de usuario No puede estar en el password");
        }

        if (strlen($password) < 8) {
            $txt_err .= empty($txt_err) ? '' : "\n";
            $txt_err .= "$user: password($password) ";
            $txt_err .= _("Debe tener más de 8 caracteres");
        }

        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        $numCriteria = 0;
        if ($uppercase) {
            $numCriteria++;
        }
        if ($lowercase) {
            $numCriteria++;
        }
        if ($number) {
            $numCriteria++;
        }
        if ($specialChars) {
            $numCriteria++;
        }

        if ($numCriteria < 3) {
            $txt_err .= empty($txt_err) ? '' : "\n";
            $txt_err .= "$user: password($password) ";
            $txt_err .= _("Debe incluir como mínimo una letra mayúscula, un número y un caracter especial");
        }
        if (empty($txt_err)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $txt_err;
        }
        return $jsondata;
    }

    private function is_valid_password_2($user, $pw, $score_ref = 55)
    {
        //$CRACKLIB = "/usr/sbin/cracklib-check";
        //$PWSCORE = "/usr/bin/pwscore";

        $CRACKLIB = shell_exec(sprintf("command -v cracklib-check"));
        $PWSCORE = shell_exec(sprintf("command -v pwscore")) . ' ' . $user;

        // prevent UTF-8 characters being stripped by escapeshellarg
        setlocale(LC_ALL, 'en_US.utf-8');

        $out = [];
        $ret = NULL;
        $command = "echo " . escapeshellarg($pw) . " | {$CRACKLIB}";
        exec($command, $out, $ret);
        $regs = [];
        $txt_err = '';
        if (($ret == 0) && preg_match("/: ([^:]+)$/", $out[0], $regs)) {
            list(, $msg) = $regs;
            switch ($msg) {
                case "OK":
                    $command = "echo " . escapeshellarg($pw) . " | {$PWSCORE}";
                    exec($command, $out, $ret);
                    if (!empty($out[1]) && is_numeric($out[1]) && ($out[1] > $score_ref)) {
                        // OK
                    } else {
                        //$txt_err = 'probably OK, but may be too short, or a palindrome';
                        $txt_err = _("Bien, pero poco fuerte: quizá demasiado corto o un palíndromo");
                    }
                    break;
                default:
                    //$txt_err = str_replace("dictionary word", "common word, name or pattern", $msg);
                    $txt_err = $msg;
            }
        }
        // possibly OK

        if (empty($txt_err)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $txt_err;
        }
        return $jsondata;
    }
}
