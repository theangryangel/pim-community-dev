<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param NormalizerInterface                 $normalizer
     * @param ChannelRepositoryInterface          $channelRepository
     * @param LocaleRepositoryInterface           $localeRepository
     * @param AttributeRepositoryInterface        $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $normalizerOptions = [];
        $channel = null;

        if ($request->query->has('channel')) {
            $channel = $this->channelRepository->findOneByIdentifier($request->query->get('channel'));
            if (null === $channel) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Channel "%s" does not exist.', $request->query->get('channel'))
                );
            }

            $normalizerOptions['channels'] = [$channel->getCode()];
            $normalizerOptions['locales'] = $channel->getLocaleCodes();
        }

        if ($request->query->has('locales')) {
            $this->checkLocalesParameters($request->query->get('locales'), $channel);

            $normalizerOptions['locales'] = explode(',', $request->query->get('locales'));
        }

        if ($request->query->has('attributes')) {
            $this->checkAttributesParameters($request->query->get('attributes'));

            $normalizerOptions['attributes'] = $request->query->get('attributes');
        }

        $pqb = $this->pqbFactory->create([]);
        // TODO: be able to use the PQB filters (will be done in API-65).
        // for the moment, set an empty array
        $this->setPQBFilters($pqb, [], $channel);

        $pqb->getQueryBuilder()->setMaxResults($request->query->getInt('limit', 10)); // limit will be put in config in an other PR
        $productStandard = $this->normalizer->normalize($pqb->execute(), 'external_api', $normalizerOptions);

        return new JsonResponse($productStandard);
    }

    /**
     * Set the PQB filters.
     * If a channel is requested, add a filter to return only products linked to its category tree
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param array                        $pqbFilters
     * @param ChannelInterface|null        $channel
     */
    protected function setPQBFilters(
        ProductQueryBuilderInterface $pqb,
        array $pqbFilters,
        ChannelInterface $channel = null
    ) {
        if (null !== $channel) {
            $pqbFilters['categories'] = [
                [
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => [$channel->getCategory()->getCode()]
                ]
            ];
        }

        foreach ($pqbFilters as $attributeCode => $filters) {
            foreach ($filters as $filter) {
                $pqb->addFilter($attributeCode, $filter['operator'], $filter['value']);
            }
        }
    }

    /**
     * Checks $localeCodes if they exist.
     * Thrown an exception if one of them does not exist or, if there is a $channel, one of them does not belong to it.
     *
     * @param string                $localeCodes
     * @param ChannelInterface|null $channel
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function checkLocalesParameters($localeCodes, ChannelInterface $channel = null)
    {
        $locales = explode(',', $localeCodes);

        $errors = [];
        foreach ($locales as $locale) {
            if (null === $this->localeRepository->findOneByIdentifier($locale)) {
                $errors[] = $locale;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Locales "%s" do not exist.' : 'Locale "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }

        if (null !== $channel) {
            if ($diff = array_diff($locales, $channel->getLocaleCodes())) {
                $plural = sprintf(count($diff) > 1 ? 'Locales "%s" are' : 'Locale "%s" is', implode(', ', $diff));
                throw new UnprocessableEntityHttpException(
                    sprintf('%s not activated for the channel "%s".', $plural, $channel->getCode())
                );
            }
        }
    }

    /**
     * Checks $attributes if they exist. Thrown an exception if one of them does not exist.
     *
     * @param string $attributes
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return array
     */
    protected function checkAttributesParameters($attributes)
    {
        $attributeCodes = explode(',', $attributes);

        $errors = [];
        foreach ($attributeCodes as $attributeCode) {
            if (null === $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $errors[] = $attributeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Attributes "%s" do not exist.' : 'Attribute "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
