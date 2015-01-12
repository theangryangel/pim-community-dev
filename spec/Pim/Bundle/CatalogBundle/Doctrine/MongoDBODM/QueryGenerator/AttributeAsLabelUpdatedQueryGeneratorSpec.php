<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class AttributeAsLabelUpdatedQueryGeneratorSpec extends ObjectBehavior
{
    function let(NamingUtility $utility)
    {
        $this->beConstructedWith($utility, 'Pim\Bundle\CatalogBundle\Model\AttributeInterface', 'attributeAsLabel');
    }

    function it_filters_updates_on_attribute_class_and_attribute_as_label_field(
        AttributeInterface $price,
        ChannelInterface $mobile
    ) {
        $this->supports($price, 'attributeAsLabel')->shouldReturn(true);
        $this->supports($price, '')->shouldReturn(false);
        $this->supports($mobile, 'attributeAsLabel')->shouldReturn(false);
        $this->supports($mobile, '')->shouldReturn(false);
    }

    function it_generates_a_query_to_update_product_families(AttributeInterface $price)
    {
        $price->getId()->willReturn(12);

        $this->generateQuery($price, 'attributeAsLabel', 'sku', 'name')->shouldReturn([[
            ['family'   => 12],
            ['$set'     => ['normalizedData.family.attributeAsLabel' => 'name']],
            ['multiple' => true]
        ]]);
    }
}
