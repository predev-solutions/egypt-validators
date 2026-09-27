<h1 align="center">🇪🇬 egypt-validators</h1>

<p align="center">
  Validate and parse <b>Egyptian national IDs</b>, <b>mobile numbers</b> and <b>governorates</b> in PHP.<br />
  Framework-agnostic, with ready-made <b>Laravel</b> validation rules.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%5E8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11 | 12" />
  <img src="https://img.shields.io/badge/tests-45%20passing-7F75E8?style=for-the-badge" alt="45 tests passing" />
  <img src="https://img.shields.io/badge/license-MIT-0A0A0A?style=for-the-badge" alt="MIT" />
  <br />
  <a href="https://packagist.org/packages/predev-solutions/egypt-validators"><img src="https://img.shields.io/packagist/v/predev-solutions/egypt-validators?style=for-the-badge&label=packagist&color=F28D1A&logo=packagist&logoColor=white" alt="Latest version on Packagist" /></a>
  <a href="https://packagist.org/packages/predev-solutions/egypt-validators/stats"><img src="https://img.shields.io/packagist/dt/predev-solutions/egypt-validators?style=for-the-badge&color=7F75E8&logo=composer&logoColor=white" alt="Total downloads" /></a>
</p>

---

Every Egyptian product ends up writing the same regexes: *is this a real national ID? is this a real mobile number? which governorate is code 21?* This package does it once, properly — including Arabic-Indic digits (`٠١٠...`), every way people type `+20`, and the birth date, governorate and gender encoded in the national ID.

## Install

```bash
composer require predev-solutions/egypt-validators
```

## National ID

A national ID is 14 digits: `C YYMMDD GG SSSS X` — century, date of birth, governorate, serial (odd last digit = male) and a check digit.

```php
use Predev\EgyptValidators\NationalId;

NationalId::isValid('29001010101234');   // true

$info = NationalId::parse('29001010101234');
$info->birthDate->format('Y-m-d');       // "1990-01-01"
$info->age();                            // 36
$info->governorate->nameEn();            // "Cairo"
$info->governorate->nameAr();            // "القاهرة"
$info->gender;                           // Gender::Male

NationalId::parse('29002300101234');     // null — there is no 30 February
```

Rejects impossible dates, leap-year mistakes, unknown governorate codes and birth dates in the future.

## Mobile numbers

```php
use Predev\EgyptValidators\Mobile;

Mobile::isValid('+20 101 234 5678');     // true
Mobile::normalize('(+20) 10-1234-5678'); // "01012345678"
Mobile::normalize('٠١٠١٢٣٤٥٦٧٨');        // "01012345678"
Mobile::toE164('010 1234 5678');         // "+201012345678"
Mobile::carrier('01212345678');          // Carrier::Orange
```

Accepts `010…`, `+2010…`, `002010…`, `2010…` and `10…`, with spaces, dashes, dots and parentheses. Valid prefixes are `010` Vodafone, `011` e& (Etisalat), `012` Orange and `015` WE.

> Numbers can be ported between networks, so treat `carrier()` as a hint, not a guarantee.

## Governorates

```php
use Predev\EgyptValidators\Governorate;

Governorate::from('21')->nameEn();       // "Giza"
Governorate::from('21')->nameAr();       // "الجيزة"
count(Governorate::governorates());      // 27
```

Codes follow the national ID; `88` is used for people born abroad.

## Laravel

```php
use Predev\EgyptValidators\Carrier;
use Predev\EgyptValidators\Gender;
use Predev\EgyptValidators\Laravel\EgyptianMobile;
use Predev\EgyptValidators\Laravel\EgyptianNationalId;

$request->validate([
    'national_id' => ['required', new EgyptianNationalId()],
    'phone'       => ['required', new EgyptianMobile()],

    // optional constraints
    'driver_id'   => ['required', EgyptianNationalId::make()->minAge(21)],
    'nurse_id'    => ['required', EgyptianNationalId::make()->gender(Gender::Female)],
    'wallet'      => ['required', EgyptianMobile::make()->carriers(Carrier::Vodafone)],
]);
```

## Testing

```bash
composer install
composer test
```

## Contributing

Issues and pull requests are welcome — especially new Egyptian formats (landlines, tax registration numbers, commercial registry numbers).

## License

MIT © [predev. Solutions](https://predevsolutions.com)

---

<p align="center">
  Built by <a href="https://github.com/predev-solutions"><b>predev. Solutions</b></a> — a Cairo software house building mobile apps, web platforms and brands for Egypt and the Gulf.
</p>
