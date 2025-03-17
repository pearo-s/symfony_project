<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    private string $uploadDir;

    public function __construct(ManagerRegistry $registry, ParameterBagInterface $parameterBag)
    {
        parent::__construct($registry, User::class);
        $this->uploadDir = $parameterBag->get('avatar_directory');
    }

    public function createSearchAndSortQueryBuilder(?string $search, string $sort = 'u.id', string $direction = 'asc'): QueryBuilder
    {
        $query = $this->createQueryBuilder('u');

        if ($search !== '') {
            $query->andWhere('u.name LIKE :q OR u.surname LIKE :q OR u.username LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        $query->orderBy($sort, $direction);

        return $query;
    }

    public function saveAvatar(User $user, UploadedFile $avatarFile): void
    {
        $newFileName = uniqid() . '.' . $avatarFile->guessExtension();

        $manager = new ImageManager(new Driver());
        $image = $manager->read($avatarFile->getPathname());

        $image->save($this->uploadDir . '/avatars/' . $newFileName);

        $thumbnailFilename = 'thumb_' . $newFileName;
        $image->scale(width: 400, height: 300)->save($this->uploadDir . '/thumbnails/' . $thumbnailFilename);

        $user->setAvatar('/avatars/' . $newFileName);
        $user->setThumbnail('/thumbnails/' . $thumbnailFilename);
    }

    public function removeAvatarFiles(User $user): void
    {
        if ($user->getAvatar()) {
            $avatarPath = $this->uploadDir . $user->getAvatar();
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        if ($user->getThumbnail()) {
            $thumbnailPath = $this->uploadDir . $user->getThumbnail();
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }
}
