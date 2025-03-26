<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($j = 1; $j <= 20; $j++) {
            $productCategory = new ProductCategory();
            $productCategory->setName($faker->word());

            $manager->persist($productCategory);
            $productCategories[] = $productCategory;
        }

        foreach ($productCategories as $productCategory) {
            $productCount = rand(10, 30);
            for ($i = 0; $i < $productCount; $i++) {
                $product = new Product();
                $product->setCategory($productCategory);
                $product->setName($faker->words($faker->numberBetween(3, 8), true));
                $product->setDescription($faker->text(1000));
                $product->setPrice($faker->numberBetween(10, 3000));
                $product->setColour($faker->safeColorName());
                $product->setQuantity($faker->numberBetween(1, 100));
                $product->setCreatedAt();
                $product->setUpdatedAt();

                $manager->persist($product);
            }
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['products'];
    }
}
