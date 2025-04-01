<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
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

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab()->setIcon('fa-solid fa-address-book'),
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('surname'),
            TextField::new('username'),
            FormField::addTab()->setIcon('fas fa-user'),
            EmailField::new('email'),
            ImageField::new('avatar')->setBasePath('uploads/user')->hideOnIndex(),
        ];
    }

}
