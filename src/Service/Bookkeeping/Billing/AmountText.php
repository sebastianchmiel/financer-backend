<?php

namespace App\Service\Bookkeeping\Billing;

class AmountText {

    /**
     * convert amoutn to text
     * 
     * @param float $amount
     * 
     * @return string
     */
    public function convertAmountToText(float $amount) : string {
        $t_a = array('', 'sto', 'dwieście', 'trzysta', 'czterysta', 'pięćset', 'sześćset', 'siedemset', 'osiemset', 'dziewięćset');
        $t_b = array('', 'dziesięć', 'dwadzieścia', 'trzydzieści', 'czterdzieści', 'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 'dziewięćdziesiąt');
        $t_c = array('', 'jeden', 'dwa', 'trzy', 'cztery', 'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć');
        $t_d = array('dziesięć', 'jedenaście', 'dwanaście', 'trzynaście', 'czternaście', 'piętnaście', 'szesnaście', 'siednaście', 'osiemnaście', 'dziewiętnaście');

        $t_kw_15 = array('septyliard', 'septyliardów', 'septyliardy');
        $t_kw_14 = array('septylion', 'septylionów', 'septyliony');
        $t_kw_13 = array('sekstyliard', 'sekstyliardów', 'sekstyliardy');
        $t_kw_12 = array('sekstylion', 'sekstylionów', 'sepstyliony');
        $t_kw_11 = array('kwintyliard', 'kwintyliardów', 'kwintyliardy');
        $t_kw_10 = array('kwintylion', 'kwintylionów', 'kwintyliony');
        $t_kw_9 = array('kwadryliard', 'kwadryliardów', 'kwaryliardy');
        $t_kw_8 = array('kwadrylion', 'kwadrylionów', 'kwadryliony');
        $t_kw_7 = array('tryliard', 'tryliardów', 'tryliardy');
        $t_kw_6 = array('trylion', 'trylionów', 'tryliony');
        $t_kw_5 = array('biliard', 'biliardów', 'biliardy');
        $t_kw_4 = array('bilion', 'bilionów', 'bilony');
        $t_kw_3 = array('miliard', 'miliardów', 'miliardy');
        $t_kw_2 = array('milion', 'milionów', 'miliony');
        $t_kw_1 = array('tysiąc', 'tysięcy', 'tysiące');
        $t_kw_0 = array('złoty', 'złotych', 'złote');

        $amount_text = '';
        
        if ($amount != '') {
            $amount = (substr_count($amount, '.') == 0) ? $amount . '.00' : $amount;
            $tmp = explode(".", $amount);
            $ln = strlen($tmp[0]);
            $tmp_a = ($ln % 3 == 0) ? (floor($ln / 3) * 3) : ((floor($ln / 3) + 1) * 3);
            $l_pad = '';
            for ($i = $ln; $i < $tmp_a; $i++) {
                $l_pad .= '0';
                $amount_w = $l_pad . $tmp[0];
            }
            $amount_w = ($amount_w == '') ? $tmp[0] : $amount_w;
            $paczki = (strlen($amount_w) / 3) - 1;
            $p_tmp = $paczki;
            for ($i = 0; $i <= $paczki; $i++) {
                $t_tmp = 't_kw_' . $p_tmp;
                $p_tmp--;
                $p_kw = substr($amount_w, ($i * 3), 3);
                $amount_w_s = ($p_kw{1} != 1) ? $t_a[$p_kw{0}] . ' ' . $t_b[$p_kw{1}] . ' ' . $t_c[$p_kw{2}] : $t_a[$p_kw{0}] . ' ' . $t_d[$p_kw{2}];
                if (($p_kw{0} == 0) && ($p_kw{2} == 1) && ($p_kw{1} < 1)) {
                    $ka = ${$t_tmp}[0]; //możliwe że $p_kw{1}!=1
                } else if (($p_kw{2} > 1 && $p_kw{2} < 5) && $p_kw{1} != 1) {
                    $ka = ${$t_tmp}[2];
                } else {
                    $ka = ${$t_tmp}[1];
                }
                $amount_text .= $amount_w_s . ' ' . $ka . ' ';
            }
        }
        $text = $amount_text . ' ' . $tmp[1].(strlen($tmp[1]) == 1 ? '0' : '') . ' gr';
        return $text;
    }

}
