<?php

namespace App\Controller\Admin;

use App\Entity\WidgetSetting;
use App\Form\WidgetSettingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingController extends AbstractController
{
    #[Route('/admin/settings', name: 'app_setting', methods: ['GET', 'POST'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $settings = $entityManager->getRepository(WidgetSetting::class)->find(1);

        if (!$settings) {
            $settings = new WidgetSetting();
        }

        $form = $this->createForm(WidgetSettingType::class, $settings);
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($settings);
            $entityManager->flush();
            $this->addFlash('success', 'Settings successfully updated');

            return $this->redirect($referer ?? $this->generateUrl('index_user'));
        }
        return $this->render('admin/setting.html.twig', ['form' => $form]);
    }
}
