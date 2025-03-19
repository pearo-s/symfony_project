<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    private string $uploadDir;
    public function __construct(ManagerRegistry $registry, ParameterBagInterface $parameterBag)
    {
        parent::__construct($registry, Post::class);
        $this->uploadDir = $parameterBag->get('post_image_directory');
    }

    public function saveImage(Post $post, UploadedFile $imageFile): void
    {
        $newFileName = uniqid() . '.' . $imageFile->guessExtension();

        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageFile->getPathname());

        $image->save($this->uploadDir . '/images/' . $newFileName);

        $thumbnailFilename = 'thumb_' . $newFileName;
        $image->scale(width: 400, height: 300)->save($this->uploadDir . '/thumbnails/' . $thumbnailFilename);

        $post->setImage('/images/' . $newFileName);
        $post->setThumbnail('/thumbnails/' . $thumbnailFilename);
    }

    public function removeImageFiles(Post $post): void
    {
        if ($post->getImage()) {
            $imagePath = $this->uploadDir . $post->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($post->getThumbnail()) {
            $thumbnailPath = $this->uploadDir . $post->getThumbnail();
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }

    public function createSearchBuilder(?string $search, QueryBuilder $query): QueryBuilder
    {
        if ($search !== '') {
            $query->andWhere('p.title LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        return $query;
    }
}
