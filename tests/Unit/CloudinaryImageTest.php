<?php

namespace Tests\Unit;

use App\Support\CloudinaryImage;
use PHPUnit\Framework\TestCase;

class CloudinaryImageTest extends TestCase
{
    public function test_replaces_upload_transformations_and_keeps_version_and_folders(): void
    {
        $url = 'https://res.cloudinary.com/demo/image/upload/w_600,h_600,c_fill,q_auto,f_auto/v1690000001/bida-events/xv_sofia/galeria/foto.jpg';

        $this->assertSame(
            'https://res.cloudinary.com/demo/image/upload/f_auto,q_auto,c_limit,w_800/v1690000001/bida-events/xv_sofia/galeria/foto.jpg',
            CloudinaryImage::url($url, 800)
        );
    }

    public function test_does_not_strip_folders_that_look_like_parameters(): void
    {
        $url = 'https://res.cloudinary.com/demo/image/upload/my_photos/foto.jpg';

        $this->assertSame(
            'https://res.cloudinary.com/demo/image/upload/f_auto,q_auto,c_limit,w_480/my_photos/foto.jpg',
            CloudinaryImage::url($url, 480)
        );
    }

    public function test_leaves_non_cloudinary_urls_untouched(): void
    {
        foreach (['/storage/uploads/foto.jpg', 'data:image/png;base64,AAAA', 'https://example.com/foto.jpg', null] as $url) {
            $this->assertSame($url, CloudinaryImage::url($url, 800));
            $this->assertNull(CloudinaryImage::srcset($url));
        }
    }

    public function test_builds_a_srcset_with_one_candidate_per_width(): void
    {
        $srcset = CloudinaryImage::srcset('https://res.cloudinary.com/demo/image/upload/v1/foto.jpg', [480, 1200]);

        $this->assertSame(
            'https://res.cloudinary.com/demo/image/upload/f_auto,q_auto,c_limit,w_480/v1/foto.jpg 480w, '
            .'https://res.cloudinary.com/demo/image/upload/f_auto,q_auto,c_limit,w_1200/v1/foto.jpg 1200w',
            $srcset
        );
    }
}
