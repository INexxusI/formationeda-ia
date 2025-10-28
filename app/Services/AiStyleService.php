<?php
namespace App\Services;

class AiStyleService {
    public static function profiles(): array {
        return [
            'arielle' => ['name' => 'Arielle la Sage', 'hint' => 'Indice : soustrais 7 des deux côtés.', 'short' => 'Soustrais 7 : x = 3.', 'deep' => 'x + 7 = 10 → soustrais 7 aux deux membres : x = 3.'],
            'max'     => ['name' => 'Max le Motivé', 'hint' => 'Indice : enlève le 7.', 'short' => 'x = 3. Next!', 'deep' => 'Va direct : 10 − 7 = 3.'],
            'noa'     => ['name' => 'Dr. Noa', 'hint' => 'Indice : retire 7 comme une tomate de trop.', 'short' => 'x = 3 (10 − 7 = 3).', 'deep' => 'Imagine 10 bonbons, tu enlèves 7 → 3.'],
            'sora'    => ['name' => 'Sora l’Exigeant', 'hint' => 'Indice : opération inverse.', 'short' => 'Opération inverse : x = 3.', 'deep' => 'Inverse de +7 → −7 des deux côtés. Vérifie : 3+7=10.'],
        ];
    }
}
