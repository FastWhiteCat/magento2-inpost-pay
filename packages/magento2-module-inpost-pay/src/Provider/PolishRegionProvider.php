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
        '00' => 'mazowieckie',
        '01' => 'mazowieckie',
        '02' => 'mazowieckie',
        '03' => 'mazowieckie',
        '04' => 'mazowieckie',
        '05' => 'mazowieckie',
        '06' => 'mazowieckie',
        '07' => 'mazowieckie',
        '08' => 'mazowieckie',
        '09' => 'mazowieckie',
        '10' => 'warmińsko-mazurskie',
        '11' => 'warmińsko-mazurskie',
        '12' => 'warmińsko-mazurskie',
        '13' => 'warmińsko-mazurskie',
        '14' => 'warmińsko-mazurskie',
        '15' => 'podlaskie',
        '16' => 'podlaskie',
        '17' => 'podlaskie',
        '18' => 'podlaskie',
        '19' => 'podlaskie',
        '20' => 'lubelskie',
        '21' => 'lubelskie',
        '22' => 'lubelskie',
        '23' => 'lubelskie',
        '24' => 'lubelskie',
        '25' => 'świętokrzyskie',
        '26' => 'świętokrzyskie',
        '27' => 'świętokrzyskie',
        '28' => 'świętokrzyskie',
        '29' => 'świętokrzyskie',
        '30' => 'małopolskie',
        '31' => 'małopolskie',
        '32' => 'małopolskie',
        '33' => 'małopolskie',
        '34' => 'małopolskie',
        '35' => 'podkarpackie',
        '36' => 'podkarpackie',
        '37' => 'podkarpackie',
        '38' => 'podkarpackie',
        '39' => 'podkarpackie',
        '40' => 'śląskie',
        '41' => 'śląskie',
        '42' => 'śląskie',
        '43' => 'śląskie',
        '44' => 'śląskie',
        '45' => 'opolskie',
        '46' => 'opolskie',
        '47' => 'opolskie',
        '48' => 'opolskie',
        '49' => 'opolskie',
        '50' => 'dolnośląskie',
        '51' => 'dolnośląskie',
        '52' => 'dolnośląskie',
        '53' => 'dolnośląskie',
        '54' => 'dolnośląskie',
        '55' => 'dolnośląskie',
        '56' => 'dolnośląskie',
        '57' => 'dolnośląskie',
        '58' => 'dolnośląskie',
        '59' => 'dolnośląskie',
        '60' => 'wielkopolskie',
        '61' => 'wielkopolskie',
        '62' => 'wielkopolskie',
        '63' => 'wielkopolskie',
        '64' => 'wielkopolskie',
        '65' => 'wielkopolskie',
        '66' => 'wielkopolskie',
        '67' => 'wielkopolskie',
        '68' => 'lubuskie',
        '69' => 'lubuskie',
        '70' => 'zachodniopomorskie',
        '71' => 'zachodniopomorskie',
        '72' => 'zachodniopomorskie',
        '73' => 'zachodniopomorskie',
        '74' => 'zachodniopomorskie',
        '75' => 'zachodniopomorskie',
        '76' => 'zachodniopomorskie',
        '77' => 'pomorskie',
        '78' => 'pomorskie',
        '79' => 'pomorskie',
        '80' => 'pomorskie',
        '81' => 'pomorskie',
        '82' => 'pomorskie',
        '83' => 'pomorskie',
        '84' => 'pomorskie',
        '85' => 'kujawsko-pomorskie',
        '86' => 'kujawsko-pomorskie',
        '87' => 'kujawsko-pomorskie',
        '88' => 'kujawsko-pomorskie',
        '89' => 'kujawsko-pomorskie',
        '90' => 'łódzkie',
        '91' => 'łódzkie',
        '92' => 'łódzkie',
        '93' => 'łódzkie',
        '94' => 'łódzkie',
        '95' => 'łódzkie',
        '96' => 'łódzkie',
        '97' => 'łódzkie',
        '98' => 'łódzkie',
        '99' => 'łódzkie',
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

        return $this->regionNameToIdMap[$regionName] ?? 0;
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
