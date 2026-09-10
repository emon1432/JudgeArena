<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('countries')->delete();
        
        \DB::table('countries')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Afghanistan',
                'code' => 'AF',
                'flag' => '🇦🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Aland Islands',
                'code' => 'AX',
                'flag' => '🇦🇽',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Albania',
                'code' => 'AL',
                'flag' => '🇦🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Algeria',
                'code' => 'DZ',
                'flag' => '🇩🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'American Samoa',
                'code' => 'AS',
                'flag' => '🇦🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Andorra',
                'code' => 'AD',
                'flag' => '🇦🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'Angola',
                'code' => 'AO',
                'flag' => '🇦🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'Anguilla',
                'code' => 'AI',
                'flag' => '🇦🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            8 => 
            array (
                'id' => '9',
                'name' => 'Antarctica',
                'code' => 'AQ',
                'flag' => '🇦🇶',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            9 => 
            array (
                'id' => '10',
                'name' => 'Antigua and Barbuda',
                'code' => 'AG',
                'flag' => '🇦🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            10 => 
            array (
                'id' => '11',
                'name' => 'Argentina',
                'code' => 'AR',
                'flag' => '🇦🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            11 => 
            array (
                'id' => '12',
                'name' => 'Armenia',
                'code' => 'AM',
                'flag' => '🇦🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            12 => 
            array (
                'id' => '13',
                'name' => 'Aruba',
                'code' => 'AW',
                'flag' => '🇦🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            13 => 
            array (
                'id' => '14',
                'name' => 'Australia',
                'code' => 'AU',
                'flag' => '🇦🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            14 => 
            array (
                'id' => '15',
                'name' => 'Austria',
                'code' => 'AT',
                'flag' => '🇦🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            15 => 
            array (
                'id' => '16',
                'name' => 'Azerbaijan',
                'code' => 'AZ',
                'flag' => '🇦🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            16 => 
            array (
                'id' => '17',
                'name' => 'The Bahamas',
                'code' => 'BS',
                'flag' => '🇧🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            17 => 
            array (
                'id' => '18',
                'name' => 'Bahrain',
                'code' => 'BH',
                'flag' => '🇧🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            18 => 
            array (
                'id' => '19',
                'name' => 'Bangladesh',
                'code' => 'BD',
                'flag' => '🇧🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            19 => 
            array (
                'id' => '20',
                'name' => 'Barbados',
                'code' => 'BB',
                'flag' => '🇧🇧',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            20 => 
            array (
                'id' => '21',
                'name' => 'Belarus',
                'code' => 'BY',
                'flag' => '🇧🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            21 => 
            array (
                'id' => '22',
                'name' => 'Belgium',
                'code' => 'BE',
                'flag' => '🇧🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            22 => 
            array (
                'id' => '23',
                'name' => 'Belize',
                'code' => 'BZ',
                'flag' => '🇧🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            23 => 
            array (
                'id' => '24',
                'name' => 'Benin',
                'code' => 'BJ',
                'flag' => '🇧🇯',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            24 => 
            array (
                'id' => '25',
                'name' => 'Bermuda',
                'code' => 'BM',
                'flag' => '🇧🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            25 => 
            array (
                'id' => '26',
                'name' => 'Bhutan',
                'code' => 'BT',
                'flag' => '🇧🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            26 => 
            array (
                'id' => '27',
                'name' => 'Bolivia',
                'code' => 'BO',
                'flag' => '🇧🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            27 => 
            array (
                'id' => '28',
                'name' => 'Bosnia and Herzegovina',
                'code' => 'BA',
                'flag' => '🇧🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            28 => 
            array (
                'id' => '29',
                'name' => 'Botswana',
                'code' => 'BW',
                'flag' => '🇧🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            29 => 
            array (
                'id' => '30',
                'name' => 'Bouvet Island',
                'code' => 'BV',
                'flag' => '🇧🇻',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            30 => 
            array (
                'id' => '31',
                'name' => 'Brazil',
                'code' => 'BR',
                'flag' => '🇧🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            31 => 
            array (
                'id' => '32',
                'name' => 'British Indian Ocean Territory',
                'code' => 'IO',
                'flag' => '🇮🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            32 => 
            array (
                'id' => '33',
                'name' => 'Brunei',
                'code' => 'BN',
                'flag' => '🇧🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            33 => 
            array (
                'id' => '34',
                'name' => 'Bulgaria',
                'code' => 'BG',
                'flag' => '🇧🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            34 => 
            array (
                'id' => '35',
                'name' => 'Burkina Faso',
                'code' => 'BF',
                'flag' => '🇧🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            35 => 
            array (
                'id' => '36',
                'name' => 'Burundi',
                'code' => 'BI',
                'flag' => '🇧🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            36 => 
            array (
                'id' => '37',
                'name' => 'Cambodia',
                'code' => 'KH',
                'flag' => '🇰🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            37 => 
            array (
                'id' => '38',
                'name' => 'Cameroon',
                'code' => 'CM',
                'flag' => '🇨🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            38 => 
            array (
                'id' => '39',
                'name' => 'Canada',
                'code' => 'CA',
                'flag' => '🇨🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            39 => 
            array (
                'id' => '40',
                'name' => 'Cape Verde',
                'code' => 'CV',
                'flag' => '🇨🇻',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            40 => 
            array (
                'id' => '41',
                'name' => 'Cayman Islands',
                'code' => 'KY',
                'flag' => '🇰🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            41 => 
            array (
                'id' => '42',
                'name' => 'Central African Republic',
                'code' => 'CF',
                'flag' => '🇨🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            42 => 
            array (
                'id' => '43',
                'name' => 'Chad',
                'code' => 'TD',
                'flag' => '🇹🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            43 => 
            array (
                'id' => '44',
                'name' => 'Chile',
                'code' => 'CL',
                'flag' => '🇨🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            44 => 
            array (
                'id' => '45',
                'name' => 'China',
                'code' => 'CN',
                'flag' => '🇨🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            45 => 
            array (
                'id' => '46',
                'name' => 'Christmas Island',
                'code' => 'CX',
                'flag' => '🇨🇽',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            46 => 
            array (
                'id' => '47',
            'name' => 'Cocos (Keeling) Islands',
                'code' => 'CC',
                'flag' => '🇨🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            47 => 
            array (
                'id' => '48',
                'name' => 'Colombia',
                'code' => 'CO',
                'flag' => '🇨🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            48 => 
            array (
                'id' => '49',
                'name' => 'Comoros',
                'code' => 'KM',
                'flag' => '🇰🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            49 => 
            array (
                'id' => '50',
                'name' => 'Congo',
                'code' => 'CG',
                'flag' => '🇨🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            50 => 
            array (
                'id' => '51',
                'name' => 'Democratic Republic of the Congo',
                'code' => 'CD',
                'flag' => '🇨🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            51 => 
            array (
                'id' => '52',
                'name' => 'Cook Islands',
                'code' => 'CK',
                'flag' => '🇨🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            52 => 
            array (
                'id' => '53',
                'name' => 'Costa Rica',
                'code' => 'CR',
                'flag' => '🇨🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            53 => 
            array (
                'id' => '54',
                'name' => 'Ivory Coast',
                'code' => 'CI',
                'flag' => '🇨🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            54 => 
            array (
                'id' => '55',
                'name' => 'Croatia',
                'code' => 'HR',
                'flag' => '🇭🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            55 => 
            array (
                'id' => '56',
                'name' => 'Cuba',
                'code' => 'CU',
                'flag' => '🇨🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            56 => 
            array (
                'id' => '57',
                'name' => 'Cyprus',
                'code' => 'CY',
                'flag' => '🇨🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            57 => 
            array (
                'id' => '58',
                'name' => 'Czech Republic',
                'code' => 'CZ',
                'flag' => '🇨🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            58 => 
            array (
                'id' => '59',
                'name' => 'Denmark',
                'code' => 'DK',
                'flag' => '🇩🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            59 => 
            array (
                'id' => '60',
                'name' => 'Djibouti',
                'code' => 'DJ',
                'flag' => '🇩🇯',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            60 => 
            array (
                'id' => '61',
                'name' => 'Dominica',
                'code' => 'DM',
                'flag' => '🇩🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            61 => 
            array (
                'id' => '62',
                'name' => 'Dominican Republic',
                'code' => 'DO',
                'flag' => '🇩🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            62 => 
            array (
                'id' => '63',
                'name' => 'Timor-Leste',
                'code' => 'TL',
                'flag' => '🇹🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            63 => 
            array (
                'id' => '64',
                'name' => 'Ecuador',
                'code' => 'EC',
                'flag' => '🇪🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            64 => 
            array (
                'id' => '65',
                'name' => 'Egypt',
                'code' => 'EG',
                'flag' => '🇪🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            65 => 
            array (
                'id' => '66',
                'name' => 'El Salvador',
                'code' => 'SV',
                'flag' => '🇸🇻',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            66 => 
            array (
                'id' => '67',
                'name' => 'Equatorial Guinea',
                'code' => 'GQ',
                'flag' => '🇬🇶',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            67 => 
            array (
                'id' => '68',
                'name' => 'Eritrea',
                'code' => 'ER',
                'flag' => '🇪🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            68 => 
            array (
                'id' => '69',
                'name' => 'Estonia',
                'code' => 'EE',
                'flag' => '🇪🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            69 => 
            array (
                'id' => '70',
                'name' => 'Ethiopia',
                'code' => 'ET',
                'flag' => '🇪🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            70 => 
            array (
                'id' => '71',
                'name' => 'Falkland Islands',
                'code' => 'FK',
                'flag' => '🇫🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            71 => 
            array (
                'id' => '72',
                'name' => 'Faroe Islands',
                'code' => 'FO',
                'flag' => '🇫🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            72 => 
            array (
                'id' => '73',
                'name' => 'Fiji Islands',
                'code' => 'FJ',
                'flag' => '🇫🇯',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            73 => 
            array (
                'id' => '74',
                'name' => 'Finland',
                'code' => 'FI',
                'flag' => '🇫🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            74 => 
            array (
                'id' => '75',
                'name' => 'France',
                'code' => 'FR',
                'flag' => '🇫🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            75 => 
            array (
                'id' => '76',
                'name' => 'French Guiana',
                'code' => 'GF',
                'flag' => '🇬🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            76 => 
            array (
                'id' => '77',
                'name' => 'French Polynesia',
                'code' => 'PF',
                'flag' => '🇵🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            77 => 
            array (
                'id' => '78',
                'name' => 'French Southern Territories',
                'code' => 'TF',
                'flag' => '🇹🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            78 => 
            array (
                'id' => '79',
                'name' => 'Gabon',
                'code' => 'GA',
                'flag' => '🇬🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            79 => 
            array (
                'id' => '80',
                'name' => 'The Gambia',
                'code' => 'GM',
                'flag' => '🇬🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            80 => 
            array (
                'id' => '81',
                'name' => 'Georgia',
                'code' => 'GE',
                'flag' => '🇬🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            81 => 
            array (
                'id' => '82',
                'name' => 'Germany',
                'code' => 'DE',
                'flag' => '🇩🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            82 => 
            array (
                'id' => '83',
                'name' => 'Ghana',
                'code' => 'GH',
                'flag' => '🇬🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            83 => 
            array (
                'id' => '84',
                'name' => 'Gibraltar',
                'code' => 'GI',
                'flag' => '🇬🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            84 => 
            array (
                'id' => '85',
                'name' => 'Greece',
                'code' => 'GR',
                'flag' => '🇬🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            85 => 
            array (
                'id' => '86',
                'name' => 'Greenland',
                'code' => 'GL',
                'flag' => '🇬🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            86 => 
            array (
                'id' => '87',
                'name' => 'Grenada',
                'code' => 'GD',
                'flag' => '🇬🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            87 => 
            array (
                'id' => '88',
                'name' => 'Guadeloupe',
                'code' => 'GP',
                'flag' => '🇬🇵',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            88 => 
            array (
                'id' => '89',
                'name' => 'Guam',
                'code' => 'GU',
                'flag' => '🇬🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            89 => 
            array (
                'id' => '90',
                'name' => 'Guatemala',
                'code' => 'GT',
                'flag' => '🇬🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            90 => 
            array (
                'id' => '91',
                'name' => 'Guernsey',
                'code' => 'GG',
                'flag' => '🇬🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            91 => 
            array (
                'id' => '92',
                'name' => 'Guinea',
                'code' => 'GN',
                'flag' => '🇬🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            92 => 
            array (
                'id' => '93',
                'name' => 'Guinea-Bissau',
                'code' => 'GW',
                'flag' => '🇬🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            93 => 
            array (
                'id' => '94',
                'name' => 'Guyana',
                'code' => 'GY',
                'flag' => '🇬🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            94 => 
            array (
                'id' => '95',
                'name' => 'Haiti',
                'code' => 'HT',
                'flag' => '🇭🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            95 => 
            array (
                'id' => '96',
                'name' => 'Heard Island and McDonald Islands',
                'code' => 'HM',
                'flag' => '🇭🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            96 => 
            array (
                'id' => '97',
                'name' => 'Honduras',
                'code' => 'HN',
                'flag' => '🇭🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            97 => 
            array (
                'id' => '98',
                'name' => 'Hong Kong S.A.R.',
                'code' => 'HK',
                'flag' => '🇭🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            98 => 
            array (
                'id' => '99',
                'name' => 'Hungary',
                'code' => 'HU',
                'flag' => '🇭🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            99 => 
            array (
                'id' => '100',
                'name' => 'Iceland',
                'code' => 'IS',
                'flag' => '🇮🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            100 => 
            array (
                'id' => '101',
                'name' => 'India',
                'code' => 'IN',
                'flag' => '🇮🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            101 => 
            array (
                'id' => '102',
                'name' => 'Indonesia',
                'code' => 'ID',
                'flag' => '🇮🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            102 => 
            array (
                'id' => '103',
                'name' => 'Iran',
                'code' => 'IR',
                'flag' => '🇮🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            103 => 
            array (
                'id' => '104',
                'name' => 'Iraq',
                'code' => 'IQ',
                'flag' => '🇮🇶',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            104 => 
            array (
                'id' => '105',
                'name' => 'Ireland',
                'code' => 'IE',
                'flag' => '🇮🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            105 => 
            array (
                'id' => '106',
                'name' => 'Israel',
                'code' => 'IL',
                'flag' => '🇮🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            106 => 
            array (
                'id' => '107',
                'name' => 'Italy',
                'code' => 'IT',
                'flag' => '🇮🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            107 => 
            array (
                'id' => '108',
                'name' => 'Jamaica',
                'code' => 'JM',
                'flag' => '🇯🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            108 => 
            array (
                'id' => '109',
                'name' => 'Japan',
                'code' => 'JP',
                'flag' => '🇯🇵',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            109 => 
            array (
                'id' => '110',
                'name' => 'Jersey',
                'code' => 'JE',
                'flag' => '🇯🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            110 => 
            array (
                'id' => '111',
                'name' => 'Jordan',
                'code' => 'JO',
                'flag' => '🇯🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            111 => 
            array (
                'id' => '112',
                'name' => 'Kazakhstan',
                'code' => 'KZ',
                'flag' => '🇰🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            112 => 
            array (
                'id' => '113',
                'name' => 'Kenya',
                'code' => 'KE',
                'flag' => '🇰🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            113 => 
            array (
                'id' => '114',
                'name' => 'Kiribati',
                'code' => 'KI',
                'flag' => '🇰🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            114 => 
            array (
                'id' => '115',
                'name' => 'North Korea',
                'code' => 'KP',
                'flag' => '🇰🇵',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            115 => 
            array (
                'id' => '116',
                'name' => 'South Korea',
                'code' => 'KR',
                'flag' => '🇰🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            116 => 
            array (
                'id' => '117',
                'name' => 'Kuwait',
                'code' => 'KW',
                'flag' => '🇰🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            117 => 
            array (
                'id' => '118',
                'name' => 'Kyrgyzstan',
                'code' => 'KG',
                'flag' => '🇰🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            118 => 
            array (
                'id' => '119',
                'name' => 'Laos',
                'code' => 'LA',
                'flag' => '🇱🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            119 => 
            array (
                'id' => '120',
                'name' => 'Latvia',
                'code' => 'LV',
                'flag' => '🇱🇻',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            120 => 
            array (
                'id' => '121',
                'name' => 'Lebanon',
                'code' => 'LB',
                'flag' => '🇱🇧',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            121 => 
            array (
                'id' => '122',
                'name' => 'Lesotho',
                'code' => 'LS',
                'flag' => '🇱🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            122 => 
            array (
                'id' => '123',
                'name' => 'Liberia',
                'code' => 'LR',
                'flag' => '🇱🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            123 => 
            array (
                'id' => '124',
                'name' => 'Libya',
                'code' => 'LY',
                'flag' => '🇱🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            124 => 
            array (
                'id' => '125',
                'name' => 'Liechtenstein',
                'code' => 'LI',
                'flag' => '🇱🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            125 => 
            array (
                'id' => '126',
                'name' => 'Lithuania',
                'code' => 'LT',
                'flag' => '🇱🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            126 => 
            array (
                'id' => '127',
                'name' => 'Luxembourg',
                'code' => 'LU',
                'flag' => '🇱🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            127 => 
            array (
                'id' => '128',
                'name' => 'Macau S.A.R.',
                'code' => 'MO',
                'flag' => '🇲🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            128 => 
            array (
                'id' => '129',
                'name' => 'North Macedonia',
                'code' => 'MK',
                'flag' => '🇲🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            129 => 
            array (
                'id' => '130',
                'name' => 'Madagascar',
                'code' => 'MG',
                'flag' => '🇲🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            130 => 
            array (
                'id' => '131',
                'name' => 'Malawi',
                'code' => 'MW',
                'flag' => '🇲🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            131 => 
            array (
                'id' => '132',
                'name' => 'Malaysia',
                'code' => 'MY',
                'flag' => '🇲🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            132 => 
            array (
                'id' => '133',
                'name' => 'Maldives',
                'code' => 'MV',
                'flag' => '🇲🇻',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            133 => 
            array (
                'id' => '134',
                'name' => 'Mali',
                'code' => 'ML',
                'flag' => '🇲🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            134 => 
            array (
                'id' => '135',
                'name' => 'Malta',
                'code' => 'MT',
                'flag' => '🇲🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            135 => 
            array (
                'id' => '136',
            'name' => 'Man (Isle of)',
                'code' => 'IM',
                'flag' => '🇮🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            136 => 
            array (
                'id' => '137',
                'name' => 'Marshall Islands',
                'code' => 'MH',
                'flag' => '🇲🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            137 => 
            array (
                'id' => '138',
                'name' => 'Martinique',
                'code' => 'MQ',
                'flag' => '🇲🇶',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            138 => 
            array (
                'id' => '139',
                'name' => 'Mauritania',
                'code' => 'MR',
                'flag' => '🇲🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            139 => 
            array (
                'id' => '140',
                'name' => 'Mauritius',
                'code' => 'MU',
                'flag' => '🇲🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            140 => 
            array (
                'id' => '141',
                'name' => 'Mayotte',
                'code' => 'YT',
                'flag' => '🇾🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            141 => 
            array (
                'id' => '142',
                'name' => 'Mexico',
                'code' => 'MX',
                'flag' => '🇲🇽',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            142 => 
            array (
                'id' => '143',
                'name' => 'Micronesia',
                'code' => 'FM',
                'flag' => '🇫🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            143 => 
            array (
                'id' => '144',
                'name' => 'Moldova',
                'code' => 'MD',
                'flag' => '🇲🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            144 => 
            array (
                'id' => '145',
                'name' => 'Monaco',
                'code' => 'MC',
                'flag' => '🇲🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            145 => 
            array (
                'id' => '146',
                'name' => 'Mongolia',
                'code' => 'MN',
                'flag' => '🇲🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            146 => 
            array (
                'id' => '147',
                'name' => 'Montenegro',
                'code' => 'ME',
                'flag' => '🇲🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            147 => 
            array (
                'id' => '148',
                'name' => 'Montserrat',
                'code' => 'MS',
                'flag' => '🇲🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            148 => 
            array (
                'id' => '149',
                'name' => 'Morocco',
                'code' => 'MA',
                'flag' => '🇲🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            149 => 
            array (
                'id' => '150',
                'name' => 'Mozambique',
                'code' => 'MZ',
                'flag' => '🇲🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            150 => 
            array (
                'id' => '151',
                'name' => 'Myanmar',
                'code' => 'MM',
                'flag' => '🇲🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            151 => 
            array (
                'id' => '152',
                'name' => 'Namibia',
                'code' => 'NA',
                'flag' => '🇳🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            152 => 
            array (
                'id' => '153',
                'name' => 'Nauru',
                'code' => 'NR',
                'flag' => '🇳🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            153 => 
            array (
                'id' => '154',
                'name' => 'Nepal',
                'code' => 'NP',
                'flag' => '🇳🇵',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            154 => 
            array (
                'id' => '155',
                'name' => 'Bonaire, Sint Eustatius and Saba',
                'code' => 'BQ',
                'flag' => '🇧🇶',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            155 => 
            array (
                'id' => '156',
                'name' => 'Netherlands',
                'code' => 'NL',
                'flag' => '🇳🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            156 => 
            array (
                'id' => '157',
                'name' => 'New Caledonia',
                'code' => 'NC',
                'flag' => '🇳🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            157 => 
            array (
                'id' => '158',
                'name' => 'New Zealand',
                'code' => 'NZ',
                'flag' => '🇳🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            158 => 
            array (
                'id' => '159',
                'name' => 'Nicaragua',
                'code' => 'NI',
                'flag' => '🇳🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            159 => 
            array (
                'id' => '160',
                'name' => 'Niger',
                'code' => 'NE',
                'flag' => '🇳🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            160 => 
            array (
                'id' => '161',
                'name' => 'Nigeria',
                'code' => 'NG',
                'flag' => '🇳🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            161 => 
            array (
                'id' => '162',
                'name' => 'Niue',
                'code' => 'NU',
                'flag' => '🇳🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            162 => 
            array (
                'id' => '163',
                'name' => 'Norfolk Island',
                'code' => 'NF',
                'flag' => '🇳🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            163 => 
            array (
                'id' => '164',
                'name' => 'Northern Mariana Islands',
                'code' => 'MP',
                'flag' => '🇲🇵',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            164 => 
            array (
                'id' => '165',
                'name' => 'Norway',
                'code' => 'NO',
                'flag' => '🇳🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            165 => 
            array (
                'id' => '166',
                'name' => 'Oman',
                'code' => 'OM',
                'flag' => '🇴🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            166 => 
            array (
                'id' => '167',
                'name' => 'Pakistan',
                'code' => 'PK',
                'flag' => '🇵🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            167 => 
            array (
                'id' => '168',
                'name' => 'Palau',
                'code' => 'PW',
                'flag' => '🇵🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            168 => 
            array (
                'id' => '169',
                'name' => 'Palestinian Territory Occupied',
                'code' => 'PS',
                'flag' => '🇵🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            169 => 
            array (
                'id' => '170',
                'name' => 'Panama',
                'code' => 'PA',
                'flag' => '🇵🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            170 => 
            array (
                'id' => '171',
                'name' => 'Papua New Guinea',
                'code' => 'PG',
                'flag' => '🇵🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            171 => 
            array (
                'id' => '172',
                'name' => 'Paraguay',
                'code' => 'PY',
                'flag' => '🇵🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            172 => 
            array (
                'id' => '173',
                'name' => 'Peru',
                'code' => 'PE',
                'flag' => '🇵🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            173 => 
            array (
                'id' => '174',
                'name' => 'Philippines',
                'code' => 'PH',
                'flag' => '🇵🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            174 => 
            array (
                'id' => '175',
                'name' => 'Pitcairn Island',
                'code' => 'PN',
                'flag' => '🇵🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            175 => 
            array (
                'id' => '176',
                'name' => 'Poland',
                'code' => 'PL',
                'flag' => '🇵🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            176 => 
            array (
                'id' => '177',
                'name' => 'Portugal',
                'code' => 'PT',
                'flag' => '🇵🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            177 => 
            array (
                'id' => '178',
                'name' => 'Puerto Rico',
                'code' => 'PR',
                'flag' => '🇵🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            178 => 
            array (
                'id' => '179',
                'name' => 'Qatar',
                'code' => 'QA',
                'flag' => '🇶🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            179 => 
            array (
                'id' => '180',
                'name' => 'Reunion',
                'code' => 'RE',
                'flag' => '🇷🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            180 => 
            array (
                'id' => '181',
                'name' => 'Romania',
                'code' => 'RO',
                'flag' => '🇷🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            181 => 
            array (
                'id' => '182',
                'name' => 'Russia',
                'code' => 'RU',
                'flag' => '🇷🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            182 => 
            array (
                'id' => '183',
                'name' => 'Rwanda',
                'code' => 'RW',
                'flag' => '🇷🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            183 => 
            array (
                'id' => '184',
                'name' => 'Saint Helena',
                'code' => 'SH',
                'flag' => '🇸🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            184 => 
            array (
                'id' => '185',
                'name' => 'Saint Kitts and Nevis',
                'code' => 'KN',
                'flag' => '🇰🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            185 => 
            array (
                'id' => '186',
                'name' => 'Saint Lucia',
                'code' => 'LC',
                'flag' => '🇱🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            186 => 
            array (
                'id' => '187',
                'name' => 'Saint Pierre and Miquelon',
                'code' => 'PM',
                'flag' => '🇵🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            187 => 
            array (
                'id' => '188',
                'name' => 'Saint Vincent and the Grenadines',
                'code' => 'VC',
                'flag' => '🇻🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            188 => 
            array (
                'id' => '189',
                'name' => 'Saint-Barthelemy',
                'code' => 'BL',
                'flag' => '🇧🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            189 => 
            array (
                'id' => '190',
            'name' => 'Saint-Martin (French part)',
                'code' => 'MF',
                'flag' => '🇲🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            190 => 
            array (
                'id' => '191',
                'name' => 'Samoa',
                'code' => 'WS',
                'flag' => '🇼🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            191 => 
            array (
                'id' => '192',
                'name' => 'San Marino',
                'code' => 'SM',
                'flag' => '🇸🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            192 => 
            array (
                'id' => '193',
                'name' => 'Sao Tome and Principe',
                'code' => 'ST',
                'flag' => '🇸🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            193 => 
            array (
                'id' => '194',
                'name' => 'Saudi Arabia',
                'code' => 'SA',
                'flag' => '🇸🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            194 => 
            array (
                'id' => '195',
                'name' => 'Senegal',
                'code' => 'SN',
                'flag' => '🇸🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            195 => 
            array (
                'id' => '196',
                'name' => 'Serbia',
                'code' => 'RS',
                'flag' => '🇷🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            196 => 
            array (
                'id' => '197',
                'name' => 'Seychelles',
                'code' => 'SC',
                'flag' => '🇸🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            197 => 
            array (
                'id' => '198',
                'name' => 'Sierra Leone',
                'code' => 'SL',
                'flag' => '🇸🇱',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            198 => 
            array (
                'id' => '199',
                'name' => 'Singapore',
                'code' => 'SG',
                'flag' => '🇸🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            199 => 
            array (
                'id' => '200',
                'name' => 'Slovakia',
                'code' => 'SK',
                'flag' => '🇸🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            200 => 
            array (
                'id' => '201',
                'name' => 'Slovenia',
                'code' => 'SI',
                'flag' => '🇸🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            201 => 
            array (
                'id' => '202',
                'name' => 'Solomon Islands',
                'code' => 'SB',
                'flag' => '🇸🇧',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            202 => 
            array (
                'id' => '203',
                'name' => 'Somalia',
                'code' => 'SO',
                'flag' => '🇸🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            203 => 
            array (
                'id' => '204',
                'name' => 'South Africa',
                'code' => 'ZA',
                'flag' => '🇿🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            204 => 
            array (
                'id' => '205',
                'name' => 'South Georgia',
                'code' => 'GS',
                'flag' => '🇬🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            205 => 
            array (
                'id' => '206',
                'name' => 'South Sudan',
                'code' => 'SS',
                'flag' => '🇸🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            206 => 
            array (
                'id' => '207',
                'name' => 'Spain',
                'code' => 'ES',
                'flag' => '🇪🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            207 => 
            array (
                'id' => '208',
                'name' => 'Sri Lanka',
                'code' => 'LK',
                'flag' => '🇱🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            208 => 
            array (
                'id' => '209',
                'name' => 'Sudan',
                'code' => 'SD',
                'flag' => '🇸🇩',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            209 => 
            array (
                'id' => '210',
                'name' => 'Suriname',
                'code' => 'SR',
                'flag' => '🇸🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            210 => 
            array (
                'id' => '211',
                'name' => 'Svalbard and Jan Mayen Islands',
                'code' => 'SJ',
                'flag' => '🇸🇯',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            211 => 
            array (
                'id' => '212',
                'name' => 'Eswatini',
                'code' => 'SZ',
                'flag' => '🇸🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            212 => 
            array (
                'id' => '213',
                'name' => 'Sweden',
                'code' => 'SE',
                'flag' => '🇸🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            213 => 
            array (
                'id' => '214',
                'name' => 'Switzerland',
                'code' => 'CH',
                'flag' => '🇨🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            214 => 
            array (
                'id' => '215',
                'name' => 'Syria',
                'code' => 'SY',
                'flag' => '🇸🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            215 => 
            array (
                'id' => '216',
                'name' => 'Taiwan',
                'code' => 'TW',
                'flag' => '🇹🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            216 => 
            array (
                'id' => '217',
                'name' => 'Tajikistan',
                'code' => 'TJ',
                'flag' => '🇹🇯',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            217 => 
            array (
                'id' => '218',
                'name' => 'Tanzania',
                'code' => 'TZ',
                'flag' => '🇹🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            218 => 
            array (
                'id' => '219',
                'name' => 'Thailand',
                'code' => 'TH',
                'flag' => '🇹🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            219 => 
            array (
                'id' => '220',
                'name' => 'Togo',
                'code' => 'TG',
                'flag' => '🇹🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            220 => 
            array (
                'id' => '221',
                'name' => 'Tokelau',
                'code' => 'TK',
                'flag' => '🇹🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            221 => 
            array (
                'id' => '222',
                'name' => 'Tonga',
                'code' => 'TO',
                'flag' => '🇹🇴',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            222 => 
            array (
                'id' => '223',
                'name' => 'Trinidad and Tobago',
                'code' => 'TT',
                'flag' => '🇹🇹',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            223 => 
            array (
                'id' => '224',
                'name' => 'Tunisia',
                'code' => 'TN',
                'flag' => '🇹🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            224 => 
            array (
                'id' => '225',
                'name' => 'Turkey',
                'code' => 'TR',
                'flag' => '🇹🇷',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            225 => 
            array (
                'id' => '226',
                'name' => 'Turkmenistan',
                'code' => 'TM',
                'flag' => '🇹🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            226 => 
            array (
                'id' => '227',
                'name' => 'Turks and Caicos Islands',
                'code' => 'TC',
                'flag' => '🇹🇨',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            227 => 
            array (
                'id' => '228',
                'name' => 'Tuvalu',
                'code' => 'TV',
                'flag' => '🇹🇻',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            228 => 
            array (
                'id' => '229',
                'name' => 'Uganda',
                'code' => 'UG',
                'flag' => '🇺🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            229 => 
            array (
                'id' => '230',
                'name' => 'Ukraine',
                'code' => 'UA',
                'flag' => '🇺🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            230 => 
            array (
                'id' => '231',
                'name' => 'United Arab Emirates',
                'code' => 'AE',
                'flag' => '🇦🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            231 => 
            array (
                'id' => '232',
                'name' => 'United Kingdom',
                'code' => 'GB',
                'flag' => '🇬🇧',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            232 => 
            array (
                'id' => '233',
                'name' => 'United States',
                'code' => 'US',
                'flag' => '🇺🇸',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            233 => 
            array (
                'id' => '234',
                'name' => 'United States Minor Outlying Islands',
                'code' => 'UM',
                'flag' => '🇺🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            234 => 
            array (
                'id' => '235',
                'name' => 'Uruguay',
                'code' => 'UY',
                'flag' => '🇺🇾',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            235 => 
            array (
                'id' => '236',
                'name' => 'Uzbekistan',
                'code' => 'UZ',
                'flag' => '🇺🇿',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            236 => 
            array (
                'id' => '237',
                'name' => 'Vanuatu',
                'code' => 'VU',
                'flag' => '🇻🇺',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            237 => 
            array (
                'id' => '238',
            'name' => 'Vatican City State (Holy See)',
                'code' => 'VA',
                'flag' => '🇻🇦',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            238 => 
            array (
                'id' => '239',
                'name' => 'Venezuela',
                'code' => 'VE',
                'flag' => '🇻🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            239 => 
            array (
                'id' => '240',
                'name' => 'Vietnam',
                'code' => 'VN',
                'flag' => '🇻🇳',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            240 => 
            array (
                'id' => '241',
            'name' => 'Virgin Islands (British)',
                'code' => 'VG',
                'flag' => '🇻🇬',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            241 => 
            array (
                'id' => '242',
            'name' => 'Virgin Islands (US)',
                'code' => 'VI',
                'flag' => '🇻🇮',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            242 => 
            array (
                'id' => '243',
                'name' => 'Wallis and Futuna Islands',
                'code' => 'WF',
                'flag' => '🇼🇫',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            243 => 
            array (
                'id' => '244',
                'name' => 'Western Sahara',
                'code' => 'EH',
                'flag' => '🇪🇭',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            244 => 
            array (
                'id' => '245',
                'name' => 'Yemen',
                'code' => 'YE',
                'flag' => '🇾🇪',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            245 => 
            array (
                'id' => '246',
                'name' => 'Zambia',
                'code' => 'ZM',
                'flag' => '🇿🇲',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            246 => 
            array (
                'id' => '247',
                'name' => 'Zimbabwe',
                'code' => 'ZW',
                'flag' => '🇿🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            247 => 
            array (
                'id' => '248',
                'name' => 'Kosovo',
                'code' => 'XK',
                'flag' => '🇽🇰',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            248 => 
            array (
                'id' => '249',
                'name' => 'Curaçao',
                'code' => 'CW',
                'flag' => '🇨🇼',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
            249 => 
            array (
                'id' => '250',
            'name' => 'Sint Maarten (Dutch part)',
                'code' => 'SX',
                'flag' => '🇸🇽',
                'created_at' => '2026-02-03 21:13:57',
                'updated_at' => '2026-02-03 21:13:57',
            ),
        ));
        
        
    }
}