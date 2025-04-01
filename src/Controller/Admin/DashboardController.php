<?php

namespace App\Controller\Admin;

use App\Entity\Commentary;
use App\Entity\Post;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CommentaryRepository;
use App\Repository\PostRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[AdminDashboard(routePath: '/admin', routeName: 'admin', routes: [
    'new' => ['routePath' => '/create', 'routeName' => 'create'],
    'detail' => ['routeName' => 'show']
])]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
        private readonly CommentaryRepository $commentaryRepository,
        private readonly ProductRepository $productRepository,
    ) {}

    public function index(): Response
    {
        return $this->redirectToRoute('admin_user_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin panel')
            ->renderContentMaximized()
            ->renderSidebarMinimized()
        ;
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::subMenu('User', 'fas fa-users')->setSubItems([
                MenuItem::linkToCrud('Users', 'fas fa-users', User::class)->setBadge($this->userRepository->count()),
            ]),


            MenuItem::subMenu('Post', 'fas fa-clipboard')->setSubItems([
                MenuItem::linkToCrud('Posts ', 'fas fa-clipboard', Post::class)->setBadge($this->postRepository->count()),
                MenuItem::linkToCrud('Commentaries ', 'fa-solid fa-comment', Commentary::class)->setBadge($this->commentaryRepository->count()),
            ]),

            MenuItem::linkToCrud('Products ', 'fa-solid fa-cart-shopping', Product::class)->setBadge($this->productRepository->count()),

            MenuItem::subMenu('Settings', 'fa-solid fa-gear')->setSubItems([
                MenuItem::linkToRoute('Widget settings', null, 'app_setting')
            ]),
        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setAvatarUrl($user->getAvatar() ? '/uploads/user' . $user->getAvatar() : '/uploads/no_avatar.png')
        ;
    }
}
