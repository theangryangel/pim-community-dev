<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryController
{
    /** @var CategoryRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param CategoryRepositoryInterface $repository
     * @param NormalizerInterface         $normalizer
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        NormalizerInterface $normalizer
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $category = $this->repository->findOneByIdentifier($code);
        if (null === $category) {
            throw new NotFoundHttpException(sprintf('Category "%s" does not exist.', $code));
        }

        $categoryStandard = $this->normalizer->normalize($category, 'standard');

        return new JsonResponse($categoryStandard);
    }

    public function listAction(Request $request)
    {
        $code = $request->query->get('code');
        $limit = $request->query->getInt('limit', 3);
        $categories = $this->repository->findByCode($limit, $code);

        $standardCategories = [];
        foreach ($categories as $category) {
            $standardCategories[] = $this->normalizer->normalize($category, 'standard');
        }

        $count = $this->repository->countByCode($code);
        $url = 'http://pce-master.local/api/rest/v1/categories/?limit=' . $limit;

        $response = [
            'first'    => $url,
            'last'     => $url . '&code=' .$this->repository->findLastCursor($count, $limit, $code),
            'next'     => $url . '&code=' .end($standardCategories)['code']
        ];

        if (null !== $code) {
            $response['previous'] = $url .'&code=' . $this->repository->findPreviousCursor($limit, $code);
        }

        $response['items'] = $standardCategories;

        return new JsonResponse($response);
    }
}
