<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function saveImage(string $uploadDir, Post $post, UploadedFile $imageFile): void
    {
        $newFileName = uniqid() . '.' . $imageFile->guessExtension();

        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageFile->getPathname());

        $image->save($uploadDir . '/images/' . $newFileName);

        $thumbnailFilename = 'thumb_' . $newFileName;
        $image->scale(width: 400, height: 300)->save($uploadDir . '/thumbnails/' . $thumbnailFilename);

        $post->setImage('/images/' . $newFileName);
        $post->setThumbnail('/thumbnails/' . $thumbnailFilename);
    }

    public function removeImageFiles(string $uploadDir, Post $post): void
    {
        if ($post->getImage()) {
            $imagePath = $uploadDir . $post->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($post->getThumbnail()) {
            $thumbnailPath = $uploadDir . $post->getThumbnail();
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }
}
