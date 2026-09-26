<?php

declare(strict_types=1);

namespace Predev\EgyptValidators;

/**
 * Egyptian governorates, keyed by the two-digit code used in national ID numbers.
 */
enum Governorate: string
{
    case Cairo = '01';
    case Alexandria = '02';
    case PortSaid = '03';
    case Suez = '04';
    case Damietta = '11';
    case Dakahlia = '12';
    case Sharqia = '13';
    case Qalyubia = '14';
    case KafrElSheikh = '15';
    case Gharbia = '16';
    case Monufia = '17';
    case Beheira = '18';
    case Ismailia = '19';
    case Giza = '21';
    case BeniSuef = '22';
    case Fayoum = '23';
    case Minya = '24';
    case Asyut = '25';
    case Sohag = '26';
    case Qena = '27';
    case Aswan = '28';
    case Luxor = '29';
    case RedSea = '31';
    case NewValley = '32';
    case Matrouh = '33';
    case NorthSinai = '34';
    case SouthSinai = '35';
    case BornAbroad = '88';

    public function code(): string
    {
        return $this->value;
    }

    public function nameEn(): string
    {
        return match ($this) {
            self::Cairo => 'Cairo',
            self::Alexandria => 'Alexandria',
            self::PortSaid => 'Port Said',
            self::Suez => 'Suez',
            self::Damietta => 'Damietta',
            self::Dakahlia => 'Dakahlia',
            self::Sharqia => 'Sharqia',
            self::Qalyubia => 'Qalyubia',
            self::KafrElSheikh => 'Kafr El Sheikh',
            self::Gharbia => 'Gharbia',
            self::Monufia => 'Monufia',
            self::Beheira => 'Beheira',
            self::Ismailia => 'Ismailia',
            self::Giza => 'Giza',
            self::BeniSuef => 'Beni Suef',
            self::Fayoum => 'Fayoum',
            self::Minya => 'Minya',
            self::Asyut => 'Asyut',
            self::Sohag => 'Sohag',
            self::Qena => 'Qena',
            self::Aswan => 'Aswan',
            self::Luxor => 'Luxor',
            self::RedSea => 'Red Sea',
            self::NewValley => 'New Valley',
            self::Matrouh => 'Matrouh',
            self::NorthSinai => 'North Sinai',
            self::SouthSinai => 'South Sinai',
            self::BornAbroad => 'Born abroad',
        };
    }

    public function nameAr(): string
    {
        return match ($this) {
            self::Cairo => 'القاهرة',
            self::Alexandria => 'الإسكندرية',
            self::PortSaid => 'بورسعيد',
            self::Suez => 'السويس',
            self::Damietta => 'دمياط',
            self::Dakahlia => 'الدقهلية',
            self::Sharqia => 'الشرقية',
            self::Qalyubia => 'القليوبية',
            self::KafrElSheikh => 'كفر الشيخ',
            self::Gharbia => 'الغربية',
            self::Monufia => 'المنوفية',
            self::Beheira => 'البحيرة',
            self::Ismailia => 'الإسماعيلية',
            self::Giza => 'الجيزة',
            self::BeniSuef => 'بني سويف',
            self::Fayoum => 'الفيوم',
            self::Minya => 'المنيا',
            self::Asyut => 'أسيوط',
            self::Sohag => 'سوهاج',
            self::Qena => 'قنا',
            self::Aswan => 'أسوان',
            self::Luxor => 'الأقصر',
            self::RedSea => 'البحر الأحمر',
            self::NewValley => 'الوادي الجديد',
            self::Matrouh => 'مطروح',
            self::NorthSinai => 'شمال سيناء',
            self::SouthSinai => 'جنوب سيناء',
            self::BornAbroad => 'مواليد الخارج',
        };
    }

    /** @return list<self> the 27 governorates, without the born-abroad code */
    public static function governorates(): array
    {
        return array_values(array_filter(self::cases(), fn (self $g) => $g !== self::BornAbroad));
    }
}
