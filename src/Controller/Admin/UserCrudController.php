<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\ImageProcessing;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ImageProcessing $imageProcessing,
        private RequestStack $requestStack,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')

            ->setSearchFields(['name', 'surname', 'username', 'email'])
            ->setAutofocusSearch()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Action::INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('User data'),
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('surname'),
            TextField::new('username'),
            EmailField::new('email'),
            TextField::new('password')->setFormType(PasswordType::class)->setRequired(false)->setFormTypeOption('empty_data', '')->onlyOnForms(),
            DateField::new('dob'),
            ChoiceField::new('roles')->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'User' => 'ROLE_USER',
            ])
                ->allowMultipleChoices()->hideOnIndex()
                ->setFormTypeOption('by_reference', false),

            FormField::addTab('Avatar'),
            ImageField::new('avatar')->setBasePath('public/uploads/user/thumbnails/')->setUploadDir('public/uploads/user/thumbnails')->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword()) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword()));
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            if ($entityInstance->getPassword()) {
                $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword()));
            } elseif ($entityInstance->getPassword() === '') {
                $existingUser = $entityManager->getRepository(User::class)->find($entityInstance->getId());
                $entityInstance->setPassword($existingUser->getPassword());
            }
        }

        $request = $this->requestStack->getCurrentRequest();
        $avatarFile = $request->files->get('User')['avatar']['file'] ?? null; 

        if ($avatarFile instanceof UploadedFile) {
            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $avatarFile->guessExtension();
            copy($avatarFile->getPathname(), $tempPath);

            $accessibleFile = new UploadedFile(
                $tempPath,
                $avatarFile->getClientOriginalName(),
                $avatarFile->getClientMimeType(),
                $avatarFile->getError(),
                true
            );


        }

        dd($accessibleFile);


        /*if ($avatarFile) {
            $this->imageProcessing->save($entityInstance, $avatarFile, 'user');
        }*/

        parent::updateEntity($entityManager, $entityInstance);
    }
}
