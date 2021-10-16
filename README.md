# tolkam/image-manager

Image manager with image variations support.

## Documentation

The code is rather self-explanatory and API is intended to be as simple as possible. Please, read the sources/Docblock if you have any questions. See [Usage](#usage) for quick start.

## Usage

````php
use Intervention\Image\ImageManager as InterventionImageManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Tolkam\ImageManager\ImageManager;
use Tolkam\ImageManager\Variation\VariationInterface;
use Tolkam\ImageManager\Variation\VariationManager;
use Tolkam\Storage\Storage;
use Tolkam\UriGenerator\UriGenerator;

$filesystem = new Filesystem(new Local(sys_get_temp_dir()));
$storage = new Storage($filesystem, new UriGenerator());
$variationManger = new VariationManager();

// register image variation
$resizeToJpgVariation = new class implements VariationInterface {
    public function crop(): bool
    {
        return false;
    }
    
    public function getId(): string
    {
        return 'resized';
    }
    
    public function getExtension(): string
    {
        return 'jpg';
    }
    
    public function getWidth(): ?int
    {
        return 10;
    }
    
    public function getHeight(): ?int
    {
        return 10;
    }
    
    public function getQuality(): ?int
    {
        return 80;
    }
    
    public function getSharpen(): ?int
    {
        return 0;
    }
};
$variationManger->register($resizeToJpgVariation, 'my-resized-variation');

$imageManager = new ImageManager(
    $storage,
    $variationManger,
    new InterventionImageManager
);

$sourceFileName = 'image.png';

//create source file for demo purposes
if (!$filesystem->has($sourceFileName)) {
    $filesystem->write($sourceFileName, base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAABlBMVEUAAAD///+l2Z/dAAAAM0lEQVR4nGP4/5/h/1+G/58ZDrAz3D/McH8yw83NDDeNGe4Ug9C9zwz3gVLMDA/A6P9/AFGGFyjOXZtQAAAAAElFTkSuQmCC'));
}

// create image variation from the source file
$variationFileName = $imageManager->createVariation($sourceFileName, 'my-resized-variation');

$sourceMeta = $imageManager->getMeta($sourceFileName);
$variationMeta = $imageManager->getMeta($variationFileName);

// check results
echo "Source: $sourceFileName
Dimensions: {$sourceMeta->getWidth()}x{$sourceMeta->getHeight()}\n";

echo "Variation: $variationFileName
Dimensions: {$variationMeta->getWidth()}x{$variationMeta->getHeight()}";

// clean things up
$filesystem->delete($sourceFileName);
$imageManager->deleteVariations($sourceFileName);
````

Results:

````
Source: image.png
Dimensions: 16x16
Variation: image__resized.jpg
Dimensions: 10x10
````

## License

Proprietary / Unlicensed 🤷
