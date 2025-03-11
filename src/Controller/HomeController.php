<?php

namespace App\Controller;

use App\Entity\ExchangeRate;
use App\Entity\WidgetSetting;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ExchangeRateRepository $exchangeRateRepository, EntityManagerInterface $entityManager): Response
    {
        $rates =  $exchangeRateRepository->findLastFive();

        $countryCodes = ['cn', 'kz', 'ru', 'eu', 'us']; // to output country flags

        $settings = $entityManager->getRepository(WidgetSetting::class)->find(1);

        return $this->render('main/index.html.twig', ['rates' => $rates, 'settings' => $settings, 'countryCodes' => $countryCodes]);
    }
}
