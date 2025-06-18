<?php
/**
 * Copyright © Fast White Cat S.A. All rights reserved.
 * See LICENSE_FASTWHITECAT for license details.
 */

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Api\Provider\PolishRegionProviderInterface;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class PolishRegionProvider implements PolishRegionProviderInterface
{
    private array $postcodeToRegionMap = [
        '00' => 'Mazowieckie',
        '01' => 'Mazowieckie',
        '02' => 'Mazowieckie',
        '03' => 'Mazowieckie',
        '04' => 'Mazowieckie',
        '05' => 'Mazowieckie',
        '06' => 'Mazowieckie',
        '07' => 'Mazowieckie',
        '08' => 'Mazowieckie',
        '09' => 'Mazowieckie',
        '10' => 'Warmińsko-Mazurskie',
        '11' => 'Warmińsko-Mazurskie',
        '12' => 'Warmińsko-Mazurskie',
        '13' => 'Warmińsko-Mazurskie',
        '14' => 'Warmińsko-Mazurskie',
        '15' => 'Podlaskie',
        '16' => 'Podlaskie',
        '17' => 'Podlaskie',
        '18' => 'Podlaskie',
        '19' => 'Podlaskie',
        '20' => 'Lubelskie',
        '21' => 'Lubelskie',
        '22' => 'Lubelskie',
        '23' => 'Lubelskie',
        '24' => 'Lubelskie',
        '25' => 'Świętokrzyskie',
        '26' => 'Świętokrzyskie',
        '27' => 'Świętokrzyskie',
        '28' => 'Świętokrzyskie',
        '29' => 'Świętokrzyskie',
        '30' => 'Małopolskie',
        '31' => 'Małopolskie',
        '32' => 'Małopolskie',
        '33' => 'Małopolskie',
        '34' => 'Małopolskie',
        '35' => 'Podkarpackie',
        '36' => 'Podkarpackie',
        '37' => 'Podkarpackie',
        '38' => 'Podkarpackie',
        '39' => 'Podkarpackie',
        '40' => 'Śląskie',
        '41' => 'Śląskie',
        '42' => 'Śląskie',
        '43' => 'Śląskie',
        '44' => 'Śląskie',
        '45' => 'Opolskie',
        '46' => 'Opolskie',
        '47' => 'Opolskie',
        '48' => 'Opolskie',
        '49' => 'Opolskie',
        '50' => 'Dolnośląskie',
        '51' => 'Dolnośląskie',
        '52' => 'Dolnośląskie',
        '53' => 'Dolnośląskie',
        '54' => 'Dolnośląskie',
        '55' => 'Dolnośląskie',
        '56' => 'Dolnośląskie',
        '57' => 'Dolnośląskie',
        '58' => 'Dolnośląskie',
        '59' => 'Dolnośląskie',
        '60' => 'Wielkopolskie',
        '61' => 'Wielkopolskie',
        '62' => 'Wielkopolskie',
        '63' => 'Wielkopolskie',
        '64' => 'Wielkopolskie',
        '65' => 'Wielkopolskie',
        '66' => 'Wielkopolskie',
        '67' => 'Wielkopolskie',
        '68' => 'Lubuskie',
        '69' => 'Lubuskie',
        '70' => 'Zachodniopomorskie',
        '71' => 'Zachodniopomorskie',
        '72' => 'Zachodniopomorskie',
        '73' => 'Zachodniopomorskie',
        '74' => 'Zachodniopomorskie',
        '75' => 'Zachodniopomorskie',
        '76' => 'Zachodniopomorskie',
        '77' => 'Pomorskie',
        '78' => 'Pomorskie',
        '79' => 'Pomorskie',
        '80' => 'Pomorskie',
        '81' => 'Pomorskie',
        '82' => 'Pomorskie',
        '83' => 'Pomorskie',
        '84' => 'Pomorskie',
        '85' => 'Kujawsko-Pomorskie',
        '86' => 'Kujawsko-Pomorskie',
        '87' => 'Kujawsko-Pomorskie',
        '88' => 'Kujawsko-Pomorskie',
        '89' => 'Kujawsko-Pomorskie',
        '90' => 'Łódzkie',
        '91' => 'Łódzkie',
        '92' => 'Łódzkie',
        '93' => 'Łódzkie',
        '94' => 'Łódzkie',
        '95' => 'Łódzkie',
        '96' => 'Łódzkie',
        '97' => 'Łódzkie',
        '98' => 'Łódzkie',
        '99' => 'Łódzkie',
    ];

    /**
     * @var array|null
     */
    private ?array $regionNameToIdMap = null;

    /**
     * @param RegionCollectionFactory $regionCollectionFactory
     */
    public function __construct(
        private readonly RegionCollectionFactory $regionCollectionFactory
    ) {
    }

    /**
     * @param string $postcode
     * @return string
     */
    public function getRegionNameByPostcode(string $postcode): string
    {
        $postcode = preg_replace('/[^0-9]/', '', $postcode);
        $firstTwoDigits = substr((string)$postcode, 0, 2);

        return $this->postcodeToRegionMap[$firstTwoDigits] ?? '';
    }

    /**
     * @param string $regionName
     * @return int
     */
    public function getRegionIdByName(string $regionName): int
    {
        if ($this->regionNameToIdMap === null) {
            $this->initRegionNameToIdMap();
        }

        return $this->regionNameToIdMap[strtolower($regionName)] ?? 0;
    }

    /**
     * @return void
     */
    private function initRegionNameToIdMap(): void
    {
        $this->regionNameToIdMap = [];

        /** @var RegionCollection $collection */
        $collection = $this->regionCollectionFactory->create();
        $collection->addFieldToFilter('country_id', self::POLAND_COUNTRY_CODE);

        foreach ($collection as $region) {
            if (!$region instanceof Region) {
                continue;
            }

            $regionId = is_scalar($region->getId()) ? (int)$region->getId() : 0;

            if ($regionId) {
                $this->regionNameToIdMap[strtolower((string)$region->getName())] = $regionId;
            }
        }
    }
}
