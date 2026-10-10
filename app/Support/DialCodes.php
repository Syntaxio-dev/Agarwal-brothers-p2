<?php

namespace App\Support;

/** Country dialling codes for the phone field. The visitor picks a country; the server looks the code up. */
class DialCodes
{
    public const DEFAULT = 'IN';

    /** @return array<string, array{0: string, 1: string}> ISO code => [country name, dialling code] */
    public static function all(): array
    {
        return [
            'IN' => ['India', '91'],
            'AE' => ['United Arab Emirates', '971'],
            'US' => ['United States', '1'],
            'GB' => ['United Kingdom', '44'],
            'CA' => ['Canada', '1'],
            'AU' => ['Australia', '61'],
            'SG' => ['Singapore', '65'],
            'SA' => ['Saudi Arabia', '966'],
            'QA' => ['Qatar', '974'],
            'KW' => ['Kuwait', '965'],
            'OM' => ['Oman', '968'],
            'BH' => ['Bahrain', '973'],
            'NP' => ['Nepal', '977'],
            'BD' => ['Bangladesh', '880'],
            'LK' => ['Sri Lanka', '94'],
            'BT' => ['Bhutan', '975'],
            'PK' => ['Pakistan', '92'],
            'AF' => ['Afghanistan', '93'],
            'MV' => ['Maldives', '960'],
            'MM' => ['Myanmar', '95'],
            'TH' => ['Thailand', '66'],
            'MY' => ['Malaysia', '60'],
            'ID' => ['Indonesia', '62'],
            'PH' => ['Philippines', '63'],
            'VN' => ['Vietnam', '84'],
            'KH' => ['Cambodia', '855'],
            'CN' => ['China', '86'],
            'HK' => ['Hong Kong', '852'],
            'TW' => ['Taiwan', '886'],
            'JP' => ['Japan', '81'],
            'KR' => ['South Korea', '82'],
            'IR' => ['Iran', '98'],
            'IQ' => ['Iraq', '964'],
            'IL' => ['Israel', '972'],
            'JO' => ['Jordan', '962'],
            'LB' => ['Lebanon', '961'],
            'TR' => ['Turkey', '90'],
            'EG' => ['Egypt', '20'],
            'ZA' => ['South Africa', '27'],
            'NG' => ['Nigeria', '234'],
            'KE' => ['Kenya', '254'],
            'ET' => ['Ethiopia', '251'],
            'GH' => ['Ghana', '233'],
            'TZ' => ['Tanzania', '255'],
            'UG' => ['Uganda', '256'],
            'MU' => ['Mauritius', '230'],
            'MA' => ['Morocco', '212'],
            'DZ' => ['Algeria', '213'],
            'TN' => ['Tunisia', '216'],
            'DE' => ['Germany', '49'],
            'FR' => ['France', '33'],
            'IT' => ['Italy', '39'],
            'ES' => ['Spain', '34'],
            'PT' => ['Portugal', '351'],
            'NL' => ['Netherlands', '31'],
            'BE' => ['Belgium', '32'],
            'CH' => ['Switzerland', '41'],
            'AT' => ['Austria', '43'],
            'SE' => ['Sweden', '46'],
            'NO' => ['Norway', '47'],
            'DK' => ['Denmark', '45'],
            'FI' => ['Finland', '358'],
            'IE' => ['Ireland', '353'],
            'PL' => ['Poland', '48'],
            'CZ' => ['Czech Republic', '420'],
            'HU' => ['Hungary', '36'],
            'RO' => ['Romania', '40'],
            'GR' => ['Greece', '30'],
            'RU' => ['Russia', '7'],
            'UA' => ['Ukraine', '380'],
            'KZ' => ['Kazakhstan', '7'],
            'UZ' => ['Uzbekistan', '998'],
            'NZ' => ['New Zealand', '64'],
            'MX' => ['Mexico', '52'],
            'BR' => ['Brazil', '55'],
            'AR' => ['Argentina', '54'],
            'CL' => ['Chile', '56'],
            'CO' => ['Colombia', '57'],
            'PE' => ['Peru', '51'],
        ];
    }

    /** @return list<string> */
    public static function isos(): array
    {
        return array_keys(static::all());
    }

    /** Dialling code (digits only) for a country, falling back to the default country. */
    public static function code(?string $iso): string
    {
        $all = static::all();

        return $all[strtoupper((string) $iso)][1] ?? $all[self::DEFAULT][1];
    }

    /** For the dropdown: [['iso' => 'IN', 'name' => 'India', 'code' => '91'], ...] */
    public static function options(): array
    {
        $out = [];
        foreach (static::all() as $iso => [$name, $code]) {
            $out[] = ['iso' => $iso, 'name' => $name, 'code' => $code];
        }

        return $out;
    }
}
