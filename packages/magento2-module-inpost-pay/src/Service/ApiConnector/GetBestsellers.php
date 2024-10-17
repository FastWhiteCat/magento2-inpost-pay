<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use InPost\InPostPay\Model\IziApi\Request\GetBestsellersRequest;
use InPost\InPostPay\Model\IziApi\Request\GetBestsellersRequestFactory;
use InPost\InPostPay\Service\Converter\ArrayToBestsellersResultConverter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class GetBestsellers
{
    private const DEFAULT_PAGE_SIZE = 10;

    /**
     * @param ConnectorInterface $connector
     * @param GetBestsellersRequestFactory $getBestsellersRequestFactory
     * @param ArrayToBestsellersResultConverter $arrayToBestsellerProductConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly GetBestsellersRequestFactory $getBestsellersRequestFactory,
        private readonly ArrayToBestsellersResultConverter $arrayToBestsellerProductConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return BestsellerProductInterface[]
     * @throws LocalizedException
     */
    public function execute(): array
    {
        try {
            return $this->getAllBestsellerProducts();
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with getting payment methods. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    /**
     * @return BestsellerProductInterface[]
     */
    private function getAllBestsellerProducts(): array
    {
        $bestsellerProducts = [];
        $pageIndex = 1;

        do {
            /** @var GetBestsellersRequest $request */
            $request = $this->getBestsellersRequestFactory->create();
            $request->setParams(
                [
                    BestsellersInterface::PAGE_INDEX => $pageIndex,
                    BestsellersInterface::PAGE_SIZE => self::DEFAULT_PAGE_SIZE
                ]
            );

            try {
//                $resultArray = $this->connector->sendRequest($request);
                //TODO::Uncomment above and remove below fragment after InPost API handles those requests.
                $resultArray = [
                    'page_size' => 10,
                    'total_items' => 3,
                    'page_index' => 1,
                    'products' => [
                        [
                            'product_id' => '9',
                            'ean' => '24-WB02',
                            'qr_code' => '',
                            'deep_link' => '',
                            'product_available' => [
                                'start_date' => '2024-10-02',
                                'end_date' => '2024-10-30'
                            ],
                            'product_name' => 'Compete Track Tote',
                            'product_description' => 'The Compete Track Tote holds a host of exercise supplies with ease. Stash your towel, jacket and street shoes inside. Tuck water bottles in easy-access external spaces. Perfect for trips to gym or yoga studio, with dual top handles for convenience to and from.',
                            'product_image' => 'https://mage.localhost/media/catalog/product/w/b/wb02-green-0.jpg?width=700&height=700&store=default&image-type=image',
                            'additional_product_images' => [],
                            'price' => [
                                'net' => 26.02,
                                'gross' => 32.00,
                                'vat' => 5.98
                            ],
                            'currency' => 'PLN',
                            'quantity' => [
                                'quantity_type' => 'INT',
                                'available_quantity' => 45
                            ],
                            'product_attributes' => [
                                [
                                    'attribute_name' => 'Testowy Atrybut 1',
                                    'attribute_value' => 'testowa_wartosc_1'
                                ]
                            ]
                        ],
                        [
                            'product_id' => '14',
                            'ean' => '24-WB04',
                            'qr_code' => '',
                            'deep_link' => '',
                            'product_available' => [
                                'start_date' => '2024-10-03',
                                'end_date' => '2024-10-29'
                            ],
                            'product_name' => 'Push It Messenger Bag',
                            'product_description' => 'The name says so, but the Push It Messenger Bag is much more than a busy commuter\'s tote . It\'s a closet away from home when you\'re pedaling from class or work to gym and back or home again. It\'s the perfect size and shape for laptop, folded clothes, even extra shoes.',
                            'product_image' => 'https://mage.localhost/media/catalog/product/w/b/wb04-blue-0.jpg?width=700&height=700&store=default&image-type=image',
			                'additional_product_images' => [],
			                'price' => [
                                'net' => 32.52,
                                'gross' => 40.00,
                                'vat' => 7.48
                            ],
                            'currency' => 'PLN',
                            'quantity' => [
                                    'quantity_type' => 'INT',
                                    'available_quantity' => 56
                                ],
                            'product_attributes' => [
                                    [
                                        'attribute_name' => 'Testowy Atrybut 2',
                                        'attribute_value' => 'testowa_wartosc_2'
                                    ]
                                ]
                        ]
	                ],
                ];

                $bestsellerResult = $this->arrayToBestsellerProductConverter->convert($resultArray);
                $pageItems = $bestsellerResult->getProducts();
            } catch (LocalizedException $e) {
                $pageItems = [];
            }

            $pageIndex++;
            $bestsellerProducts = array_merge($bestsellerProducts, $pageItems);
            break; //TODO::remove this as well
        } while (!empty($pageItems));

        return $bestsellerProducts;
    }
}
