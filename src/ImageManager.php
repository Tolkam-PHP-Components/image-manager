<?php declare(strict_types=1);

namespace Tolkam\ImageManager;

use Closure;
use Imagick;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager as InterventionManager;
use Tolkam\ImageManager\Variation\VariationInterface;
use Tolkam\ImageManager\Variation\VariationManager;
use Tolkam\Storage\StorageInterface;

class ImageManager implements ImageManagerInterface
{
    /**
     * @var StorageInterface
     */
    protected StorageInterface $storage;
    
    /**
     * @var VariationManager
     */
    protected VariationManager $variationManager;
    
    /**
     * @var InterventionManager
     */
    protected InterventionManager $intervention;
    
    /**
     * Supported formats
     * @var string[]
     */
    protected array $supportedFormats = [
        'jpg',
        'png',
        'gif',
        'tif',
        'bmp',
        'ico',
        'psd',
        'webp',
    ];
    
    /**
     * Formats to copy without applying variation
     * @var string[]
     */
    protected array $passThroughFormats = [
        'gif',
        'svg',
    ];
    
    /**
     * @param StorageInterface    $storage
     * @param VariationManager    $variationManager
     * @param InterventionManager $intervention
     */
    public function __construct(
        StorageInterface $storage,
        VariationManager $variationManager,
        InterventionManager $intervention
    ) {
        $this->storage = $storage;
        $this->variationManager = $variationManager;
        $this->intervention = $intervention;
    }
    
    /**
     * @return VariationManager
     */
    public function getVariationManager(): VariationManager
    {
        return $this->variationManager;
    }
    
    /**
     * Gets the supported formats
     *
     * @return string[]
     */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }
    
    /**
     * Sets the supported formats
     *
     * @param string[] $formats
     *
     * @return self
     */
    public function setSupportedFormats(array $formats): self
    {
        $this->supportedFormats = $formats;
        
        return $this;
    }
    
    /**
     * Gets the pass-through formats
     *
     * @return string[]
     */
    public function getPassThroughFormats(): array
    {
        return $this->passThroughFormats;
    }
    
    /**
     * Sets the pass-through formats
     *
     * @param string[] $formats
     *
     * @return self
     */
    public function setPassThroughFormats(array $formats): self
    {
        $this->passThroughFormats = $formats;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getMeta(string $sourceFilename): ImageMeta
    {
        $meta = new ImageMeta;
        
        // mo meta for unsupported formats
        $sourceExt = pathinfo($sourceFilename, PATHINFO_EXTENSION);
        if (!in_array($sourceExt, $this->supportedFormats)) {
            return $meta;
        }
        
        $image = $this->createImage($sourceFilename, true);
        
        $meta->setWidth($image->getWidth());
        $meta->setHeight($image->getHeight());
        $meta->setLqp($this->lqp($image));
        $meta->setAverageColor(...$this->averageColor($image));
        
        $image->destroy();
        
        return $meta;
    }
    
    /**
     * @inheritDoc
     */
    public function createVariation(string $sourceFilename, string $variationAlias): string
    {
        $variation = $this->getVariation($variationAlias);
        $variationFilename = $this->variationManager->getVFL($sourceFilename, $variationAlias);
        
        // copy as is
        $sourceExt = pathinfo($sourceFilename, PATHINFO_EXTENSION);
        if (in_array($sourceExt, $this->passThroughFormats)) {
            $this->makeCopy($sourceFilename, $variationFilename);
            
            return $variationFilename;
        }
        
        // process variation
        $image = $this->createImage($sourceFilename, true);
        
        $width = $variation->getWidth();
        $height = $variation->getHeight();
        
        if ($width || $height) {
            if ($variation->crop()) {
                $image->fit($width, $height);
            }
            else {
                $image->resize($width, $height, $this->resizeConstraint());
            }
        }
        
        if ($sharpen = $variation->getSharpen()) {
            $image->sharpen($sharpen);
        }
        
        $this->writeImage($image, $variation, $variationFilename);
        $image->destroy();
        
        return $variationFilename;
    }
    
    /**
     * @inheritDoc
     */
    public function deleteVariations(string $sourceFilename): bool
    {
        $filenames = [];
        foreach ($this->variationManager->getAll() as $variation) {
            $filenames[] = $this->variationManager->getVFL($sourceFilename, $variation);
        }
        
        return $this->storage->deleteAll(...$filenames);
    }
    
    /**
     * Creates base64 encoded low quality image preview
     *
     * @param Image $image
     *
     * @return string
     */
    private function lqp(Image $image): string
    {
        $size = 32;
        $image = clone $image;
        
        $base64 = $image
            ->resize($size, $size, $this->resizeConstraint())
            ->blur(4)
            ->encode('data-url', 85)
            ->getEncoded();
        
        $image->destroy();
        
        return $base64;
    }
    
    /**
     * Gets average color
     *
     * @param Image $image
     *
     * @return array
     */
    private function averageColor(Image $image): array
    {
        $image = clone $image;
        
        $color = $image->resize(1, 1)->pickColor(0, 0);
        
        $image->destroy();
        
        return $color;
    }
    
    /**
     * Creates Intervention image object
     *
     * @param string $source
     * @param bool   $strip
     *
     * @return Image
     */
    private function createImage(string $source, bool $strip): Image
    {
        $image = $this->intervention->make(
            $this->storage->getRealPath($source)
        );
        
        // auto orientate before removing exif
        $image->orientate();
        
        // remove exif
        if ($strip && $this->isImagick($image)) {
            $image->getCore()->stripImage();
        }
        
        return $image;
    }
    
    /**
     * Writes image file
     *
     * @param Image              $image
     * @param VariationInterface $variation
     * @param string             $targetFilename
     *
     * @return Image
     */
    private function writeImage(
        Image $image,
        VariationInterface $variation,
        string $targetFilename
    ): Image {
        $psrStream = $image->stream(
            $variation->getExtension(),
            $variation->getQuality()
        );
        
        $this->storage->putFromStream($targetFilename, $psrStream->detach());
        
        return $image;
    }
    
    /**
     * @param string $sourceFilename
     * @param string $targetFilename
     *
     * @return bool
     */
    private function makeCopy(string $sourceFilename, string $targetFilename): bool
    {
        return $this->storage->copy($sourceFilename, $targetFilename);
    }
    
    /**
     * Checks if working with ImageMagick object
     *
     * @param Image $image
     *
     * @return bool
     */
    private function isImagick(Image $image): bool
    {
        return class_exists('Imagick') && $image->getCore() instanceof Imagick;
    }
    
    /**
     * Creates resize constraint fn
     *
     * @return Closure
     */
    private function resizeConstraint(): Closure
    {
        // TODO: configurable
        return function (Constraint $constraint) {
            $constraint->upsize();
            $constraint->aspectRatio();
        };
    }
    
    /**
     * @param string $variationAlias
     *
     * @return VariationInterface
     * @throws ImageManagerException
     */
    private function getVariation(string $variationAlias): VariationInterface
    {
        $variation = $this->variationManager->get($variationAlias);
        $format = $variation->getExtension();
        
        if (!in_array($format, $this->supportedFormats)) {
            throw new ImageManagerException(sprintf(
                'Unsupported format "%s". Supported are "%s"',
                $format,
                implode('", "', $this->supportedFormats)
            ));
        }
        
        return $variation;
    }
}
