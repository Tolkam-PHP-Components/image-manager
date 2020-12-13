<?php declare(strict_types=1);

namespace Tolkam\ImageManager;

use Tolkam\ImageManager\Variation\VariationManager;

interface ImageManagerInterface
{
    /**
     * @return VariationManager
     */
    public function getVariationManager(): VariationManager;
    
    /**
     * Gets image meta
     *
     * @param string $sourceFilename
     *
     * @return ImageMeta
     */
    public function getMeta(string $sourceFilename): ImageMeta;
    
    /**
     * Creates image variation and returns created file name
     *
     * @param string $sourceFilename
     * @param string $variationAlias
     *
     * @return string
     */
    public function createVariation(string $sourceFilename, string $variationAlias): string;
    
    /**
     * Deletes all registered variations
     *
     * @param string $sourceFilename
     *
     * @return bool
     */
    public function deleteVariations(string $sourceFilename): bool;
}
