<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\ProductImage;
use App\Entity\User;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageProcessing
{
    private string $uploadDirForPost;
    private string $uploadDirForUser;
    private string $uploadDirForProduct;

    public function __construct(ParameterBagInterface $parameterBag) {
        $this->uploadDirForPost = $parameterBag->get('post_image_directory');
        $this->uploadDirForProduct = $parameterBag->get('product_image_directory');
        $this->uploadDirForUser = $parameterBag->get('avatar_directory');
    }

    public function save(Post|User|ProductImage $obj, UploadedFile $imageFile, string $pathFor): void
    {
        if ($pathFor === 'user') {
            $directory = '/avatars/';
            $uploadDir = $this->uploadDirForUser;
        } elseif ($pathFor === 'post') {
            $directory = '/images/';
            $uploadDir = $this->uploadDirForPost;
        } elseif ($pathFor === 'product') {
            $directory = '/images/';
            $uploadDir = $this->uploadDirForProduct;
        }

        $newFileName = uniqid() . '.' . $imageFile->guessExtension();

        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageFile->getPathname());

        $image->save($uploadDir . $directory . $newFileName);

        $thumbnailFilename = 'thumb_' . $newFileName;
        $image->cover(width: 400, height: 300)->save($uploadDir . '/thumbnails/' . $thumbnailFilename);

        if ($pathFor === 'user') {
            $obj->setAvatar($directory . $newFileName);
        } elseif ($pathFor === 'post') {
            $obj->setImage($directory . $newFileName);
        } elseif ($pathFor === 'product') {
            $obj->setPath($directory . $newFileName);
        }

        $obj->setThumbnail('/thumbnails/' . $thumbnailFilename);
    }

    public function removeImage(Post $post): void
    {
        if ($post->getImage()) {
            $imagePath = $this->uploadDirForPost . $post->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        if ($post->getThumbnail()) {
            $thumbnailPath = $this->uploadDirForPost . $post->getThumbnail();
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }

    public function removeProductImage(ProductImage $productImage): void
    {
        if ($productImage->getPath()) {
            $productImagePath = $this->uploadDirForProduct . $productImage->getPath();
            if (file_exists($productImagePath)) {
                unlink($productImagePath);
            }
        }

        if ($productImage->getThumbnail()) {
            $productThumbnailPath = $this->uploadDirForProduct . $productImage->getThumbnail();
            if (file_exists($productThumbnailPath)) {
                unlink($productThumbnailPath);
            }
        }
    }

    public function removeAvatar(User $user): void
    {
        if ($user->getAvatar()) {
            $avatarPath = $this->uploadDirForUser . $user->getAvatar();
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        if ($user->getThumbnail()) {
            $thumbnailPath = $this->uploadDirForUser . $user->getThumbnail();
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }
}